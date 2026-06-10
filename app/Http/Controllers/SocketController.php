<?php

namespace App\Http\Controllers;

use App\MUser;
use App\Services\KSocket\KSocket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SocketController extends Controller
{
    public function wsConnect(Request $request)
    {
        $user = MUser::getAuth();
        if ($user === null) return response('Unauthorized', 401);
        $socketService = KSocket::getInstance();
        $token = Str::random(32);
        $socketService->setAuthToken($user->getId(), $token);
        return $token;
    }
}
