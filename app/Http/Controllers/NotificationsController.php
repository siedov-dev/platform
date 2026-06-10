<?php

namespace App\Http\Controllers;

use App\Http\Responses\NoCookiesResponse;
use App\Models\UserNotifications;
use App\MUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;

class NotificationsController extends Controller
{
    //
    public function getNotifications(Request $request)
    {
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        if($user){
            $notifications = UserNotifications::where('user_id', $user->id)
                ->whereIn('status', [UserNotifications::STATUS_SENT, UserNotifications::STATUS_SHOWN])->get();
            return new NoCookiesResponse($notifications);
        };

        return new NoCookiesResponse('');
    }

    public function getNewNotifications()
    {
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        if($user){
            if(!empty(Redis::get($user->id.'notified'))) return new NoCookiesResponse('', 200);;
            $notifs = UserNotifications::where('user_id', $user->id)
                ->whereIn('status', [UserNotifications::STATUS_NEW])->get();

            UserNotifications::where('user_id', $user->id)
                ->whereIn('status', [UserNotifications::STATUS_NEW])->update(['status' => UserNotifications::STATUS_SENT]);

            Redis::set($user->id.'notified', 1);
            $response = new NoCookiesResponse(json_encode($notifs), 200);
            $response->header('Content-Type', 'application/json');
            return $response;
        };

        return new NoCookiesResponse('', 200);
    }

    public function setNotificationStatus(Request $request)
    {
        $data = $request->json()->all();
        /** @var MUser $user */
        $user = MUser::find(Auth::id());

        if($user){
            /** @var UserNotifications $notification */
            $notification = UserNotifications::where('user_id', $user->id)
                ->where('id', $data['n_id'])->first();

            if($notification){
                $notification->status = $data['status'];
                $notification->save();
            }
        }
    }

    public function getCongratsWindow(Request $request)
    {
        $text = Redis::get('congrats-'.Auth::id());
        Redis::del('congrats-'.Auth::id());
        return $this->viewOrJson('common.congrats', ['text' => $text]);
    }
}
