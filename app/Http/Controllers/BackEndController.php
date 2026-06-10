<?php

namespace App\Http\Controllers;

use App\Events\MoneyDeposit;
use App\Helpers\Hlog;
use App\Models\Slc\UserTasks\UserTaskType;
use App\MUser;
use App\Services\KSlc\KSlc;
use App\Services\NatsService\NatsUserEvents;
use App\Services\Users\UserManager;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BackEndController extends Controller
{
    //
    public function depositCompleteEvent(Request $request)
    {
        try {
            Log::info(__METHOD__, $request->all());
            $data = $request->all();
            $user_id = $data['user_id'];
            $currency_id = $data['currency_id'];
            $amount = $data['amount'];
            $dep_n = $data['dep_n'];
            $bonus_id = $data['bonus_id'];
            $user = MUser::find($user_id);
            if ($user === null) {
                Log::error(__CLASS__.__METHOD__.' User not found', $data);
                return response('User not found', 404);
            }
            event(new MoneyDeposit($user, $amount, $currency_id));
            UserManager::addEvent($user->getId(), 'deposit');
            KSlc::getInstance()->dailyTasksCheckTaskByType($user->getId(), UserTaskType::MakeDeposit, Wallet::currencyName($currency_id));
            if ($dep_n === 1) {
                UserManager::addEvent($user->getId(), 'ftd');
                NatsUserEvents::firstDep($user->getId(), $amount, $currency_id, $user->ref_id, $user->click_id, $user->getFingerprint());
            }
            if ($dep_n < 4) UserManager::addEvent($user->getId(), 'dep' . $dep_n);
            if ($dep_n >= 4) UserManager::addEvent($user->getId(), 'dep4');
        } catch (\Throwable $e) {
            HLog::logException($e);
            return response($e->getMessage(), 500);
        }
        return response('OK');
    }
}
