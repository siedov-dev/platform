<?php

namespace App\Http\Controllers;

use App\Helpers\Device;
use App\Helpers\Hlog;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\Models\Rank\UserRank;
use App\Models\Slc\FortuneWheelBonuses;
use App\Models\Slc\SlcConfig;
use App\Models\Slc\UserTasks\UserTaskType;
use App\Models\Slc\WheelSpinLog;
use App\Models\UserDailyStat;
use App\Models\UserWeeklyData;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\Services\KSlc\KSlc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Wheel of Fortune (Lucky spin)
 */
class WheelController extends Controller
{
    public function wheelModal(Request $request)
    {
        $m_user = MUser::getAuth();

        $todays_spin = WheelSpinLog::where('created_at', '>=', now()->startOfDay())
            ->where('user_id', $m_user->getId())
            ->orderBy('id', 'desc')
            ->first();

        $prizes = FortuneWheelBonuses::all();
        return $this->viewOrJson(Device::detect().'.blocks.wheel-of-fortune', compact('prizes', 'todays_spin'));
    }

    public function spin(Request $request)
    {
        $m_user = MUser::getAuth();

        // проверка ранга
        if ($m_user->getRank()->getLevel() < 2) return [
            'error' => 'rank-required',
            'modal' => view('common.rank-required-modal', [
                'title' => __('ranks.activation_required'),
                'rank_level' => 2,
                'rank' => UserRank::getForLvl(2, $m_user->getCurrency()),
                'user' => $m_user,
                'currency' => $m_user->getCurrency(),
            ])->render(),
        ];

        // получаем кошелек и конфигурацию
        $wallet = $m_user->getSlcWallet();
        /** @var SlcConfig $slc_config */
        $slc_config = SlcConfig::first();

        // получаем сегодняшний последний спин
        /** @var WheelSpinLog $todays_spin */
        $todays_spin = WheelSpinLog::where('created_at', '>=', now()->startOfDay())
            ->where('user_id', $m_user->getId())
            ->orderBy('id', 'desc')
            ->first();

        // бесплатные спины предоставлены итемом WheelSpin (не используются в данный момент)
        $user_free_spins = $m_user->getValue('wheel_spins', 0);
        // получаем к-во спинов за неделю
        $weekly_made_free_spins = UserWeeklyData::getIntValue($m_user->getId(), 'wheel_spin');
        // получаем к-во респинов за сегодня
        $respin = UserDailyStat::getIntValue($m_user->getId(), 'wheel_respin');

        // Если сегодня сделан спин
        if ($todays_spin !== null) {
//            // и получил бонус - проверяем бесплатные спины (недоступны)
//            if ($todays_spin->getBonusId() > 0) {
//                //check have enough free-spins
//                if ($user_free_spins <= 0) return [];
//                $user_free_spins--;
//                $m_user->setValue('wheel_spins', $user_free_spins);
//            // иначе респин re-spin
//            } else {
                // получаем цену респина
                $cost = SlcConfig::getWheelRespinCost($m_user);
                // сбрасывает текущий бонус
                $todays_spin->bonus_id = null;
                $todays_spin->save();
                if ($wallet->getBalance() < $cost) {
                    return response('Not enough balance', 402);
                }
                $wallet->rem($cost, 'WheelOfFortune respin');
                $respin++;
                // устанавливаем к-во респинов за сегодня
                UserDailyStat::setValue($m_user->getId(), 'wheel_respin', $respin);
//            }
        } else {
            // первый спин в неделю бесплатно
            if ($weekly_made_free_spins === 0) {
                UserWeeklyData::setValue($m_user->getId(), 'wheel_spin', 1);
            } else {
                // остальные за установленную цену
                $cost = $slc_config->getSpinPrice();
                if ($wallet->getBalance() < $cost) {
                    return response('Not enough balance', 402);
                }
                $wallet->rem($cost, 'WheelOfFortune spin');
            }
        }

        $prizes = FortuneWheelBonuses::all();
        // случайный выигрыш
        $win_id = random_int(1, $prizes->count()) - 1;
        /** @var FortuneWheelBonuses $prize */
        $prize = $prizes[$win_id];

        // пишем лог
        $wheel_log = new WheelSpinLog();
        $wheel_log->user_id = $m_user->getId();
        $wheel_log->whee_prize_id = $prize->getId();
        $wheel_log->bonus_type = $prize->getBonusType();
        $wheel_log->bonus_title = $prize->getBonusTitle();

        // определяем ширину секции в градусах
        $section_size = 360 / $prizes->count();
        $wheel_log->save();

        $cost = SlcConfig::getWheelRespinCost($m_user);
        KSlc::getInstance()->dailyTaskAddCurrentValue($m_user->getId(), UserTaskType::WheelSpin, "", 1);

        return [
            'win_id' => $win_id,
            //section
            'angle' => -1 * ($section_size * $win_id), // угол вращения
            'title' => __($prize->getBonusTitle()),
            'user_spins' => $user_free_spins, // бесплатные спины (не используются)
            'respin_cost' => $cost,
            'can_respin' => ($cost <= $wallet->getBalance() && $respin < 2),
            'respins' => $respin,
        ];

    }

    public function takeBonus(Request $request)
    {
        $m_user = MUser::getAuth();

        try {
            DB::beginTransaction();
            /** @var WheelSpinLog $todays_spin */
            $todays_spin = WheelSpinLog::where('created_at', '>=', now()->startOfDay())
                ->where('user_id', $m_user->getId())
                ->whereNull('bonus_id')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($todays_spin === null) throw new \Exception("Spin not found");

            $prize = FortuneWheelBonuses::findOrFail($todays_spin->getWheePrizeId());

            $bonus = BonusFactory::createFromParams(
                bonus_type: $prize->getBonusType(),
                user_id: $m_user->getId(),
                params: $prize->getBonusParams(),
                currency: $m_user->getCurrency(),
                tmp: false,
                creator_type: KUserBonus::CREATOR_WHEEL,
                creator_id: $prize->getId(),
                allowed_games: $prize->getBonusGames(),
            );

//            $bonus = UserBonus::fromParams(
//                bonus_type: $prize->getBonusType(),
//                params: $prize->getBonusParams(),
//                user_id: $m_user->getId(),
//                tmp: true,
//            );

//            if ($bonus !== null) {
////                $bonus->setAllowedGames($prize->getBonusGames());
//                $bonus->active_to = now()->addDay()->format('Y-m-d H:i:s');
//                $bonus->save();
//            }
            if ($bonus !== null) {
                KBonus::getInstance()->addBonusToSegmentByName($bonus->getId(), "wheel-of-fortune #".$prize->getId());
                $todays_spin->bonus_id = $bonus->getId();
                $todays_spin->save();
            }

        } catch (\Throwable $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return redirect()->route('cabinet.bonuses');
    }
}
