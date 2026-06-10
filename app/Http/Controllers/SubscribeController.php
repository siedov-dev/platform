<?php

namespace App\Http\Controllers;

use App\Helpers\Popup;
use App\Models\BonusPatterns;
use App\Models\MailSubscribe;
use App\MUser;
use Illuminate\Http\Request;

class SubscribeController extends Controller
{
    public function subscribe(Request $request, string $lang, string $token)
    {
        $decoded = base64_decode($token);
        if (!preg_match('#\w*:\w*#', $decoded)) {
            return response('internal server error', 500);
        }
        $email = explode(':', $decoded)[0];
        /** @var MUser $user */
        $user = MUser::where('email', $email)->first();

        if ($user === null || $token !== $user->getSubscribeToken()) return response('internal server error', 500);

        MailSubscribe::subscribeUser($user->getId());

        if (!$user->getValue('got_subs_bonus', false)) {
            /** @var BonusPatterns $pattern */
            $pattern = BonusPatterns::find(BonusPatterns::PTN_SUBSCRIBE);
            $pattern->createForUser($user);
            $user->setValue('got_subs_bonus', true);
        }

        Popup::message(__('subscribe.subscribed'));
        return redirect()->route('landing');
    }

    public function unsubscribe(Request $request, string $lang, string $token)
    {
        $decoded = base64_decode($token);
        if (!preg_match('#\w*:\w*#', $decoded)) {
            return response('internal server error', 500);
        }
        $email = explode(':', $decoded)[0];
        /** @var MUser $user */
        $user = MUser::where('email', $email)->first();

        if ($user === null || $token !== $user->getSubscribeToken()) return response('internal server error', 500);

        MailSubscribe::unsubscribeUser($user->getId());

        Popup::message(__('subscribe.unsubscribed'));
        return redirect()->route('landing');
    }

    public function subscribeCheck(Request $request)
    {
        $user = MUser::getAuth();
        if ($request->boolean('subscribe')) {
            MailSubscribe::subscribeUser($user->getId());
        } else {
            MailSubscribe::unsubscribeUser($user->getId());
        }
    }

}
