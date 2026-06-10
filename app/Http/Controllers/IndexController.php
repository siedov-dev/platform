<?php

namespace App\Http\Controllers;

use App\Helpers\Currency;
use App\Helpers\MetaTags;
use App\Helpers\Password;
use App\Services\IpInfo\IpInfo;
use App\Models\UserReferrals\UserReferral;
use App\MUser;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            if (str_starts_with($request->getAcceptableContentTypes()[0]?? '', 'image')) return "";
//            Log::info('landing page not ajax request '. var_export($request->accepts("image/*"), true));
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        // Set meta-tags.
        $this->metaTags = new MetaTags();
        $this->metaTags->title = __('meta.mainTitle');
        $this->metaTags->description = __('meta.mainDescription');
        $this->metaTags->keywords = __('meta.mainKeywords');

//        $this->processSocialLogin();

        // Render layout.
        if ($request->ajax()) {
            EmulatorController::transferMoneyBack();
            $this->renderAdaptive('landing.index', [], []);
        } else {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
        }
        return $this->sendPreparedResponse();
    }

    public function loginForm(Request $request)
    {
        $this->renderAdaptive('auth.login-form', $request->all(), []);
        return $this->sendPreparedResponse();
    }

    public function registerForm(Request $request)
    {
            Cache::forget('recaptcha');
            $country = 'in';
            $info = IpInfo::getInfo(request()->ip());
            if (!empty($info)) {
                $country = mb_strtolower($info->country_code??'in');
            }
            $currencyMap = Currency::getLangCurrencyMap();
            $default_currency = $currency??$currencyMap[app()->getLocale()];

        $detected_country = '';
        if (!empty($info)) {
            $detected_country = mb_strtolower($info->country_code);
        }

        $default_currency = IpInfo::getInfo(request()->ip())?->currency ?? Currency::EUR;

        if (!Currency::isActive($default_currency)) {
            $default_currency = Currency::EUR;
            if (!empty($detected_country) && isset(Currency::getCountryCurrencyMap()[$detected_country])) {
                $default_currency = \App\Helpers\Currency::getCountryCurrencyMap()[$detected_country];
            }
        }

        if (request()->getHost() === 'krakencasino.club') $default_currency = 'EUR';
        if (request()->getHost() === 'krakencasino.co') $default_currency = 'USDT';
//        if (request()->getHost() === 'krakencasino.bet') $default_currency = 'KZT';

        $currency = $request->get('currency', UserWallet::getCurrencyCode($default_currency));
        if ($currency == UserWallet::CURRENCY_EUR) {
            $this->renderAdaptive('auth.register-form-ext', ['currency' => $currency, 'route' => $request->route()->getName()]);
        } else {
            $this->renderAdaptive('auth.register-form', ['currency' => $currency, 'route' => $request->route()->getName()]);
        }
//                $this->renderAdaptive('auth.register-form', ['currency' => $currency, 'route' => $request->route()->getName()]);
        return $this->sendPreparedResponse();
    }


    private function processSocialLogin()
    {
        if (Auth::check()) return;
        Log::info('check social user');
        Log::info('Method: ' . request()->method());
        if (request()->method() === 'POST') {
            if (empty($_POST['token'])) return;
            $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
            $userData = json_decode($s, true);
            Log::info(var_export($userData, true));
            if (is_array($userData)) {
                $user = null;

                if (!empty($userData['identity'])) {
                    $socialLogin = $userData['network'] . '_' . $userData['identity'];
                    $user = MUser::where('social_login', $socialLogin)->first();
                }

                if ($user === null) {
                    $login = 'User_' . time();
                    $tries = 0;
                    while (MUser::where('login', $login)->first() !== null && $tries < 10) {
                        $login = 'User_' . Str::random(3) . time();
                        $tries++;
                    }
                    $socialLogin = $userData['network'] . '_' . (empty($userData['identity']) ? Str::random(16) : $userData['identity']);
                    $pass = Str::random(8);

                    // Create new user model.
                    $user = new MUser();

                    // Assign some values.
                    $user->login = $login;
                    $user->social_login = $socialLogin;
                    $user->password = Password::hash($pass);
                    $user->view_password = $pass;
                    $user->currency = UserWallet::CURRENCY_RUB;
                    $user->hall_id = UserWallet::currencyHallId($user->currency);
                    $user->email = '';
                    $user->phone = '';
                    $user->once_pass = Str::random('6');
                    $user->email_verification_token = Str::random('16');
                    $user->country = $user->currency;

                    if (!empty(session('uref'))) {
                        $ref = UserReferral::where('ref_code', session('uref'))->first();
                        if ($ref) {
                            $user->ref_id = $ref->id;
                        }
                    }

                    $user->save();

//                    if ($user->ref_id) {
//                        /** @var UserReferral $ref */
//                        $ref = UserReferral::find($user->ref_id);
//                        if ($ref) {
//                            $ref->onRegister($user);
//                        }
//                    }

                    AuthController::createBonus($user);
//                    BonusGroup::createForUser($user);
                }

                Session::put('user_id', $user->id);

                $user->last_active = date('Y-m-d H:i:s');
                $user->last_ip = request()->ip();
                $user->user_agent = request()->userAgent();
                $token = Str::random(32);
                $user->token = $token;
                $user->save();

                Session::put('user_token', $token);

                Auth::login($user);
            }
        }
    }


    public function referralSystem(Request $request)
    {
        //if not ajax send page loader
        if (!$request->ajax()) {
            $this->renderAdaptive('page_loader', ['url' => $request->getRequestUri()], ['layouts.main']);
            return $this->sendPreparedResponse();
        }

        $this->metaTags = $this->createDefaultMetaTags();
        $this->metaTags->title = __('meta.referralSystemTitle');
        $this->metaTags->description = __('meta.referralSystemDesc');

        $currency = \App\UserWallet::CURRENCY_EUR;
        $user = \App\MUser::getAuth();
        if ($user !== null) {
            $currency = $user->getCurrency();
        }
        $currency_name = mb_strtolower(\App\UserWallet::currencyName($currency));
        $courses = \App\Helpers\Currency::getCourses();

        $query = UserReferral::query();
        $query->selectRaw("user_referrals.user_id, m_users.login as login
            , ref_evnts.amount as amount
            , ref_evnts.profit as profit_o
            , ref_evnts.currency as currency
            , ref_regs.regs_count as referrals
            , (ref_evnts.profit * IF(ref_evnts.currency = 1, 1,
            IF(ref_evnts.currency = 2, ".$courses['UAH'].",
            IF(ref_evnts.currency = 4, ".$courses['EUR'].",
            IF(ref_evnts.currency = 5, ".$courses['INR'].",
            IF(ref_evnts.currency = 7, ".$courses['USD'].",
            IF(ref_evnts.currency = 8, ".$courses['KZT'].", 1))))))) as profit
        ");

        $query->join('m_users', 'm_users.id', 'user_referrals.user_id');

        $query->leftJoin(DB::raw("(SELECT ref_id, currency, SUM(amount) as amount, SUM(profit) as profit
                                        from referral_events
                                        where currency is not NULL
                                        group by ref_id, currency
                                        ) as ref_evnts"), function ($join) {
            $join->on("ref_evnts.ref_id", '=', 'user_referrals.id');
        });
        $query->join(DB::raw("(SELECT ref_id, count(id) as regs_count
                                        from referral_events
                                        where type = 1
                                        group by ref_id
                                        ) as ref_regs"), function ($join) {
            $join->on("ref_regs.ref_id", '=', 'user_referrals.id');
        });
        $query->orderBy('profit', 'desc');
//        $query->having('referrals', '>', 0);
        $query->where('ref_regs.regs_count', '>', 0);
        $query->where('ref_evnts.profit', '>', 0);
        $data = $query->paginate(25);

        $this->renderAdaptive('referral-system', [
            'data' => $data,
            'currency_name' => $currency_name,
        ], $request->ajax() ? [] : ['layouts.main']);
        return $this->sendPreparedResponse();
    }

//    public function getLastWin(Request $request)
//    {
//        try {
//            $storage = CurrentWin::getWinStorage();
//            $wins = $storage->allCollection();
//        } catch (\Exception $e) {
//            Log::error('Error on win storage fetching.');
//            return new NoCookiesResponse('', 500);
//        }
//
//
//        $date = $request->get('date', date('2000-01-01 00:00:00'));
//        if (empty($date)) $date = date('1988-12-05 00:00:00');
//
//        $data = $wins->firstWhere('date', '>', $date);
//
//        if ($data) {
//
//            $game = GamesManager::getGameByCompoundId($data['system'] . '/' . $data['provider'] . '/' . $data['game_alias']);
//
//            if ($data['system'] == 'vegas' && empty($data['provider'])) {
//                $g = Vegas::findGame($data['game_alias']);
//                if ($g) {
//                    $data['provider'] = $g['SectionId'];
//                    $storage->update(['id' => $data['id']], ['provider' => $g['SectionId']]);
//                }
//            }
//
//            if ($game !== null) {
//                return new NoCookiesResponse([
//                    'game' => $data['last_game'],
//                    'user' => $data['nik'],
//                    'gameImageUrl' => $game->getImageUrl(),
//                    'date' => $data['date'],
//                    'win' => $data['sum'],
//                    'bet' => $data['bet'],
//                    'currency' => $data['currency'],
//                ]);
//            }
//        }
//
//        return new NoCookiesResponse('');
//    }

    public function showDeposit()
    {
        if (!Auth::check()) return redirect()->route('login');
        \session()->flash('showDeposit', true);
        return redirect()->route('landing');
    }

    public function showHavePromocode()
    {
        if (!Auth::check()) return redirect()->route('login');
        \session()->flash('showPromo', true);
        return redirect()->route('landing');
    }

    public function welcome()
    {
        if (!Auth::check()) return redirect()->route('login');
        //prolong flash values
        if (\session('justRegistered')) \session()->flash('justRegistered', true);
        if (\session('showDeposit')) \session()->flash('showDeposit', true);

        \session()->flash('promoModal', true);
        \session()->flash('justLoggedIn', Auth::id());
        return redirect()->route('landing');
    }
}
