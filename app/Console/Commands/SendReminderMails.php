<?php

namespace App\Console\Commands;

use App\Helpers\Hlog;
use App\MailPromotion\MailPromotionManager;
use App\Models\Bonuses\UserBonus;
use App\Models\MessageBlank;
use App\Models\Slc\Achievements\SlcAchievement;
use App\MUser;
use App\PayoutTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendReminderMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retention:send-remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send bonus remind letters';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        Log::channel('retention')->info('Письма напоминалки start');
        try {
            MailPromotionManager::wbRetention();
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        try {
            MailPromotionManager::reactivationRetention();
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        Log::channel('retention')->info('Письма напоминалки end');

//        //unactivated first dep bonuses 1 day and no deps
//        $day = 1;
//        $user_ids = UserBonus::selectRaw('users_bonuses.user_id, coalesce(payout_transactions.deps, 0) as deps')
//            ->leftJoin(DB::raw("(select count(id) deps, user_id
//                                        from payout_transactions
//                                        where status_id in (" . implode(',', PayoutTransaction::getIncomingStatuses()) . ")
//                                        group by user_id
//                                        ) as payout_transactions"), function ($join) {
//                $join->on("payout_transactions.user_id", '=', 'users_bonuses.user_id');
//            })
//            ->where('created_at', '>=', now()->subDays($day)->startOfDay())
//            ->where('created_at', '<=', now()->subDays($day)->endOfDay())
//            ->where('title', 'bonus.first_dep_bonus')
//            ->whereIn('status', [
//                UserBonus::STATUS_NOT_ACTIVATED,
//                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                UserBonus::STATUS_WAITING_FOR_PAYMENT,
//            ])
//            ->having('deps', 0)
//        ->get()->pluck('user_id')->toArray();
//
//        Log::channel('retention')->info('всем кто зарегился, и не активнул бонус на первый депозит и не сделал депозит:'. var_export($user_ids, true));
//
//        if (!empty($user_ids)) {
//            /** @var MessageBlank $blank */
//            $blank = MessageBlank::where('name', 'r1-1')->first();
//            $blank->sendMail($user_ids);
//        }
//
//        //unactivated first dep bonuses 1 day
//        $day = 3;
//        $user_ids = UserBonus::selectRaw('user_id')
//            ->where('created_at', '>=', now()->subDays($day)->startOfDay())
//            ->where('created_at', '<=', now()->subDays($day)->endOfDay())
//            ->where('title', 'bonus.first_dep_bonus')
//            ->whereIn('status', [
//                UserBonus::STATUS_NOT_ACTIVATED,
//                UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                UserBonus::STATUS_WAITING_FOR_PAYMENT,
//            ])
//            ->get()->pluck('user_id')->toArray();
//
//        Log::channel('retention')->info('Напоминаем всем что есть бонус на первый депозит через 3 дня ( Группа 1 Письмо 2.html):'. var_export($user_ids, true));
//
//        if (!empty($user_ids)) {
//            /** @var MessageBlank $blank */
//            $blank = MessageBlank::where('name', 'r1-2')->first();
//            $blank->sendMail($user_ids);
//        }
//
//        // first steps reminder
//
//        $first_steps_ach = SlcAchievement::where('type', 'first_steps')->first()->id;
//
//        //not made first steps yesterday
//        $qr = MUser::query();
//        $qr->selectRaw("
//            m_users.id as user_id, slc_user_achievements.achievement_id as ach_id
//        ");
//        $qr->leftJoin(DB::raw("(select user_id, achievement_id
//                                        from slc_user_achievements
//                                        where achievement_id = $first_steps_ach
//                                        ) as slc_user_achievements"), function ($join) {
//            $join->on("slc_user_achievements.user_id", '=', 'm_users.id');
//        });
//        $qr->where('m_users.created_at', '>=', now()->subDays(1)->startOfDay());
//        $qr->where('m_users.created_at', '<=', now()->subDays(1)->endOfDay());
//        $qr->havingNull('ach_id');
//
//        $user_ids = $qr->pluck('user_id')->toArray();
//
//        Log::channel('retention')->info('всем новым кто не открыл медаль вчера:'. var_export($user_ids, true));
//
//        if (!empty($user_ids)) {
//            /** @var MessageBlank $blank */
//            $blank = MessageBlank::where('name', 'r1-2')->first();
//            $blank->sendMail($user_ids);
//        }
//
//        // 21 days send cycle
//        $qr = MUser::query();
//        $qr->selectRaw("
//            m_users.id as user_id, DATEDIFF(NOW(), m_users.created_at) % 21 as days, slc_user_achievements.achievement_id as ach_id
//        ");
//        $qr->leftJoin(DB::raw("(select user_id, achievement_id
//                                        from slc_user_achievements
//                                        where achievement_id = $first_steps_ach
//                                        ) as slc_user_achievements"), function ($join) {
//            $join->on("slc_user_achievements.user_id", '=', 'm_users.id');
//        });
//        $qr->havingNull('ach_id');
//
//        $users = $qr->get();
//
//        /**
//         * days fists steps achievement not received => mail
//         */
//        $fs_mails = [
////            1 => 'steps-1',
//            3 => 'steps-2',
//            6 => 'steps-3',
//            9 => 'steps-4',
//            12 => 'steps-5',
//            15 => 'steps-6',
//            18 => 'steps-7',
//        ];
//
//        foreach ($fs_mails as $days => $mail_blank_name) {
//            $user_ids = $users->where('days', $days)->pluck('user_id')->toArray();
//            Log::channel('retention')->info("Не получившие медальку день цикла $days:". var_export($user_ids, true));
//            if (!empty($user_ids)) {
//                /** @var MessageBlank $blank */
//                $blank = MessageBlank::where('name', $mail_blank_name)->first();
//                $blank->sendMail($user_ids);
//            }
//        }
//
//        Log::channel('retention')->info('Письма напоминалки end');

        return Command::SUCCESS;
    }
}
