<?php

namespace App\Telegram\Commands;

use App\Models\Bonuses\UserBonus;
use App\Models\BonusPromocode;
use App\MUser;
use App\Telegram\Models\TelegramPromocodes;
use App\Telegram\Models\TelegramStat;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandPromocode implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        /** @var MUser $m_user */
        $msg = __('telegram.no_promocodes');

        if ($t_user->status == TelegramUser::STATUS_NOT_CONFIRMED) {
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.user_not_identified'),
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);

        }

        if ($t_user->status != TelegramUser::STATUS_NOT_CONFIRMED) {
            /** @var MUser $m_user */
            $m_user = MUser::find($t_user->user_id);
            if (!$m_user) {
                return $this->bot->getApi()->sendApiMessage([
                    'chat_id' => $user->getId(),
                    'text' => __('telegram.user_not_found'),
                    'reply_markup' => json_encode($t_user->getUserMenu()),
                ]);
            }
        }

        if (!$m_user->isEmailVerified()) {
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.verified_email_required'),
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);
        }

        if (!$this->bot->isUserSubscribed($user->getId(), $this->bot->chat_id)) {
            return $this->bot->getApi()->sendApiMessage([
                'chat_id' => $user->getId(),
                'text' => __('telegram.channel_subs_required') . $this->bot->channel_link,
                'parse_mode' => 'HTML',
                'reply_markup' => json_encode($t_user->getUserMenu()),
            ]);
        }

        DB::beginTransaction();
        try {
            if ($m_user) {
                /** @var TelegramPromocodes $promo */
                $promo = TelegramPromocodes::where('status', TelegramPromocodes::STATUS_SENT)
                    ->where('active_to', '>', now())->first();
                if ($promo !== null) {

                    $user_done = $promo->user_received == null ? [] : json_decode($promo->user_received, true);
                    if ($user_done === null || !is_array($user_done)) $user_done = [];

                    if (!in_array($m_user->id, $user_done)) {

                        $user_done[] = $m_user->id;
                        $promo->user_received = json_encode($user_done);
                        $promo->save();
                        DB::commit();

                        $bonus = new UserBonus();
                        $bonus->type = $promo->bonus_type;
                        $bonus = UserBonus::convertByType($bonus);

                        $promocode = Str::random(10);
                        //create bonus promocode
                        $bonus_promo = new BonusPromocode();
                        $bonus_promo->allowed_users = json_encode([$t_user->user_id]);
//                $bonus_promo->allowed_games = json_encode($games);
                        $bonus_promo->allowed_games = json_encode(['vegas' => ['space_wars_html']]);
                        $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                        $bonus_promo->bonus_type = $promo->bonus_type;
                        $bonus_promo->bonus_params = $promo->bonus_params;
                        $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
                        $bonus_promo->promocode = $promocode;
                        $bonus_promo->quantity = 1;
                        $bonus_promo->hall_id = 0;
                        $bonus_promo->save();
                        Log::channel('telegram')->info("created promocode $promocode for user $t_user->user_id");
                        $msg = __('telegram.got_promo', [
                            'bonus_name' => $bonus->getName(),
                            'promocode' => $promocode,
                            'link' => action('IndexController@showHavePromocode'),
                        ]);
//                        $msg = "Вы получили промокод с бонусом {$bonus->getName()}!\n<b>" . $promocode . "</b> \n" .
//                            "Пройдите по " . '<a href="' . action('IndexController@showHavePromocode') . '">ссылке</a>' .
//                            ' либо нажмите кнопку промокода в разделе "Бонусы" личного кабинета';
                        TelegramStat::incPromos();

                        return $this->bot->getApi()->sendApiMessage([
                            'chat_id' => $user->getId(),
                            'text' => $msg,
                            'parse_mode' => 'HTML',
                            'reply_markup' => json_encode($t_user->getUserMenu()),
                        ]);
                    }
                }

            } else {
                $msg = __('telegram.user_not_connected');
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::commit();
            Log::channel('telegram')->error($e->getMessage());
            Log::channel('telegram')->error($e->getLine());
            Log::channel('telegram')->error($e->getFile());
            Log::channel('telegram')->error($e->getTraceAsString());
        }

        return $this->bot->getApi()->sendApiMessage([
            'chat_id' => $user->getId(),
            'text' => $msg,
            'reply_markup' => json_encode($t_user->getUserMenu()),
        ]);
    }
}
