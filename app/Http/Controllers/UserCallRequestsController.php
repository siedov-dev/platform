<?php

namespace App\Http\Controllers;

use App\Models\UserValue;
use App\MUser;
use App\Services\KCms\KCms;
use Illuminate\Http\Request;

class UserCallRequestsController extends Controller
{
    public function renderCallRequestBlock()
    {
        if (!auth()->check()) {
            return "";
        }
        $user = MUser::getAuth();
        UserValue::clearValue($user->getId(), 'support_notify');

        if (!$user->isPhoneVerified(true) || !$user->isEmailVerified()) {
            $state = "disabled";
        } else {
            $resp = KCms::getInstance()->userCallRequestGetStatus($user->getId());
            if ($resp['cd'] !== 200) return "";
            $state = $resp['state']->value;
        }
        return $this->viewOrJson('common.blocks.user-call-request', compact('state'));
    }

    public function requestCall()
    {
        if (!auth()->check()) {
            return "";
        }
        $user = MUser::getAuth();
        if (!$user->isPhoneVerified(true) || !$user->isEmailVerified()) {
            $state = "disabled";
        } else {
            $cms = KCms::getInstance();
            $cms->userCallRequestMake($user->getId());
            $resp = $cms->userCallRequestGetStatus($user->getId());
            if ($resp['cd'] !== 200) return "";
            $state = $resp['state']->value;
        }
        return $this->viewOrJson('common.blocks.user-call-request', compact('state'));
    }
}
