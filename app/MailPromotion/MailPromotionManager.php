<?php


namespace App\MailPromotion;

use App\Helpers\Hlog;
use App\Models\BonusAvailable;
use App\Models\BonusBlackList;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusPatterns;
use App\Models\BonusPromocode;
use App\Models\MessageBlank;
use App\PayoutTransaction;
use App\Models\UnsubscribedEmail;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class MailPromotionManager
 * Отправляет ретейшн письма
 * @package App\MailPromotion
 * @deprecated
 */
class MailPromotionManager
{
    public static function wbRetention(): void
    {
        set_time_limit(3600);
//        // first dep bonuses reminders
//        $remind_days = [2, 5, 7];
//
//        for ($dep_n = 1; $dep_n < 4; $dep_n++) {
//            //get users with not completed first dep bonuses or not activated welcome bonuses on state 1-3
//            // first dep pattern bonuses
//            $fd_ids = [];
//            foreach (UserWallet::activeCurrencies() as $currency) {
//                $ptn = BonusPatterns::getRegPattern(UserWallet::currencyName($currency), BonusPatterns::RB_TYPE_DEP, $dep_n);
//                if ($ptn !== null) $fd_ids[] = $ptn->getId();
//            }
//
//            foreach ($remind_days as $remind => $r_day) {
//                $bonuses = UserBonus::whereIn('pattern_id', $fd_ids)
//                    ->whereIn('status', [
//                        UserBonus::STATUS_NOT_ACTIVATED,
//                        UserBonus::STATUS_PENDING,
//                        UserBonus::STATUS_WAITING_FOR_AGREEMENT,
//                        UserBonus::STATUS_WAITING_FOR_PAYMENT,
//                        UserBonus::STATUS_ACTIVE,
//                    ])
//                    ->where('created_at', '>=', now()->subDays($r_day)->startOfDay())
//                    ->where('created_at', '<=', now()->subDays($r_day)->endOfDay())
//                    ->get();
//
//                $user_ids = $bonuses->pluck('user_id')->toArray();
//                if (count($user_ids) > 0) {
//                    //retention dep remind email send job
//                    $blank = 'ret2_dep' . $dep_n . '_' . $remind;
//                    Log::channel('retention')->info("Отправка напоминание о $dep_n деп бонусе через $r_day юзерам:" . var_export($user_ids, true));
//                    /** @var MessageBlank $blank */
//                    $blank = MessageBlank::where('name', $blank)->first();
//                    if ($blank === null) {
//                        Log::channel('mail')->error("$blank blank not found");
//                        return;
//                    }
//                    $blank->sendMail($user_ids, []);
//                }
//            }
//        }

        // first dep bonuses reminders for not activated welcome bonus
        $remind_days = [2, 5, 7];

        for ($dep_n = 1; $dep_n < 4; $dep_n++) {
            try {
                //get users with welcome bonus without dep
                foreach ($remind_days as $remind => $r_day) {
                    $bonuses = UserBonus::where('type', UserBonus::TYPE_WELCOME_BONUS_PACK)
                        ->whereIn('status', [
                            UserBonus::STATUS_NOT_ACTIVATED,
                            UserBonus::STATUS_PENDING,
                            UserBonus::STATUS_WAITING_FOR_AGREEMENT,
                            UserBonus::STATUS_WAITING_FOR_PAYMENT,
                        ])
                        ->where('tries', $dep_n);

                    if ($dep_n === 1) {
                        $bonuses->where('created_at', '>=', now()->subDays($r_day)->startOfDay())
                            ->where('created_at', '<=', now()->subDays($r_day)->endOfDay());
                    } else {
                        //when wb bonus gets deposit it sets the completed_at field to deposit time
                        $bonuses->where('completed_at', '>=', now()->subDays($r_day)->startOfDay())
                            ->where('completed_at', '<=', now()->subDays($r_day)->endOfDay());
                    }

                    $user_ids = $bonuses->pluck('user_id')->toArray();
                    Log::channel('retention')->info("Отправка напоминание о $dep_n деп бонусе через $r_day юзеров:" . count($user_ids));
                    if (count($user_ids) > 0) {
                        //retention dep remind email send job
                        $blank_name = 'ret2_dep' . $dep_n . '_' . ($remind + 1);
                        Log::channel('retention')->info("Отправка напоминание о $dep_n деп бонусе через $r_day юзерам:" . var_export($user_ids, true));
                        /** @var MessageBlank $blank */
                        $blank = MessageBlank::where('name', $blank_name)->first();
                        if ($blank === null) {
                            Log::channel('mail')->error("$blank_name blank not found");
                            continue;
                        }
                        $blank->sendMail($user_ids, []);
                    }
                }
            } catch (\Throwable $e) {
                Hlog::logException($e, 'retention');
            }
        }
    }

    public static function reactivationRetention(): void
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);

        Log::channel('retention')->info(__METHOD__ . " process reactivation2 promo");

        $black_list_ids = BonusBlackList::all()->pluck('user_id')->toArray();

        $all_users = MUser::selectRaw('m_users.*, deposits.last_dep_date as last_dep_date, TIMESTAMPDIFF(DAY, deposits.last_dep_date, NOW()) as days')
            ->whereNotIn('m_users.id', $black_list_ids)
            ->join(DB::raw("(select user_id, sum(sum) as total_deposit, COUNT(id) as deposit_cnt, min(date) as first_dep_date, max(date) as last_dep_date
                                        from payout_transactions
                                        where status_id in (" . implode(", ", PayoutTransaction::getIncomingStatuses()) . ")
                                        group by user_id) as deposits"), function ($join) {
                $join->on('deposits.user_id', '=', 'm_users.id');
            })
            ->join(DB::raw("(select user_id
                                        from users_bonuses
                                        where type = ".UserBonus::TYPE_WELCOME_BONUS_PACK."
                                        and status in (" . implode(", ", [
                                            UserBonus::STATUS_DONE,
                                            UserBonus::STATUS_CANCELED,
                                            UserBonus::STATUS_EXPIRED,
                                        ]) . ")
                                        group by user_id) as wb"), function ($join) {
                $join->on('wb.user_id', '=', 'm_users.id');
            })
            ->get();

        Log::channel('retention')->info(__METHOD__ . " all_users count:" . $all_users->count());
        $days = [
            7 => 'ret2_reactive1',
            14 => 'ret2_reactive2',
            21 => 'ret2_reactive3',
            31 => 'ret2_reactive4',
        ];

        foreach ($days as $day => $blank_name) {
            $users = $all_users->where('days', $day);
            Log::channel('retention')->info(__METHOD__ . " all_users $day days count:" . $users->count());

            if (!empty($users->count() > 0)) {
                Log::channel('retention')->debug("sending $blank_name");
                try {
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $ids[] = $user->id;
                    }
                    /** @var MessageBlank $blank */
                    $blank = MessageBlank::where('name', $blank_name)->first();
                    if ($blank !== null) {
                        $blank->sendMail($ids);
                    } else {
                        Log::channel('retention')->error("blank $blank_name not found!");
                    }
                } catch (\Exception $e) {
                    Hlog::logException($e, 'retention');
                }
            }
        }
    }

    //send promotions to users without deposits
    public static function nonDepPromo()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(3600);
        $unsub_emails = UnsubscribedEmail::all()->pluck('email')->toArray();
        $black_list_ids = BonusBlackList::all()->pluck('user_id')->toArray();

        Log::channel('retention')->info(__CLASS__ . __FUNCTION__ . 'отправка промо пользователям без депозитов');
        $query = MUser::query();
        $query->whereNotIn('m_users.id', function ($query) {
            $query->selectRaw('DISTINCT user_id')
                ->from('payout_transactions')
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses());
        });
        // 36-day cycle starts 30 days after reg (creation)
        $query->selectRaw('m_users.id as id, MOD((ABS(DATEDIFF(NOW(), DATE(m_users.created_at))) -30 ) , 36) + 30 AS reg_day, ABS(DATEDIFF(NOW(), DATE(m_users.created_at))) as reg_diff');
        $query->havingRaw('reg_diff >= 30');

        //30 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 30);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 30 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            Log::channel('retention')->info(__FUNCTION__ . ' 30 дней ids:' . var_export($ids, true));
            try {
                $promocode = mb_strtoupper(Str::random(10));
                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_20SPINS_REACTIVATION);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_20SPINS_REACTIVATION);
                }

                //create bonus promocode
//                $bonus_promo = new BonusPromocode();
//                $bonus_promo->allowed_users = json_encode($ids);
//                $bonus_promo->allowed_games = '{"pragplay":["vs20sugarrush"]}';
//                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
//                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
//                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_20SPINS_REACTIVATION]);
//                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
//                $bonus_promo->promocode = $promocode;
//                $bonus_promo->quantity = count($ids);
//                $bonus_promo->hall_id = 237;
//                $bonus_promo->save();
//                Log::channel('retention')->info(__FUNCTION__ . ' промокод id#' . $bonus_promo->id);

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_1')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_1 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //34 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 34);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 34 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 34 дней ids:' . var_export($ids, true));
                $promocode = mb_strtoupper(Str::random(10));

                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_40SPINS_REACTIVATION);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_40SPINS_REACTIVATION);
                }

//                Log::channel('retention')->info(__FUNCTION__ . ' создаю промокод:' . $promocode);
//                //create bonus promocode
//                $bonus_promo = new BonusPromocode();
//                $bonus_promo->allowed_users = json_encode($ids);
//                $bonus_promo->allowed_games = '{"pragplay":["vs20starlight"]}';
//                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
//                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
//                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_40SPINS_REACTIVATION]);
//                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
//                $bonus_promo->promocode = $promocode;
//                $bonus_promo->quantity = count($ids);
//                $bonus_promo->hall_id = 237;
//                $bonus_promo->save();
//                Log::channel('retention')->info(__FUNCTION__ . ' промокод id#' . $bonus_promo->id);

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_2')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_2 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //38 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 38);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 40 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 40 дней ids:' . var_export($ids, true));
                $promocode = mb_strtoupper(Str::random(10));

                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_50SPINS_REACTIVATION);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                    Log::channel('retention')->info(__FUNCTION__ . ' создан доступный бонус id#' . $bonus->id);
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_50SPINS_REACTIVATION);
                }

//                Log::channel('retention')->info(__FUNCTION__ . ' создаю промокод:' . $promocode);
//                //create bonus promocode
//                $bonus_promo = new BonusPromocode();
//                $bonus_promo->allowed_users = json_encode($ids);
//                $bonus_promo->allowed_games = '{"pragplay":["vs20doghouse"]}';
//                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
//                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
//                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_50SPINS_REACTIVATION]);
//                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
//                $bonus_promo->promocode = $promocode;
//                $bonus_promo->quantity = count($ids);
//                $bonus_promo->hall_id = 237;
//                $bonus_promo->save();
//                Log::channel('retention')->info(__FUNCTION__ . ' промокод id#' . $bonus_promo->id);

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_3')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_3 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //42 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 42);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 42 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 42 дней ids:' . var_export($ids, true));
                $promocode = mb_strtoupper(Str::random(10));
                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_REACTIVATION_DEPOSIT1);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                    Log::channel('retention')->info(__FUNCTION__ . ' создан доступный бонус id#' . $bonus->id);
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_REACTIVATION_DEPOSIT1);
                }

//                Log::channel('retention')->info(__FUNCTION__ . ' создаю промокод:' . $promocode);
//                //create bonus promocode
//                $bonus_promo = new BonusPromocode();
//                $bonus_promo->allowed_users = json_encode($ids);
//                $bonus_promo->allowed_games = '{"pragplay":["vs40wildwest"]}';
//                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
//                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
//                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_REACTIVATION_DEPOSIT1]);
//                $bonus_promo->active_to = now()->addDays(5)->format('Y-m-d H:i:s');
//                $bonus_promo->promocode = $promocode;
//                $bonus_promo->quantity = count($ids);
//                $bonus_promo->hall_id = 237;
//                $bonus_promo->save();
//                Log::channel('retention')->info(__FUNCTION__ . ' промокод id#' . $bonus_promo->id);

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_4')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_4 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //46 days after reg
        $qr = clone $query;
        $qr->join('users_bonuses', 'users_bonuses.user_id', 'm_users.id');
        $qr->where('users_bonuses.pattern_id', BonusPatterns::PTN_REACTIVATION_DEPOSIT1);
        $qr->whereIn('users_bonuses.status', [
            UserBonus::STATUS_NOT_ACTIVATED,
            UserBonus::STATUS_PENDING,
            UserBonus::STATUS_WAITING_FOR_PAYMENT,
            UserBonus::STATUS_WAITING_FOR_AGREEMENT,
            UserBonus::STATUS_LOCKED,
        ]);
        $qr->having('reg_day', 46);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 46 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 46 дней ids:' . var_export($ids, true));
                $promocode = '';

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_5')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_5 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //50 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 54);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 50 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 50 дней ids:' . var_export($ids, true));
                $promocode = mb_strtoupper(Str::random(10));
                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_REACTIVATION_DEPOSIT2);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                    Log::channel('retention')->info(__FUNCTION__ . ' создан доступный бонус id#' . $bonus->id);
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_REACTIVATION_DEPOSIT2);
                }

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_6')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_6 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //54 days after reg
        $qr = clone $query;
        $qr->join('users_bonuses', 'users_bonuses.user_id', 'm_users.id');
        $qr->where('users_bonuses.pattern_id', BonusPatterns::PTN_REACTIVATION_DEPOSIT2);
        $qr->whereIn('users_bonuses.status', [
            UserBonus::STATUS_NOT_ACTIVATED,
            UserBonus::STATUS_PENDING,
            UserBonus::STATUS_WAITING_FOR_PAYMENT,
            UserBonus::STATUS_WAITING_FOR_AGREEMENT,
            UserBonus::STATUS_LOCKED,
        ]);
        $qr->having('reg_day', 54);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 54 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 54 дней ids:' . var_export($ids, true));
                $promocode = '';

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_7')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_7 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }


        set_time_limit(3600);
        //58 days after reg
        $qr = clone $query;
        $qr->having('reg_day', 58);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 58 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 58 дней ids:' . var_export($ids, true));
                $promocode = mb_strtoupper(Str::random(10));
                Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                /** @var BonusPatterns $pattern */
                $pattern = BonusPatterns::find(BonusPatterns::PTN_REACTIVATION_DEPOSIT3);
                if ($pattern !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                    $bonus = new BonusAvailable();
                    $bonus->allowed_users = json_encode($ids);
                    $bonus->games = $pattern->allowed_games;
                    $bonus->bonus_type = $pattern->bonus_type;
                    $bonus->params = $pattern->bonus_params;
                    $bonus->available_from = now()->format('Y-m-d H:i:s');
                    $bonus->available_to = now()->addDays(4)->format('Y-m-d H:i:s');
                    $bonus->pattern_id = $pattern->id;
                    $bonus->save();
                    Log::channel('retention')->info(__FUNCTION__ . ' создан доступный бонус id#' . $bonus->id);
                } else {
                    Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . BonusPatterns::PTN_REACTIVATION_DEPOSIT3);
                }

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_8')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_8 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        set_time_limit(3600);
        //62 days after reg
        $qr = clone $query;
        $qr->join('users_bonuses', 'users_bonuses.user_id', 'm_users.id');
        $qr->where('users_bonuses.pattern_id', BonusPatterns::PTN_REACTIVATION_DEPOSIT3);
        $qr->whereIn('users_bonuses.status', [
            UserBonus::STATUS_NOT_ACTIVATED,
            UserBonus::STATUS_PENDING,
            UserBonus::STATUS_WAITING_FOR_PAYMENT,
            UserBonus::STATUS_WAITING_FOR_AGREEMENT,
            UserBonus::STATUS_LOCKED,
        ]);
        $qr->having('reg_day', 62);
        $ids = $qr->pluck('id')->toArray();
        Log::channel('retention')->info(__FUNCTION__ . ' 62 дней к-во:' . count($ids));
        if (count($ids) > 0) {
            try {
                Log::channel('retention')->info(__FUNCTION__ . ' 62 дней ids:' . var_export($ids, true));
                $promocode = '';

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'reactivation_9')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => $promocode]);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank reactivation_9 not found!');
                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

        Log::channel('retention')->info(__FUNCTION__ . ' Завершено!');
    }

    private static array $patterns = [
        BonusPatterns::PTN_RET_1,
        BonusPatterns::PTN_RET_2,
        BonusPatterns::PTN_RET_3,
        BonusPatterns::PTN_RET_4,
        BonusPatterns::PTN_RET_5,
    ];

    /**
     * send promotional emails
     * called from cron(App\Console\Kernel)
     */
    public static function retention()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(3600);
        Log::channel('retention')->info(__CLASS__ . " process");
        Log::channel('retention')->info(__CLASS__ . __FUNCTION__ . 'отправка промо пользователям с пополнением > 7 дней');
        $query = MUser::query();
        //total deps
        $query->leftJoin(DB::raw("(select user_id, max(date) as last_dep_date
                                        from slotmashine.payout_transactions
                                        where status_id in (" . implode(", ", PayoutTransaction::getIncomingStatuses()) . ")
                                        group by user_id) as total_deposits"), function ($join) {
            $join->on("total_deposits.user_id", '=', 'slotmashine.m_users.id');
        });

        // 35-day cycle starts 7 days after last dep (creation)
        $query->selectRaw('m_users.id as id, MOD((ABS(DATEDIFF(NOW(), DATE(total_deposits.last_dep_date))) -7 ) , 35) + 7 AS dep_day, ABS(DATEDIFF(NOW(), DATE(total_deposits.last_dep_date))) as dep_diff');
        $query->havingRaw('dep_diff >= 7');

        for ($bonus_number = 1; $bonus_number < 6; $bonus_number++) {
            //7 days after dep
            $qr = clone $query;
            $qr->having('dep_day', $bonus_number * 7);
            $ids = $qr->pluck('id')->toArray();
            Log::channel('retention')->info(__FUNCTION__ . ($bonus_number * 7) . ' дней к-во:' . count($ids));
            if (count($ids) > 0) {
                Log::channel('retention')->info(__FUNCTION__ . ($bonus_number * 7) . ' дней ids:' . var_export($ids, true));
                try {
                    $promocode = '';
                    Log::channel('retention')->info(__FUNCTION__ . ' создаю доступный бонус');

                    /** @var BonusPatterns $pattern */
                    $pattern = BonusPatterns::find(self::$patterns[$bonus_number - 1]);
                    if ($pattern !== null) {
                        Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                        $bonus = new BonusAvailable();
                        $bonus->allowed_users = json_encode($ids);
                        $bonus->games = $pattern->allowed_games;
                        $bonus->bonus_type = $pattern->bonus_type;
                        $bonus->params = $pattern->bonus_params;
                        $bonus->pattern_id = $pattern->id;
                        $bonus->available_from = now()->format('Y-m-d H:i:s');
                        $bonus->available_to = now()->addDays(5)->format('Y-m-d 23:59:59');
                        $bonus->save();
                    } else {
                        Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . self::$patterns[$bonus_number - 1]);
                    }

                    MUser::whereIn('id', $ids)
                        ->update([
                            'scenario_step_index' => $bonus_number,
                            'scenario_instance_id' => time(),
                        ]);

                    /** @var MessageBlank $blank */
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'ret_' . $bonus_number)->first();
                    if ($blank !== null) {
                        Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                        Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                    } else {
                        Log::channel('retention')->error('blank not found! #' . 'ret_' . $bonus_number);
                    }

                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                    Log::channel('retention')->error($e->getFile());
                    Log::channel('retention')->error($e->getLine());
                    Log::channel('retention')->error($e->getTraceAsString());
                }
            }
        }

        //remind about ending
        $available_bonuses = BonusAvailable::where('available_to', now()->addDay()->format('Y-m-d 23:59:59'))
            ->whereIn('pattern_id', self::$patterns)
            ->get();
        /** @var BonusAvailable $bonus_av */
        foreach ($available_bonuses as $bonus_av) {
            try {
                $ids = array_diff($bonus_av->getAvailableIds(), $bonus_av->getActivatedIds());
                if (!empty($ids)) {
                    Log::channel('retention')->info('Отправляю напоминание не активировавшим доступный бонус #' . $bonus_av->getId());
                    Log::channel('retention')->info(__FUNCTION__ . ' ids к-во:' . count($ids));
                    Log::channel('retention')->info(__FUNCTION__ . ' ids:' . var_export($ids, true));
                }
                $bonus_number = $bonus_av->pattern_id - BonusPatterns::PTN_RET_1 + 1;

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'ret_' . $bonus_number . '_1')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                    $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    $blank->sendAll($ids, ['promocode' => '']);
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank not found! #' . 'ret_' . $bonus_number);
                }
            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }

    }

    public static function retentionDeps()
    {
        Log::channel('retention')->info(__CLASS__ . __FUNCTION__ . 'отправка промо пользователям с депом после письма');
        //users made deposit after bonus
        $four_days_ago = now()->subDays(1);
        Log::channel('retention')->info(__CLASS__ . __FUNCTION__ . 'депозиты до ' . $four_days_ago);


        $query = PayoutTransaction::query();
        $query->selectRaw('user_id, DATE(min(payout_transactions.date)) as dep_date, m_users.scenario_step_index as bonus_number, DATE(FROM_UNIXTIME(m_users.scenario_instance_id)) as bonus_date');
        $query->join('m_users', 'm_users.id', 'payout_transactions.user_id');
        $query->whereRaw('payout_transactions.date >= FROM_UNIXTIME(m_users.scenario_instance_id)');
        $query->where('m_users.scenario_step_index', '>', 0);
        $query->whereIn('payout_transactions.status_id', PayoutTransaction::getIncomingStatuses());
        $query->groupBy('user_id');
        $query->groupBy('m_users.scenario_step_index');
        $query->groupBy('m_users.scenario_instance_id');

        $query->having('dep_date', '<=', $four_days_ago->format('Y-m-d'));

        $users = $query->get();
        $bonus_number = 0;
        foreach (self::$patterns as $pattern_id) {
            ++$bonus_number;
            $ids = $users->where('bonus_number', '=', $bonus_number)->pluck('user_id')->toArray();
            Log::channel('retention')->info(__FUNCTION__ . $bonus_number . 'й бонус к-во:' . count($ids));
            if (count($ids) > 0) {
                Log::channel('retention')->info(__FUNCTION__ . $bonus_number . 'й бонус ids:' . var_export($ids, true));
                try {
                    $promocode = '';
                    Log::channel('retention')->info(__FUNCTION__ . ' создаю слелующий доступный бонус');
                    $nex_number = $bonus_number + 1;
                    if ($nex_number > count(self::$patterns)) $nex_number = 1;

                    /** @var BonusPatterns $pattern */
                    $pattern = BonusPatterns::find(self::$patterns[$nex_number - 1]);
                    if ($pattern !== null) {
                        Log::channel('retention')->info(__FUNCTION__ . ' шаблон найден id#' . $pattern->id);
                        $bonus = new BonusAvailable();
                        $bonus->allowed_users = json_encode($ids);
                        $bonus->games = $pattern->allowed_games;
                        $bonus->bonus_type = $pattern->bonus_type;
                        $bonus->params = $pattern->bonus_params;
                        $bonus->pattern_id = $pattern->id;
                        $bonus->available_from = now()->format('Y-m-d H:i:s');
                        $bonus->available_to = now()->addDays(5)->format('Y-m-d 23:59:59');
                        $bonus->save();
                    } else {
                        Log::channel('retention')->error(__FUNCTION__ . ' не найден шаблон #' . self::$patterns[$nex_number - 1]);
                    }

                    MUser::whereIn('id', $ids)
                        ->update([
                            'scenario_step_index' => $nex_number,
                            'scenario_instance_id' => time(),
                        ]);

                    /** @var MessageBlank $blank */
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'ret_' . $nex_number)->first();
                    if ($blank !== null) {
                        Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                        Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                    } else {
                        Log::channel('retention')->error('blank not found! #' . 'ret_' . $nex_number);
                    }

                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                    Log::channel('retention')->error($e->getFile());
                    Log::channel('retention')->error($e->getLine());
                    Log::channel('retention')->error($e->getTraceAsString());
                }
            }
        }

        /*
select user_id, DATE(min(payout_transactions.date)) as dep_date, m_users.scenario_step_index bonus_number, DATE(FROM_UNIXTIME(m_users.scenario_instance_id)) as bonus_date
    from payout_transactions
    join m_users on (m_users.id = payout_transactions.user_id)
    where payout_transactions.date >= FROM_UNIXTIME(m_users.scenario_instance_id)
    and m_users.scenario_step_index > 0
    GROUP by user_id;
                         */
    }

    public static function processReactivationPromo()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(1800);

        Log::channel('retention')->info(__CLASS__ . " process reactivation promo");

        $black_list_ids = BonusBlackList::all()->pluck('user_id')->toArray();

        $all_users = MUser::selectRaw('m_users.*, deposits.last_dep_date as last_dep_date, TIMESTAMPDIFF(DAY, deposits.last_dep_date, NOW()) as days')
            ->whereNotIn('m_users.id', $black_list_ids)
            ->join(DB::raw("(select user_id, sum(sum) as total_deposit, COUNT(id) as deposit_cnt, min(date) as first_dep_date, max(date) as last_dep_date
                                        from payout_transactions
                                        where status_id in (" . implode(", ", PayoutTransaction::getIncomingStatuses()) . ")
                                        group by user_id) as deposits"), function ($join) {
                $join->on('deposits.user_id', '=', 'm_users.id');
            })
            ->get();

        Log::channel('retention')->info(__CLASS__ . " all_users count:" . $all_users->count());

        // 20 freespins
        $users = $all_users->where('days', 30);
        Log::channel('retention')->info(__CLASS__ . " all_users 30 days count:" . $users->count());

        if (!empty($users->count() > 0)) {
            Log::channel('retention')->debug("Creating promo for users with last deposit 30 days ago");
            try {
                $promocode = mb_strtoupper(Str::random(10));
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = json_encode(['vegas' => ['berry_burst']]); //Berry Burst Vegas
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_20SPINS_REACTIVATION]);
                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 1 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_1')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_1 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }
            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        // 40 freespins
        $users = $all_users->where('days', 33);
        Log::channel('retention')->info(__CLASS__ . " all_users 33 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 33 days ago");
            try {
                $promocode = Str::random(10);
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = json_encode(['vegas' => ['magic_portals_touch']]); //Magic Portals touch от vegas
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_40SPINS_REACTIVATION]);
                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 2 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_2')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_2 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }
            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        // 50 freespins
        $users = $all_users->where('days', 36);
        Log::channel('retention')->info(__CLASS__ . " all_users 36 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 36 days ago");
            try {
                $promocode = Str::random(10);
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = json_encode(['vegas' => ['qso_leprechaunhills']]); //Leprechaun Hills от vegas
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_50SPINS_REACTIVATION]);
                $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 3 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_3')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_3 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        // deposit 125%
        $users = $all_users->where('days', 40);
        Log::channel('retention')->info(__CLASS__ . " all_users 40 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 40 days ago");
            try {
                $promocode = Str::random(10);
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = null;
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_REACTIVATION_DEPOSIT1]);
                $bonus_promo->active_to = now()->addDays(5)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 4 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_4')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_4 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        //remind about not activated deposit 125
        $users = $all_users->where('days', 43);
        Log::channel('retention')->info(__CLASS__ . " all_users 43 days count:" . $users->count());

        if (!empty($users)) {
            Log::channel('retention')->debug("remind promo for users with last deposit 43 days ago");
            try {
//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 5 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_5')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, []);
                    } else {
                        Log::channel('retention')->error('blank reactivation_5 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        // deposit 144%
        $users = $all_users->where('days', 47);
        Log::channel('retention')->info(__CLASS__ . " all_users 47 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 47 days ago");
            try {
                $promocode = Str::random(10);
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = null;
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_REACTIVATION_DEPOSIT2]);
                $bonus_promo->active_to = now()->addDays(5)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 6 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_6')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_6 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        //remind about not activated deposit 144
        $users = $all_users->where('days', 50);
        Log::channel('retention')->info(__CLASS__ . " all_users 50 days count:" . $users->count());

        if (!empty($users)) {
            try {
//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 7 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_7')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, []);
                    } else {
                        Log::channel('retention')->error('blank reactivation_7 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        // deposit 177%
        $users = $all_users->where('days', 54);
        Log::channel('retention')->info(__CLASS__ . " all_users 54 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 47 days ago");
            try {
                $promocode = Str::random(10);
                //create bonus promocode
                $bonus_promo = new BonusPromocode();
                $bonus_promo->allowed_users = json_encode($users->pluck('id')->toArray());
                $bonus_promo->allowed_games = null;
                $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                $bonus_promo->bonus_type = BonusPromocode::BONUS_PATTERN;
                $bonus_promo->bonus_params = json_encode(['pattern_id' => BonusPatterns::PTN_REACTIVATION_DEPOSIT3]);
                $bonus_promo->active_to = now()->addDays(5)->format('Y-m-d H:i:s');
                $bonus_promo->promocode = $promocode;
                $bonus_promo->quantity = $users->count();
                $bonus_promo->hall_id = 237;
                $bonus_promo->save();

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 8 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_8')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, ['promocode' => $promocode]);
                    } else {
                        Log::channel('retention')->error('blank reactivation_8 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }

        //remind about not activated deposit 177
        $users = $all_users->where('days', 57);
        Log::channel('retention')->info(__CLASS__ . " all_users 57 days count:" . $users->count());

        if (!empty($users)) {
            Log::debug("Creating promo for users with last deposit 50 days ago");
            try {

//                foreach (Localization::getAvailableLocales() as $locale) {
                try {
//                        Log::channel('retention')->info("reactivation promo sending subject 9 mail to locale $locale");
//                        Localization::setAppLocale($locale);
//                        $locale_users = $users->where('locale', $locale);
//                        Log::channel('retention')->info("users count" . $locale_users->count());
                    $emails = [];
                    $ids = [];
                    /** @var MUser $user */
                    foreach ($users as $user) {
                        $emails[] = $user->email;
                        $ids[] = $user->id;
                    }
                    $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                        ->where('name', 'reactivation_9')->first();
                    if ($blank !== null) {
                        $blank->sendManagersTelegram($ids, ['promocode' => $promocode]);
                        $blank->popupMessages($ids, ['promocode' => $promocode, 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        $blank->sendAll($ids, []);
                    } else {
                        Log::channel('retention')->error('blank reactivation_9 not found!');
                    }
                } catch (\Exception $e) {
                    Log::channel('retention')->error($e->getMessage());
                }
//                }

            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
            }
        }
    }

    private static function saveToHistory($text, $subject)
    {
        DB::table('all_email_history')
            ->insert([
                'total' => 1,
                'sent' => 1,
                'not_sent' => 0,
                'title' => $subject,
                'text' => $text,
                'html' => $text,
                'sender' => 'info@krakencassino.com',
                'created_at' => now()->format('Y-m-d H:i:s')
            ]);
    }

    public static function resendTodaysLetters()
    {
        $blanks = [
            BonusPatterns::PTN_20SPINS_REACTIVATION => 'reactivation_1',
            BonusPatterns::PTN_40SPINS_REACTIVATION => 'reactivation_2',
            BonusPatterns::PTN_50SPINS_REACTIVATION => 'reactivation_3',
            BonusPatterns::PTN_REACTIVATION_DEPOSIT1 => 'reactivation_4',
            BonusPatterns::PTN_REACTIVATION_DEPOSIT2 => 'reactivation_6',
            BonusPatterns::PTN_REACTIVATION_DEPOSIT3 => 'reactivation_8',
            BonusPatterns::PTN_RET_1 => 'ret_1',
            BonusPatterns::PTN_RET_2 => 'ret_2',
            BonusPatterns::PTN_RET_3 => 'ret_3',
            BonusPatterns::PTN_RET_4 => 'ret_4',
            BonusPatterns::PTN_RET_5 => 'ret_5',
        ];

        $bonuses = BonusAvailable::where('created_at', '>=', now()->startOfDay()->format('Y-m-d H:i:s'))
            ->whereNotNull('pattern_id')
            ->get();

        /** @var BonusAvailable $bonus */
        foreach ($bonuses as $bonus) {
            $all_ids = json_decode($bonus->allowed_users, true) ?? [];
            $completed_ids = json_decode($bonus->users_activated, true) ?? [];
            $ids = array_diff($all_ids, $completed_ids);

            if (!empty($ids)) {
                $blank_name = $blanks[$bonus->pattern_id] ?? '';
                if (!empty($blank_name)) {
                    /** @var MessageBlank $blank */
                    $blank = MessageBlank::where('name', $blank_name)->first();
                    if ($blank !== null) {
                        Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                        $blank->sendAll($ids, ['promocode' => '']);
                        $blank->popupMessages($ids, ['promocode' => '', 'route' => route('cabinet.bonuses', [], false)], 'retention');
                        Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                    } else {
                        Log::channel('retention')->error('blank not found! #' . $blank_name);
                    }
                }
            }
        }


        //remind about ending
        $available_bonuses = BonusAvailable::where('available_to', now()->addDay()->format('Y-m-d 23:59:59'))
            ->whereIn('pattern_id', self::$patterns)
            ->get();
        /** @var BonusAvailable $bonus_av */
        foreach ($available_bonuses as $bonus_av) {
            try {
                $ids = array_diff($bonus_av->getAvailableIds(), $bonus_av->getActivatedIds());
                if (!empty($ids)) {
                    Log::channel('retention')->info('Отправляю напоминание не активировавшим доступный бонус #' . $bonus_av->getId());
                    Log::channel('retention')->info(__FUNCTION__ . ' ids к-во:' . count($ids));
                    Log::channel('retention')->info(__FUNCTION__ . ' ids:' . var_export($ids, true));
                }
                $bonus_number = $bonus_av->pattern_id - BonusPatterns::PTN_RET_1 + 1;

                /** @var MessageBlank $blank */
                $blank = MessageBlank::where('type', MessageBlank::TYPE_RETENTION)
                    ->where('name', 'ret_' . $bonus_number . '_1')->first();
                if ($blank !== null) {
                    Log::channel('retention')->info(__FUNCTION__ . ' отправляю письма бланком#' . $blank->id);
                    $blank->sendAll($ids, ['promocode' => '']);
                    $blank->sendManagersTelegram($ids, ['promocode' => '']);
                    $blank->popupMessages($ids, ['promocode' => '', 'route' => route('cabinet.bonuses', [], false)], 'retention');
                    Log::channel('retention')->info(__FUNCTION__ . ' завершена отправка бланка#' . $blank->id);
                } else {
                    Log::channel('retention')->error('blank not found! #' . 'ret_' . $bonus_number);
                }
            } catch (\Exception $e) {
                Log::channel('retention')->error($e->getMessage());
                Log::channel('retention')->error($e->getFile());
                Log::channel('retention')->error($e->getLine());
                Log::channel('retention')->error($e->getTraceAsString());
            }
        }
    }

}
