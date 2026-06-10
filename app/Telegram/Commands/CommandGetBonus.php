<?php


namespace App\Telegram\Commands;

use App\Models\BonusPatterns;
use App\Models\BonusPromocode;
use App\MUser;
use App\Telegram\Models\TelegramStat;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Objects\Message;
use Telegram\Bot\Objects\User;

class CommandGetBonus implements CommandInterface
{
    private TelegramBot $bot;

    public function __construct(TelegramBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process(string $params, User $user, TelegramUser $t_user, Message $message): ?Message
    {
        $msg = __('telegram.got_subs_bonus');

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

                if (!$t_user->gotSubsBonus()) {
                        DB::commit();

                        $promocode = Str::random(10);
                        /** @var BonusPatterns $pattern */
                        $pattern = BonusPatterns::where('name', 'bonus.tg_menu_bonus')->first();

                        //create bonus promocode
                        $bonus_promo = new BonusPromocode();
                        $bonus_promo->allowed_users = json_encode([$t_user->user_id]);
                        $bonus_promo->allowed_games = $pattern->allowed_games;
                        $bonus_promo->activation_type = BonusPromocode::ACTIVATION_TYPE_ALLOWED_USERS;
                        $bonus_promo->bonus_type = $pattern->bonus_type;
                        $bonus_promo->bonus_params = $pattern->bonus_params;
                        $bonus_promo->active_to = now()->addDays(3)->format('Y-m-d H:i:s');
                        $bonus_promo->promocode = $promocode;
                        $bonus_promo->quantity = 1;
                        $bonus_promo->hall_id = 0;
                        $bonus_promo->img_url = $pattern->img_url;
                        $bonus_promo->title = $pattern->title;
                        $bonus_promo->save();
                        Log::channel('telegram')->info("created promocode $promocode for user $t_user->user_id");
                        $msg = __('telegram.got_promo', [
                            'bonus_name' => $bonus_promo->title?? '',
                            'promocode' => $promocode,
                            'link' => action('IndexController@showHavePromocode'),
                        ]);
                        $t_user->setGotSubBonus();
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
