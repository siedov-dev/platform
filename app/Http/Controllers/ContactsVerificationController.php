<?php

namespace App\Http\Controllers;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Events\EmailVerifiedEvent;
use App\Events\PhoneVerifiedEvent;
use App\Helpers\Localization;
use App\Helpers\Popup;
use App\Mail\MailSystemType;
use App\Models\BonusPatterns;
use App\Models\Crm\TelegramClient;
use App\Models\PersonalData;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\SMSManager;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class ContactsVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        /** @var MUser $user */
        $user = Auth::user();

        $email = $request->post('email', null);
        if (empty($email)) return response('Email is required', 400);

        if ($email !== $user->email) {
            $existingUser = MUser::where('email', $email)->first();
            if ($existingUser !== null) {
                return response(__('cabinet.emailAlreadyTaken'), 400);
            }
            $user->email = $email;
        }

//        $token = Str::random(16);
        $token = str_pad(random_int(0, 99999999), 8, '0');

        $user->email_verification_token = $token;
        $user->save();

        $view = view('email.emailVerification', ['user' => $user])->render();

        $letter = new \App\Email([
            'to' => $user->email,
            'subject' => 'KrakenCasino - confirm email',
            'html' => $view,
        ], MailSystemType::CustomerIo->value);

        $letter->send(true, true);

        CustomerIo::getInstance()->sendEvent($user->id, CioEventType::EmailVerify, [
            'email' => $user->email,
            'user_id' => $user->id,
            "language" => $user->locale,
            "locale" => $user->locale,
            'phone' => $user->phone,
            'email_verification_code' => $token,
        ]);

//        Mail::to($user)->send(new EmailVerification($user));

        return response('OK');
    }


    public function verifyEmail(Request $request)
    {
        $token = $request->get('token');
        Log::info('check email token:'. $request->get('token'));

        if (Auth::check()) {
            Localization::setAppLocale(Auth::user()->locale);
        }

        $email = $request->get('email', MUser::getAuth()?->email);

        if ($request->ajax()) {
            if (empty($token)) return $this->invalidLinkResponse();

            /** @var MUser $user */
            $user = MUser::where('email_verification_token', $token)
                ->where('email', $email)
                ->first();

            if ($user === null) return $this->invalidLinkResponse();
            Localization::setAppLocale($user->getLocale());

            if (!$user->getValue('email_verification_bonus', false)) {
                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_MAIL_CONFIRM);
                if ($pattern === null && !$pattern->isDisabled()) {
                    $bonus = $pattern->createForUser($user);
                    $user->setValue('email_verification_bonus', true);
                    $bonus = $pattern->createForUser($user);
                    if ($bonus !== null) {
                        KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "email verification");
                    }
                }
            }

            $user->email_verification_token = '';
            $user->save();
            event(new EmailVerifiedEvent($user, $user->email, $token));

            return response(__('cabinet.emailVerifiedSuccessfully'));
        } else {
            if (empty($token)) {
                Popup::message("Wrong token!");
                return redirect()->route('landing');
            }

            if (empty($email)) {
                Popup::message("Wrong email!");
                return redirect()->route('landing');
            }

            /** @var MUser $user */
            $user = MUser::where('email_verification_token', $token)
                ->where('email', $email)
                ->first();

            if ($user === null) {
                Popup::message("User not found!");
                return redirect()->route('landing');
            }

            if (!$user->getValue('email_verification_bonus', false)) {
                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_MAIL_CONFIRM);
                $bonus = $pattern->createForUser($user);
                if ($bonus !== null) {
                    KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "email verification");
                }
                $user->setValue('email_verification_bonus', true);
            }

            Localization::setAppLocale($user->getLocale());
            $user->email_verification_token = null;
            $user->save();

            if (!Auth::check()) {
                $user_token = Str::random(32);
                $user->token = $user_token;
                $user->save();
                Session::put('user_id', $user->id);
                Session::put('user_token', $user_token);
                Auth::login($user);
            }

            Popup::message(__('cabinet.emailVerifiedSuccessfully'));
            event(new EmailVerifiedEvent($user, $user->email, $token));

            return redirect()->route('landing');
        }
    }


    public function sendVerificationSMS(Request $request)
    {
        /** @var MUser $user */
        $user = Auth::user();
        $code = $user->once_pass;

        $new_code = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        if (empty($user->once_pass)) $code = $new_code;

        $phone = $request->post('phone', null);
        if (empty($phone)) return response(__('cabinet.phone_required'), 400);

        // Check if given phone is not taken.
        if ($phone !== $user->phone) {
            $existingUser = MUser::where('phone', $phone)->first();
            if ($existingUser !== null) {
                return response(__('cabinet.phoneAlreadyTaken'), 400);
            }
            if (!preg_match('#^\+?\d{5,}$#', $phone)) {
                return response(__('cabinet.phone_wrong_number'), 400);
            }
            $user->phone = $phone;
            $code = $new_code;
        }

//        if ($user->currency == UserWallet::CURRENCY_RUB) {
//            //disable verification sms
//            $user->once_pass = null;
//            $user->save();
//        } else {
            // Limit code sending once per minute.
//            if (!empty($user->once_pass_sent_at) && time() - strtotime($user->once_pass_sent_at) < 60) {
//                return response('OK');
//            }

            if (!empty($user->once_pass_sent_at) && time() - strtotime($user->once_pass_sent_at) > 3600) {
                $code = $new_code;
            }

            try{
                $result = SMSManager::sendSMS($user->phone, $code, currency: UserWallet::currencyName($user->currency));
                if ($result->success) {
                    Log::info('Sent code ' . $code);
                    $user->once_pass = $code;
                    $user->once_pass_sent_at = date('Y-m-d H:i:s');
                    $user->save();
                } else {
                    return response($result->error?? 'Error sending SMS', 400);
                }
            } catch (\Exception $e) {
                return response($e->getMessage(), 400);
            }
//        }

        return response('OK');
    }


    public function verifyPhone(Request $request)
    {
        $token = $request->get('token');
        if (empty($token)) return $this->invalidLinkResponse();

        /** @var MUser $user */
        $user = MUser::find(Auth::id());
        if ($user === null) return response('User not found');

        if ($user->once_pass !== $token) return $this->invalidLinkResponse();

        $user->once_pass = '';
        $user->save();

//        try {
//            if (!empty($user->phone)) {
//                $client = TelegramClient::where('m_user_id', $user->getId());
//                if ($client === null) {
//                    //create new manager's client
//                    $client = new TelegramClient();
//                    $client->m_user_id = $user->getId();
//                    $client->status = $client::STATUS_REGISTERED;
//                    $client->locale = $user->getLocale();
//                    $client->save();
//                }
//            }
//        } catch (\Exception $e) {
//            Log::error($e->getMessage());
//        }
        event(new PhoneVerifiedEvent($user, $user->phone));
        return response(__('cabinet.phoneVerifiedSuccessfully'));
    }


    public function setPersonalData(Request $request)
    {

        /** @var MUser $user */
        $user = Auth::user();

        if ($user->currency == UserWallet::CURRENCY_INR) {
            if (empty($request->name) || empty($request->surname)) {
                return response(__('cabinet.provide_name_birthday'), 400);
            }
        } else {
            if (empty($request->name) || empty($request->surname) || empty($request->birthday)) {
                return response(__('cabinet.provide_name_birthday'), 400);
            }

            if (empty($request->passport)) {
                return response(__('cabinet.provide_passport_data'), 400);
            }
        }


        $filename = '';

        if (!$request->hasFile('verification_img')) {
            return response(__('cabinet.image_required'), 400);
        }

        if ($request->hasFile('verification_img')) {
//            $validator = Validator::make($request->all(),
//                [
//                    'verification_img' => 'required|image|mimes:jpeg,png,jpg,bmp|max:8172'
//                ],
//                [
//                    'image' => __('cabinet.not_an_image'),
//                    'mimes' => __('cabinet.image_type_should_be'),
//                ]
//            );
//            if ($validator->fails()) {
//                return response(__('cabinet.not_an_image'), 400);
//            }
            if ($request->file('verification_img')->isValid()) {
                $filename = Auth::id() . '.' . $request->file('verification_img')->extension();
                $request->file('verification_img')->storeAs('verification_images', $filename, 'local');
            } else {
                return response(__('cabinet.not_an_image'), 400);
            }
        }

        $data = $user->personalData();

        if (empty($data->name)) {
            $data->status = PersonalData::STATUS_REQUESTED;
        } else {
            $data->status = PersonalData::STATUS_CHANGED;
        }

        $data->name = $request->post('name');
        $data->surname = $request->post('surname');
        $data->patr = $request->post('patr');
        $data->passport = $request->post('passport');
        $data->country = $request->post('country');
        $data->city = $request->post('city');
        $data->birthday = Carbon::parse($request->post('birthday'))->format('Y-m-d H:i:s');

        if (!empty($filename)) $data->filename = $filename;
        $data->save();

        return response(__('cabinet.data_transferred'));
    }


    private function invalidLinkResponse()
    {
        return response(__('cabinet.wrong_code'), 400);
    }
}
