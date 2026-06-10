<?php

namespace App\Http\Controllers;

use App\Helpers\Captcha;
use App\Helpers\Device;
use App\Helpers\GoldPartnersSdk;
use App\Helpers\Hlog;
use App\Helpers\Localization;
use App\Helpers\Password;
use App\Services\Users\UserManager;
use App\Http\Requests\RegisterRequest;
use App\Services\IpInfo\IpInfo;
use App\Jobs\BindManagerJob;
use App\Jobs\CreateJoinBonusByParamsJob;
use App\Jobs\CreateJoinBonusByTokenJob;
use App\Jobs\RegistrationDoneJob;
use App\Jobs\SendRecoveryEmailJob;
use App\Models\AdBonusToken;
use App\Models\Bonuses\Types\BonusMarker;
use App\Models\Bonuses\WelcomeBonusPack;
use App\Models\BonusPatterns;
use App\Models\BonusRegBType;
use App\Models\LoginForm;
use App\Models\MailSubscribe;
use App\Models\Partner;
use App\Models\PasswordRecoveryForm;
use App\Models\Popups\UserPopup;
use App\Models\RegistrationForm;
use App\Models\Bonuses\UserBonus;
use App\Models\SiteData;
use App\Models\UnsubscribedEmail;
use App\Models\UserReferrals\UserReferral;
use App\MUser;
use App\UserWallet;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthController extends Controller
{
    /**
     * Генерация кода подтверждения телефона
     * @param int $length
     * @return string
     */
    public function generatePhoneConfirmationCode($length = 4)
    {
        $chars = '1234567890';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        return $string;
    }


    /**
     * Регистрация
     * @param RegisterRequest $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|RedirectResponse|\Illuminate\Http\Response|string
     * @throws Exception
     */
    public function register(RegisterRequest $request)
    {
        if (!empty($request->post('email'))) {
            $lock = Cache::lock('register_email_lock_' . $request->post('email'), 10);
        } else {
            $phone = $request->post('phone') ? $request->post('phone_prefix', '') . $request->post('phone', '') : null;
            if ($phone === null) return response('Phone number or email is required', 400);
            $lock = Cache::lock('register_phone_lock_' . $request->post('phone'), 10);
        }

        try {
            if (!$lock->get()) {
                return response('Registration in process', 400);
            }
            // валидация данных
            $request->validated();
            //
            if ($request->onlyValidate === 'true') return 'success';
            $loginType = $request->post('login_type', 'both');
//        if ($loginType !== 'phone') $loginType = 'email';

            // создаем пользователя
            $user = new MUser();

            // установка значение
            $user->login = $request->getLogin();
            $user->password = Password::hash($request->post('password'));
            $user->view_password = $request->post('password');
            $user->currency = intval($request->post('currency'));
            $user->hall_id = UserWallet::currencyHallId($user->currency);
//        $user->email = $loginType === 'email' ? $request->post('email') : '';
//        $user->phone = $loginType === 'phone' ? $request->post('phone_prefix', '') . $request->post('phone', '') : '';
            $user->phone = $request->post('phone') ? $request->post('phone_prefix', '') . $request->post('phone', '') : null;
            $user->email = $request->post('email');

            $user->locale = App::getLocale();
            $user->address = $request->post('address');
            $user->post_index = $request->post('postcode');
            $user->city = substr($request->post('city', ""), 0, 15);
            $user->addres = $request->post('state');
            $user->name = $request->post('name');
            $user->surname = $request->post('surname');

            // устанавливаем коды верификации
            $user->once_pass = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);//Str::random('6');
            $user->email_verification_token = Str::random('16');
            $token = Str::random(32);
            // токен пользователя для запрета двойного входа
            $user->token = $token;
            $user->last_ip = $request->ip();
            $user->country = $user->currency;
            $user->setFingerprint($request->input('fingerprint').':'.request()->ip());
            $user->setLastTz($request->input('timezone'));;

            // пользовательский реферрал
            if (!empty(session('uref'))) {
                /** @var UserReferral $ref */
                $ref = UserReferral::where('ref_code', session('uref'))->first();
                if ($ref !== null) {
                    $user->ref_id = $ref->id;
//            } else {
//                $ref = UserReferral::where('bonus_ref_code', session('uref'))->first();
//                if ($ref !== null) $user->ref_id = $ref->id;
                }
            }

            // источник перехода пользователя
            $refOrigin = $request->cookie('origRef', null);
            if ($refOrigin) $user->origin_ref = $refOrigin;

            if (!$user->save()) return response('Server error', 500);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            $lock->release();
        }

        try {
            // данные из формы для валюты EUR
            if ($request->has('name') && $request->has('surname') && $request->has('birthday')) {
                // сохраняем как персональные данные
                $pers_data = $user->personalData();
                $pers_data->name = $request->input('name', '');
                $pers_data->surname = $request->input('surname', '');
                $pers_data->birthday = $request->input('birthday');
                $pers_data->country = $request->input('country');
                $pers_data->region = $request->input('state');
                $pers_data->city = $request->input('city');
                $pers_data->save();
            }

            // подписка на рассылку
            if ($request->boolean('subscribe')) {
                MailSubscribe::subscribeUser($user->getId());
            }

        } catch (\Exception $e) {
            Log::channel('login')->error($e->getMessage());
        }

        Session::put('user_id', $user->id);
        Session::put('user_token', $token);
        // привязка пользователя к менеджеру
        if (session('tg_client_hash') !== null) {
            BindManagerJob::dispatch($user->id, session('tg_client_hash'), false);
            session()->remove('tg_client_hash');
        }

        // выводим регистрационный попап
        UserPopup::showTaskStarterPopup($user->getId());

        // авторизируем
        Auth::login($user);

        // привязка пользователя к партнеру
        $clickId = $request->cookie('clickId', null);
        if ($clickId !== null) {
            $user->setClickId($clickId);
        }

        // создаем регистрационные бонусы
        self::createBonus($user);

//            // создаем бонусы за реферральную привязку
//            if (!empty($ref) && $ref->getBonusRefCode() === session('uref')) {
//                try {
//                    /** @var BonusPatterns $pattern */
//                    $pattern = BonusPatterns::where('name', 'bonus.referral_bonus')->first();
//                    if ($pattern !== null) {
//                        $pattern->createForUser($user);
//                    }
//                } catch (Exception $e) {
//                    Hlog::logException($e);
//                }
//            }

        // активация промокода введенного в регистрационной форме
        if (!empty($request->post('promocode'))) {
            try {
                $req = Request::create(action('BonusPromocodesController@activate'), 'POST', [
                    'promocode' => $request->post('promocode'),
                    'registration' => true,
                ]);
                Log::info('Activating promo after registration:' . $request->post('promocode'));
//                    $resp = Route::dispatch($req);
                $resp = (new BonusPromocodesController)->activate($req);
                Log::info(var_export($resp->getContent(), true));
            } catch (Exception $e) {
                Hlog::logException($e);
            }
        }
        Cookie::queue(Cookie::forget('clickId'));

        // бонусы при переходе из сайта колеса фортуны
        if (Cookie::get('fortune_bonus') !== null) {
            CreateJoinBonusByParamsJob::dispatch($user->id, Cookie::get('fortune_bonus'), $clickId, true);
            Cookie::queue(Cookie::forget('fortune_bonus'));
        }
        // запуск задачи завершения регистрации
        RegistrationDoneJob::dispatch($user->id, !\session('reg_click_sent', false), \request()->getHost());


        // пометка для ui что пользователь только что зарегистрирован
        \session()->flash('justRegistered', Auth::id());
//        \session()->flash('showDeposit', Auth::id());

        if (request()->ajax()) {
            $this->metaTags = null;
            if (Device::detect() === Device::PHONE) {
                $this->renderAdaptive('right-sidebar');
                $this->renderAdaptive('bottom-menu');
            } else {
                $this->renderAdaptive('header');
            }
            return $this->sendPreparedResponse();
        }

        return redirect()->route('landing');
    }


    /**
     * Создает регистрационные бонусы
     * @param MUser $user
     * @throws Exception
     */
    public static function createBonus(MUser $user, $currency = null)
    {
        try {
            if ($currency === null) $currency = $user->getWallet()->getCurrencyId();
            $bonus = null;
            // определяем тип регистрационного бонуса по валюте
            /** @var BonusRegBType $btype */
            $btype = BonusRegBType::where('currency_id', $currency)->first();
            // создаем бонусы
            if ($btype?->getBtype() === UserBonus::TYPE_PS_SFS_DYNAMIC) {
                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::getPatternByName('bonus.reg_fsbydep_' . \App\UserWallet::currencyName($currency));
                if ($pattern !== null) {
                    $bonus = $pattern->createForUser($user);
                } else {
                    Log::error("Can't find reg bonus pattern");
                }
            } else {
                $bonus = WelcomeBonusPack::createForUser($user);
            }
            // помечаем бонус регистрационным
            $bonus?->setMarker(BonusMarker::RegBonus->value);
            $bonus?->save();
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }


    public function setNewPassword()
    {
        $form = new PasswordRecoveryForm();
        $form->load($_POST);

        if ($form->checkToken() && $form->validate() && !$form->hasErrors()) {
            if (\request()->ajax()) {
                Log::info('ajax request success');
                return response('You can now log in using your new data.');
            } else {
                Log::info('changing password');
                $id = $form->updatePassword();
                /** @var MUser $user */
                $user = MUser::findOrFail($id);
                \session()->put('user_token', $user->token);
                Log::info('id ' . $id);
                Auth::loginUsingId($id);
                Log::info('should be authorized');
                return redirect()->route('landing');
            }
        }
        return response($form->getError(), 400);
    }


    /**
     * Авторизация
     */
    public function login()
    {
        $form = new LoginForm();
        $form->load($_POST);
        $ip = \request()->getClientIp();

        if ($form->validate()) {
            $form->login();

            if (request()->ajax()) {
                $this->metaTags = null;
                $this->renderAdaptive('header');
                $this->renderAdaptive('cabinet.account', [], ['cabinet.index']);
                if (Device::detect() === Device::PHONE) {
                    $this->renderAdaptive('right-sidebar');
                    $this->renderAdaptive('bottom-menu');
                }
                UserManager::addEvent(Auth::id(), 'visit');
                Redis::del('wl' . $ip);
                return $this->sendPreparedResponse(false, [], ['user_id' => Auth::id()]);
            }
            return response($form->getError(), 400);
        } else {
            sleep(2);
            $cnt = Redis::get('wl'.$ip, 0);
            $cnt++;
            if ($cnt % 3 === 0) {
                sleep(4);
            }
            if ($cnt > 9) {
                $info = IpInfo::getInfo($ip);
                if ($info !== null) {
//                    $info->block_to = now()->addMinutes(20);
                    IpInfo::Block($ip, now()->addMinutes(20));
//                    $info->save();
                }
            }
            Redis::set('wl'.$ip, $cnt);
            Log::info(\request()->getClientIp(). ' wrong login data, try '.$cnt);
            return response($form->getError(), 400);
        }
    }


    public function forgotPassword(Request $request)
    {
        Log::info('captcha:' . session('captcha'));
        Log::info('sess id' . session()->getId());
        if (SiteData::getFromJson('captcha_recovery', false) && intval($request->post('captcha', '')) !== intval(session('captcha'))) {
            return response('Captcha is invalid', 400);
        }
//        if (!Captcha::verify()) {
//            return response('Captcha is invalid', 400);
//        }

        $form = new PasswordRecoveryForm();
        $loginType = $request->post('login_type', 'email');
        $emailOrPhone = $request->post('email', null);
//        if ($loginType === 'phone') {
//            $emailOrPhone = $request->post('phone_prefix', '') . $request->post('phone', null);
//        }
        $user = $form->findUser($emailOrPhone);

        if ($user === null) {
            return response($form->getError(), 400);
        }

        if ($form->userFoundByPhone()) {
            $newPassword = Password::generate();
            $passwordHash = Password::hash($newPassword);
            $form->sendNewPasswordBySMS($newPassword, $passwordHash);
        } else {
            SendRecoveryEmailJob::dispatch($user->getId(), url('/'));
//            $form->sendRecoveryEmail();
        }

        if ($form->hasErrors()) {
            return response($form->getError(), 400);
        }

//        if ($form->userFoundByPhone()) return response(__('login.newPasswordSentViaSMS'));
        if ($form->userFoundByPhone()) {
            return $this->viewOrJson('common.recovery-token-form');
        };

        return $this->viewOrJson('common.recovery-mail-sent', ['phone' => $user->phone]);

//        $resp = new SendRecoveryMailResponse(__('login.newPasswordSentViaEmail'));
//        $resp->user_id = $user->id;
//        return $resp;
    }

    /**
     * Проверка токена смены пароля
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|RedirectResponse|\Illuminate\Http\Response|string
     */
    public function checkToken(Request $request)
    {
        sleep(1);
        if ($request->ajax()) {
            if (empty($request->input('token'))) return response(__('login.yourLinkIsInvalid'), 400);
            $user = MUser::where('password_reset_token', '=', $request->input('token'))->first();
            if ($user === null) {
                return response(__('login.yourLinkIsInvalid'), 400);
            }
            return 'ok';
        }
        return redirect()->route('setNewPassword', ['token' => $request->input('token')]);
    }


    public function logout()
    {
        Auth::logout();
        \session()->flush();
        return redirect('/' . app()->getLocale());
    }


    /**
     * @param Request $request
     * @deprecated
     */
    public function socialLogin(Request $request)
    {
        $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
        $user = json_decode($s, true);
        Log::info('social login user:' . var_export($user, true));
        $nickname = $user['uid'];
        $phone = isset($user['phone']) ? '+' . preg_replace('/[^0-9]/', '', $user['phone']) : '';
        $email = '';
        if (isset($user['verified_email']) && $user['verified_email']) {
            $email = $user['email'];
        }

        if (!empty($email)) {
            $lock = Cache::lock('register_email_lock_' . $email, 10);
        } else {
            $lock = Cache::lock('register_phone_lock_' . $phone, 10);
        }

        try {
            $passwd = $this->generatePhoneConfirmationCode(4);
            $one_pass = '';
            $passwd_confirm = $passwd;
            $hall_id = 237;
            $birthday = isset($user['bdate']) ? date("Y-m-d", strtotime($user['bdate'])) : null;

            $currency = UserWallet::CURRENCY_RUB;   /// валюта

            if ($currency == 2) {
                $val = 'UAH';
            } elseif ($currency == 1) {
                $val = 'RUB';
            } elseif ($currency == 3) {
                $val = 'BYN';
            }

            $client_mail = null;
            if (!empty($email)) {
                $client_mail = DB::table('m_users')
                    ->where('hall_id', '=', $hall_id)
                    ->where('email', '=', $email)
                    ->first();
            }

            $new_client_nik = DB::table('m_users')
                ->where('hall_id', '=', $hall_id)
                ->where('login', '=', $nickname)
                ->first();


            if (!empty($client_mail) || !empty($new_client_nik)) {
                /** @var MUser $user_reg */
                $user_reg = MUser::where('hall_id', '=', $hall_id)
                    ->where(function ($query) use ($email, $nickname) {
                        $query->where('email', '=', $email)->orWhere('login', '=', $nickname);
                    })->first();

                Session::put('user_id', $user_reg->id);
//            $user_reg->last_active = date('Y-m-d H:i:s');
                $user_reg->last_ip = request()->ip();
                $user_reg->user_agent = request()->userAgent();
                $token = Str::random(32);
                $user_reg->token = $token;
                $user_reg->save();

                Session::put('user_token', $token);

                if (Auth::loginUsingId($user_reg->id)) {
                    return redirect()->to('/cabinet');
                }
            }

            if ($passwd == $passwd_confirm) {

                $password = md5(md5($passwd) . 'q3jkp455ct6e64d5');

                $reg_form = new RegistrationForm();

                $reg_form->nickname = $nickname;
                $reg_form->phone = $phone;
                $reg_form->password = $password;
                $reg_form->email = $email;
                $reg_form->country = 1;
                $reg_form->currency = $currency;

                $user_reg = $reg_form->createAccount();

                if (!empty(session('uref'))) {
                    $ref = UserReferral::where('ref_code', session('uref'))->first();
                    if ($ref) {
                        $user_reg->ref_id = $ref->id;
                    }
                }

                $refOrigin = $request->cookie('origRef');
                if ($refOrigin) $user->origin_ref = $refOrigin;

                $user_reg->name = isset($user['first_name']) ? $user['first_name'] : '';
                $user_reg->surname = isset($user['last_name']) ? $user['last_name'] : '';
                $user_reg->save();


                // Notify partners about this event.
                $clickId = $request->cookie('clickId', null);
                if ($clickId !== null) {
                    $clickId = $user_reg->setClickId($clickId);
                    if ($clickId !== 0) {
                        $sdk = new GoldPartnersSdk(intval(config('goldPartners.casinoId')), config('goldPartners.secretKey'));
                        $sdk->clickId = $clickId;
                        $sdk->clientId = $reg_form->getUser()->id;
                        $sdk->clientName = $reg_form->getUser()->login;
                        $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
                        $sdk->send($msg);
                    }
                }

                Session::put('user_id', $user_reg->id);

                $user_reg->last_active = null;
                $user_reg->last_ip = request()->ip();
                $user_reg->user_agent = request()->userAgent();
                $token = Str::random(32);
                $user_reg->token = $token;
                $user_reg->save();
                Session::put('user_token', $token);

                if (Auth::loginUsingId($user_reg->id)) {
                    return redirect($this->getRedirectionUrl('/cabinet'));
                }
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            $lock->release();
        }
    }


    /**
     * @return RedirectResponse|void
     * @deprecated
     */
    public function loginByUrl()
    {
        if (isset($_GET['token'])) {
            /** @var MUser $user */
            $user = MUser::where(['autologin_1' => $_GET['token']])->first();
            if ($user !== null) {
                Session::put('user_id', $user->id);

                $user->updateLastActive();
                $user->last_ip = request()->ip();
                $user->user_agent = request()->userAgent();
                $token = Str::random(32);
                $user->token = $token;
                $user->save();

                Session::put('user_token', $token);
                Auth::loginUsingId($user->id);
                return redirect()->to('/cabinet');
            }
        }
    }


    /**
     * @param $defaultUrl
     * @return array|string|null
     * @deprecated
     */
    public function getRedirectionUrl($defaultUrl)
    {
        $url = Cookie::get('redirectTo', $defaultUrl);
        Cookie::forget('redirectTo');
        return $url;
    }


    public function joinWithBonusByParams(Request $request)
    {
        Log::info(__METHOD__ . var_export($request->all(), true));
        if (!isset($_POST['email']) || !preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $_POST['email'], $match)) {
            Log::error('joinWithBonusByParams wrong email' . var_export($request->all(), true));
            die(500);
        }
        if (!empty($email)) {
            $lock = Cache::lock('register_email_lock_' . $_POST['email'], 10);
        }
        try {
            if (MUser::where('email', $_POST['email'])->first()) {
                Log::error('joinWithBonusByParams email already exists: ' . $_POST['email']);
                return redirect()->back()->withErrors(['email' => 'Email already exists']);
            }

            // Create new user model.
            $pass = Str::random(10);
//        $user = new MUser();

            $login = $match[1];
            $tries = 0;
            //if login exists
            while ($tries < 10 && MUser::where('login', $login)->first()) {
                $login = $login . random_int(0, 9 * $tries);
                $tries++;
            }
            if (MUser::where('login', $login)->first()) {
                Log::error('joinWithBonusByParams cant generate login! last login: ' . $login);
                die(500);
            }
            // Assign some values.
            $reg_form = new RegistrationForm();

            $reg_form->nickname = $login;
            $reg_form->password = $pass;
            $reg_form->email = $_POST['email'];
            $reg_form->country = 1;
            $reg_form->currency = UserWallet::CURRENCY_RUB;

            $locale = $request->post('lang', 'en');

            // Define currency for the user.
            $reg_form->currency = UserWallet::CURRENCY_INR;
            if ($locale === 'ua') {
                $reg_form->currency = UserWallet::CURRENCY_UAH;
            }
            if ($locale === 'ru') {
                $reg_form->currency = UserWallet::CURRENCY_RUB;
            }
            if ($locale === 'kz') {
                $reg_form->currency = UserWallet::CURRENCY_KZT;
            }

            $user = $reg_form->createAccount();
            $user->login = $login;
            $user->password = Password::hash($pass);
            $user->view_password = $pass;
            $user->hall_id = UserWallet::currencyHallId($user->currency);
            $user->email = $_POST['email'];
            $user->locale = $request->post('lang', 'en');

            $refOrigin = $request->cookie('origRef');
            if ($refOrigin) $user->origin_ref = $refOrigin;

            $token = Str::random(32);
            $user->token = $token;

            $user->country = 1;
            $user->procents = 0;
            $hallSettings = DB::connection('mysql2')->table('halls')->where('id', '=', 237)->first();
            if ($hallSettings) $user->procents = $hallSettings->procents;

            $clickId = $request->cookie('clickId', null);
            if (!$clickId) {
                $clickId = $request->post('clickId', null);
            }

            if ($clickId !== null) {
                $clickId = $user->setClickId($clickId);
            }

            if ($user->save()) {
                Localization::setAppLocale($user->locale);

                Cookie::queue(Cookie::forget('clickId'));
                Session::put('user_token', $token);
                session()->put('user_id', $user->id);
                Auth::loginUsingId($user->id);

                RegistrationDoneJob::dispatch($user->id, !\session('reg_click_sent', false), \request()->getHost());
                CreateJoinBonusByParamsJob::dispatch($user->id, $request->get('bonus'), $clickId, true);
                return redirect()->to('/' . $user->locale);
            } else {
                Log::error('Can`t save user ' . var_export($user, true));
            }
        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            $lock->release();
        }
    }

    public function regWithBonusByParams(Request $request)
    {
        Log::info('register from fortune:' . var_export($request->all(), true));
        Log::info(__METHOD__ . var_export($request->all(), true));
//        if (!isset($_POST['email']) || !preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $_POST['email'], $match)) {
//            Log::error('joinWithBonusByParams wrong email' . var_export($request->all(), true));
//            die(500);
//        }
//        if (MUser::where('email', $_POST['email'])->first()) {
//            Log::error('joinWithBonusByParams email already exists: ' . $_POST['email']);
//            die(500);
//        }
//        \session()->put('email', $_POST['email']);
        session()->put('fortune_bonus', $request->get('bonus'));
        if (!empty($request->get('bonus'))) {
            Cookie::queue(Cookie::make('fortune_bonus', $request->get('bonus'), 60));
        }

        $locale = $request->post('lang', 'kz');

        $clickId = $request->cookie('clickId', null);
        if (!$clickId) {
            $clickId = $request->post('clickId', null);
        }

        if ($clickId !== null) {
            Cookie::queue(Cookie::make('clickId', $clickId, 60));
        }
        Localization::setAppLocale($locale);

        return redirect()->action('AuthController@register', ['locale' => $locale]);
    }


    /**
     * @param Request $request
     * @return RedirectResponse|void
     * @deprecated
     */
    public function joinWithBonusByToken(Request $request)
    {
        $adBonus = AdBonusToken::where('token', $request->get('token', '-1'))
            ->where('user_id', null)->first();

        if ($adBonus === null) {
            throw new NotFoundHttpException();
        }

        $partner = Partner::where('id', $adBonus->partner_id)->first();

        if ($partner === null) {
            throw new NotFoundHttpException();
        }

        // Create new user model.
        $pass = Str::random(10);
        $user = new MUser();
        $this->_user = $user;

        // Assign some values.
        $user->login = Str::random(8);
        $user->password = Password::hash($pass);
        $user->view_password = $pass;
        $user->hall_id = 237;
        $user->pid = $adBonus->partner_id;

        // Define currency for the user.
        $user->currency = 1; // 1 - means RUB.
        $user->country = 1;
        $hallSettings = DB::connection('mysql2')->table('halls')->where('id', '=', 237)->first();
        $user->procents = $hallSettings === null ? 0 : $hallSettings->procents;

        $clickId = $request->cookie('clickId', null);
        if ($clickId !== null) {
            $clickId = $user->setClickId($clickId);
        }

        $refOrigin = $request->cookie('origRef');
        if ($refOrigin) $user->origin_ref = $refOrigin;

        if ($user->save()) {

            Cookie::queue(Cookie::forget('clickId'));
            \Session::put('user_id', $user->id);
            Auth::loginUsingId($user->id);

            CreateJoinBonusByTokenJob::dispatch($user->id, $request->get('token', '-1'), $request->cookie('clickId', null), $request->get('source', ''));
            return redirect()->to('/');
        }
    }

    /**
     * user from managers telegram link
     * @param Request $request
     * @param $locale
     * @param $hash
     * @return RedirectResponse|void
     */
    public function joinHashClient(Request $request, $locale, $hash)
    {
        // if user authorized and client is not bind
        if (Auth::check()) {
            BindManagerJob::dispatch(Auth::id(), $hash);
        } else {
            //mark user
            \Session::put('tg_client_hash', $hash);
        }
        return redirect()->route('landing');
    }


    public function unsubscribe(Request $request)
    {
        if (hash('sha256', $request->email . '128tm342--') === $request->token) {

            $unsb = new UnsubscribedEmail();
            $unsb->email = $request->email;
            $unsb->save();

            return $this->viewOrJson('email.emailUnsubscribed');
        }

        return response('internal server error', 500);
    }

    public function regPreland(Request $request)
    {
        if (Auth::check()) return redirect()->route('landing');

        $key = "Dus78edsd$123das";;
        $hash = hash_hmac("sha256", $request->input('nonce'), $key);
        Log::info('regPreland request' . var_export($request->all(), true));
        Log::info('regPreland hash' . var_export($hash, true));

        $link_id = $request->input('link_id');

        if ($hash !== $request->input('hash')) {
            Log::error('regPreland wrong hash' . var_export($request->all(), true));
            return redirect()->route('login');
        }

        Log::info(__METHOD__ . var_export($request->all(), true));
        if (isset($_POST['email']) && !preg_match('/^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/', $_POST['email'], $match)) {
            Log::error('regPreland wrong email' . var_export($request->all(), true));
            return redirect()->route('login');
        }
        if (isset($_POST['email'])) {
            $lock = Cache::lock('register_email_lock_' . $_POST['email'], 10);
        } else {
            $lock = Cache::lock('register_email_lock_' . $_POST['phone'], 10);
        }
        try {
            if (isset($_POST['email']) && MUser::where('email', $_POST['email'])->first()) {
                Log::error('regPreland email already exists: ' . $_POST['email']);
                \session()->flash('email', $_POST['email']);
                \session()->flash('login_error', __('reg.user_already_exists'));
                return redirect()->route('login');
            }

            if (isset($_POST['phone']) && !preg_match('/^\+?[0-9]+$/', $_POST['phone'], $match)) {
                Log::error('regPreland wrong phone' . var_export($request->all(), true));
                return redirect()->route('login');
            }
            if (isset($_POST['phone']) && preg_match('/^\+?([0-9]+)$/', $_POST['phone'], $match)) {
                $phone = $match[1];
            }

            if (isset($_POST['phone']) && MUser::where('phone', '+' . $phone)->first()) {
                Log::error('regPreland phone already exists: ' . $_POST['phone']);
                \session()->flash('phone', $_POST['phone']);
                \session()->flash('login_error', __('reg.user_already_exists'));
                return redirect()->route('login');
            }

            $email = $request->post('email', '');
            $currency = $request->post('currency');
            if (preg_match('#^\w{3,3}$#', $currency)) {
                try {
                    $currency = UserWallet::getCurrencyCode($currency);
                } catch (\Exception $e) {
                    $currency = UserWallet::CURRENCY_RUB;
                }
            }

            if (!empty($email)) {
                $login = explode('@', $email)[0];
            } else {
                $login = $phone;
            }

            // Create new user model.
            $user = new MUser();

            // Assign some values.
            $user->login = $login;
            $pass = Str::random(8);
            $user->password = Password::hash($request->post('password', $pass));
            $user->view_password = $request->post('password', $pass);
            $user->currency = intval($currency);
            $user->hall_id = UserWallet::currencyHallId($currency);
            $user->phone = $request->post('phone') ? $request->post('phone_prefix', '') . $request->post('phone', '') : null;
            $user->email = $request->post('email');

            $user->locale = App::getLocale();
            $user->address = $request->post('address');
            $user->post_index = $request->post('postcode');
            $user->city = substr($request->post('city', ""), 0, 15);
            $user->addres = $request->post('state');
            $user->name = $request->post('name');
            $user->surname = $request->post('surname');

            $user->once_pass = Str::random('6');
            $user->email_verification_token = Str::random('16');
            $token = Str::random(32);
            $user->token = $token;
            $user->country = $user->currency;

            $refOrigin = $request->cookie('origRef', null);
            if ($refOrigin) $user->origin_ref = $refOrigin;

            if (!$user->save()) return response('Server error', 500);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            $lock->release();
        }

        Session::put('user_id', $user->id);
        Session::put('user_token', $token);

        //next popup - tasks
        UserPopup::showTaskStarterPopup($user->getId());

        Auth::login($user);

        $clickId = $request->input('click_id', null);
        if ($clickId !== null) {
            $user->setClickId($clickId);
        } elseif ($link_id !== null) {
            try {
                $clickId = GoldPartnersSdk::getClickId($link_id);
                $user->click_id = $clickId;
                $user->save();
            } catch (\Exception $e) {
                Hlog::logException($e);
            }
        }

        self::createBonus($user);

        Cookie::queue(Cookie::forget('clickId'));
        if (Cookie::get('fortune_bonus') !== null) {
            CreateJoinBonusByParamsJob::dispatch($user->id, Cookie::get('fortune_bonus'), $clickId, true);
            Cookie::queue(Cookie::forget('fortune_bonus'));
        }
        RegistrationDoneJob::dispatch($user->id, !\session('reg_click_sent', false), \request()->getHost());


        \session()->flash('justRegistered', Auth::id());

        if (request()->ajax()) {
            $this->metaTags = null;
            if (Device::detect() === Device::PHONE) {
                $this->renderAdaptive('right-sidebar');
                $this->renderAdaptive('bottom-menu');
            } else {
                $this->renderAdaptive('header');
            }
            return $this->sendPreparedResponse();
        }

        return redirect()->route('landing');
    }
}
