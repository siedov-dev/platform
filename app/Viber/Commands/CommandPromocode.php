<?php


namespace App\Viber\Commands;


use App\Http\Controllers\GetBonusController;
use App\Models\Bonuses\UserBonus;
use App\Models\BonusPromocode;
use App\MUser;
use App\Telegram\Models\TelegramPromocodes;
use App\Viber\Models\ViberPromocodes;
use App\Viber\Models\ViberStat;
use App\Viber\Models\ViberUser;
use App\Viber\ViberBot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CommandPromocode implements CommandInterface
{
    private ViberBot $bot;

    public function __construct(ViberBot &$bot)
    {
        $this->bot = $bot;
    }

    public function process($params, ViberUser $v_user, $update)
    {
        /** @var MUser $m_user */
        $msg = 'Отсутствуют доступные промокоды';

        if ($v_user->status == ViberUser::STATUS_NOT_CONFIRMED) {
            return $v_user->sendText('Пользователь не идентифицирован');
        }

        if ($v_user->status != ViberUser::STATUS_NOT_CONFIRMED) {
            /** @var MUser $m_user */
            $m_user = MUser::find($v_user->user_id);
            if (!$m_user) {
                return $v_user->sendText('Пользователь не найден');
            }
        }

        if (!$m_user->isEmailVerified()) {
            return $v_user->sendText('Для получения промокода необходимо верифицировать email');
        }

        //todo what about channel?
//        if (!$this->bot->isUserSubscribed($user->getId(), $this->bot->chat_id)) {
//            return $this->bot->getApi()->sendMessage([
//                'chat_id' => $user->getId(),
//                'text' => 'Для получения промокода необходимо быть подписаным на канал ' . $this->bot->channel_link,
//                'parse_mode' => 'HTML',
//                'reply_markup' => json_encode($v_user->getUserMenu()),
//            ]);
//        }

        DB::beginTransaction();
        try {
            if ($m_user) {
                /** @var ViberPromocodes $promo */
                $promo = ViberPromocodes::where('status', ViberPromocodes::STATUS_SENT)
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
                        $bonus_promo->allowed_users = json_encode([$v_user->user_id]);
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
                        Log::channel('viber')->info("created promocode $promocode for user $v_user->user_id");
                        $msg = "Вы получили промокод с бонусом {$bonus->getName()}!\n**" . $promocode . "** \n" .
                            "Пройдите по ссылке " . action('IndexController@showHavePromocode') .
                            ' либо нажмите кнопку промокода в разделе "Бонусы" личного кабинета';

                        $v_user->sendText($msg);
                        ViberStat::incPromos();
                    }
                }

            } else {
                $msg = 'Не привязан пользователь, укажите свой email на сайте, командой /login';
            }
            DB::commit();

        } catch (\Exception $e) {
            DB::commit();
            Log::channel('telegram')->error($e->getMessage());
        }

        return $v_user->sendText($msg);
    }
}
