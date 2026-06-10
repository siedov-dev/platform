<?php

namespace App\Http\Controllers;

use App\CustomerIo\CioEventType;
use App\CustomerIo\CustomerIo;
use App\Helpers\Hlog;
use App\Helpers\RdRand;
use App\Helpers\UI;
use App\Models\Slc\SlcConfig;
use App\Models\Slc\SlotMachine\SlcSlotMachineCombination;
use App\Models\Slc\SlotMachine\SlcSlotMachineLog;
use App\Models\Slc\SlotMachine\SlcSlotMachineSymbol;
use App\Models\Slc\UserTasks\UserTaskType;
use App\MUser;
use App\Services\KSlc\KSlc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SlcSlotMachineController extends Controller
{
    public function smModal(Request $request)
    {
        $combinations = SlcSlotMachineCombination::all();
        return $this->viewOrJson('common.blocks.slotmachine', compact('combinations'));
    }

    public function spin(Request $request)
    {
        $spin_n = intval($request->get('spinN'));
        $m_user = MUser::getAuth();
        $params = SlcConfig::getParams();
        $cost = SlcConfig::getBetCost();
        $wallet = $m_user->getSlcWallet();
        if ($wallet->getBalance() < $cost) {
            return response('Not enough balance', 402);
        }
        $wallet->rem($cost, 'SlotMachine spin');
        $params->addJackpotBet($cost);

        $result = [];
        $result['is_jackpot'] = false;
        $log = new SlcSlotMachineLog();
        $log->user_id = $m_user->getId();

        foreach (range(0,2) as $drum) {
            $symbols[$drum] = SlcSlotMachineSymbol::getForDrum($drum);
            if ($params->getRandomGen() === $params::RANDOM_GEN_DEFAULT) {
                $result[$drum] = random_int(0, $symbols[$drum]->count() - 1);
            } else {
                $result[$drum] = RdRand::randomInt(0, $symbols[$drum]->count() - 1);
            }
            $log['drum_'.$drum] = $result[$drum];
            $log['drum_'.$drum.'_symbol'] = $symbols[$drum][$result[$drum]]->getType()->value;
        }

        $win = SlcSlotMachineCombination::checkCombination($log->getDrum0Symbol(), $log->getDrum1Symbol(), $log->getDrum2Symbol());
        if ($win > 0) $wallet->add($win, 'SlotMachine win');
        //jackpot
        if ($win === -1) {
            $win = floor($params->getJackpot());
            $params->setJackpot($params->getStartJackpot());
            $params->save();
            $result['is_jackpot'] = true;
            $wallet->add($win, 'SlotMachine win');
        }
        $log->setReward($win);
        $log->save();

        $result['win'] = $win;
        $result['balance'] = $wallet->getBalance();
        $result['jackpot'] = UI::moneyStr(($params->getJackpot()));
        $result['bet'] = $params->getBet();

        KSlc::getInstance()->dailyTaskAddCurrentValue($m_user->getId(), UserTaskType::SlotmachineBet, "", 1 * $cost);

        if ($spin_n === 3) {
            try {
                CustomerIo::getInstance()->sendEvent($m_user->getId(), CioEventType::SlotMachineSpin, ['spin_n' => $spin_n]);
            } catch (\Exception $e) {
                Hlog::logException($e);
            }
        }

        return $result;
    }
}
