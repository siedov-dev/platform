<?php

namespace App\Models;

use App\Models\Bonuses\KUserBonus;
use App\Models\Bonuses\Segments\BonusSegment;
use App\Models\Bonuses\Segments\BonusSegmentAttachable;
use App\MUser;
use App\Services\KBonus\KBonus;
use App\Services\KSocket\KSocket;
use App\Services\Users\UserManager;
use App\Telegram\Models\TelegramUser;
use App\Telegram\TelegramBot;
use App\UserWallet;
use App\Viber\Models\ViberUser;
use Illuminate\Database\Eloquent\Model;
use App\SMSManager;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class BonusSchedule
 * Планировщик выдачи бонусов
 * с возможностью отправки уведомления.
 *
 * @property int $id
 * @property int $hall_id
 * @property int $type
 * @property string $system_type
 * @property int $status
 * @property float $bonus_sum
 * @property int $bonus_lifetime
 * @property int $free_spins
 * @property int $wager_koef
 * @property int $max_limit
 * @property int $money_to_activate
 * @property string $promocode
 * @property int $all_allowed
 * @property int $send_sms
 * @property int $send_email
 * @property int $send_tg
 * @property int $send_viber
 * @property int $send_push
 * @property string $message_text
 * @property string $user_list
 * @property string $send_time
 * @property string $start_time
 * @property string $end_time
 * @property string $allowed_games
 * @property string $json_params
 * @property string $emails json array with base64 encoded html code of emails for different currencies
 * @property string $tg_message telegramm message
 * @property string $tg_media_type type of file
 * @property string $tg_image base64 image data
 * @property int $blank_id
 * @property string $title
 * @property string $img_url
 * @property Collection|BonusSegment[] $bonusSegments
 * @property ?int $wager_games_segment_id
 */
class BonusSchedule extends Model implements BonusSegmentAttachable
{
    const SMS_NOT_SENT = 1;
    const SMS_SENT = 2;
    const SMS_LOW_BALANCE = 3;
    const SMS_ERROR = 4;

    const STATUS_PENDING = 0;
    const STATUS_DONE = 1;

    public $table = 'bonus_schedule';

    protected $dates = [
        'start_time',
        'end_time',
        'send_time',
    ];

    public static function sendNotifications()
    {
        //send sms
        /** @var BonusSchedule $bs */
        $bs = BonusSchedule::where('send_sms', static::SMS_NOT_SENT)
            //->where('system_type', 'drummatik')
            ->where('send_time', '<', date('Y-m-d H:i:s'))
            ->first();

        $black_list_ids = BonusBlackList::all()->pluck('user_id')->toArray();

        if ($bs) {

            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($bs->blank_id);

            log::info('Sending SMS');

            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['send_sms' => static::SMS_SENT]);

            $user_ids = json_decode($bs->user_list);
            $phones = DB::table('m_users')
                ->whereIn('id', $user_ids)
                ->where('phone', '!=', '')
                ->whereNotIn('id', $black_list_ids)
                ->pluck('phone')->toArray();

            $totalSent = 0;
            foreach ($phones as $phone) {
                $info = SMSManager::sendSMS($phone, $blank? $blank->sms_text : $bs->message_text);
                if ($info->success) $totalSent++;
            }
            if ($totalSent !== count($phones)) {
                DB::table('bonus_schedule')
                    ->where('id', $bs->id)
                    ->update(['send_sms' => static::SMS_ERROR]);
            }
        }

        //send email
        /** @var BonusSchedule $bs */
        $bs = BonusSchedule::where('send_email', static::SMS_NOT_SENT)
            //->where('system_type', 'drummatik')
            ->where('send_time', '<', date('Y-m-d H:i:s'))
            ->first();

        if ($bs) {
            log::channel('mail')->info('Sending email');

            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['send_email' => static::SMS_SENT]);

            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($bs->blank_id);

            $user_ids = json_decode($bs->user_list);
            $emails = [];
            $schedule_letters = json_decode($blank? $blank->emails : $bs->emails, true);

            $users = MUser::whereIn('id', $user_ids)->get();

            foreach ($users as $user) {
                /** @var MUser $user */
                Log::channel('mail')->info($user->email);

                if ($user && $user->email != '' && !in_array($user->id, $black_list_ids)) {
                    $emails[$user->currency][] = $user->email;
                }
            }

            foreach ($emails as $currency => $addresses) {
                if (!empty($schedule_letters[UserWallet::currencyName($currency)])) {
                    $letter = new \App\Email([
                        'to' => $addresses,
                        'subject' => $schedule_letters['subject_'.UserWallet::currencyName($currency)]?? 'Krakencassino Bonus!',
                        'text' => $bs->message_text,
                        'html' => $schedule_letters[UserWallet::currencyName($currency)],
                    ]);
                } else {
                    $letter = new \App\Email([
                        'to' => $addresses,
                        'subject' => 'Krakencassino bonus',
                        'text' => $bs->message_text,
                        'html' => \App\Email::createKrakenMailHtml($bs->message_text),
                    ]);
                }

                try {
                    $letter->send();
                } catch (\Exception $e) {
                    Log::channel('mail')->error($e->getMessage());
                }
            }

        }

        //send telegram
        /** @var BonusSchedule $bs */
        $bs = BonusSchedule::where('send_tg', static::SMS_NOT_SENT)
            //->where('system_type', 'drummatik')
            ->where('send_time', '<', date('Y-m-d H:i:s'))
            ->first();

        if ($bs) {
            log::channel('telegram')->info('Sending bonus notification');
            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($bs->blank_id);

            $bot = new TelegramBot();

            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['send_tg' => static::SMS_SENT]);

            $user_ids = json_decode($bs->user_list);

            $t_users = TelegramUser::whereIn('user_id', $user_ids)->get();

            foreach ($t_users as $t_user) {
                /** @var TelegramUser $t_user */
                Log::channel('telegram')->info($t_user->telegram_id);

                try {
                    if ($blank->tg_media_type === 'image') {
                        $bot::sendChatPhotoData($blank->tg_image, $blank->tg_message, $t_user->telegram_id);
                    } else {
                        $bot::sendChatMessage($blank->tg_message, $t_user->telegram_id);
                    }
                } catch (\Exception $e) {
                    Log::channel('telegram')->error($e->getMessage());
                }
            }

        }

        //send viber
        /** @var BonusSchedule $bs */
        $bs = BonusSchedule::where('send_viber', static::SMS_NOT_SENT)
            //->where('system_type', 'drummatik')
            ->where('send_time', '<', date('Y-m-d H:i:s'))
            ->first();

        if ($bs) {
            log::channel('viber')->info('Sending bonus notification');
            /** @var MessageBlank $blank */
            $blank = MessageBlank::find($bs->blank_id);

            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['send_viber' => static::SMS_SENT]);

            $user_ids = json_decode($bs->user_list);

            $v_users = ViberUser::whereIn('user_id', $user_ids)->get();

            foreach ($v_users as $v_user) {
                /** @var ViberUser $v_user */
                Log::channel('viber')->info($v_user->viber_id);

                try {
                    if ($blank->viber_media_type === 'image') {
                        $v_user->sendImage($blank->viber_image, $blank->viber_message);
                    } else {
                        $v_user->sendText($blank->viber_message);
                    }
                } catch (\Exception $e) {
                    Log::channel('viber')->error($e->getMessage());
                }
            }

        }
    }

    /**
     * create users bonuses from bonus_schedule table
     */
    public static function processBonusSchedule()
    {
        $black_list_ids = BonusBlackList::all()->pluck('user_id')->toArray();

        /** @var BonusSchedule $bs */
        $bs = BonusSchedule::where('status', self::STATUS_PENDING)
            ->where('system_type', 'json')
            ->where('start_time', '<=', date('Y-m-d H:i:s'))
            ->first();

        if ($bs) {
            Log::info('SCHEDULE:' . 'starting bonuses');
            DB::table('bonus_schedule')
                ->where('id', $bs->id)
                ->update(['status' => self::STATUS_DONE]);

            $user_list = json_decode($bs->user_list);
            Log::info('SCHEDULE: userlist ' . $bs->user_list);

            foreach ($user_list as $user_id) {
                try {
                    //skip bonus black list
                    if (in_array($user_id, $black_list_ids)) continue;

                    if ($bs->type > 0) {
                        Log::info('json bonus schedule creating bonus type' . $bs->type . ' for user #' . $user_id);

                        /** @var MUser $user */
                        $user = MUser::find($user_id);

                        $bonus = KBonus::getInstance()->createFromParams(
                            bonus_type: $bs->type,
                            user_id: $user->id,
                            params: $bs->json_params,
                            currency: $user->currency,
                            keep_params: false,
                            tmp: false,
                            pattern_id: 0,
                            creator_type: KUserBonus::CREATOR_SCHEDULE,
                            creator_id: $bs->id,
                            title: $bs->title,
                            img_url: $bs->img_url,
                            marker: 0,
                            allowed_games: $bs->allowed_games,
                            wager_game_segment_id: $bs->wager_games_segment_id,
                        );

                        if ($bonus !== null) {
                            if ($bs->bonusSegments->count() > 0) {
                                KBonus::getInstance()->addBonusToSegmentsIds($bonus->getId(), $bs->bonusSegments->pluck('id')->toArray());
                            }
                            KSocket::getInstance()->sendEvent($bonus->getUserId(), "bonus:changed");
//                            $bonus->attachBonusSegments($bs->bonusSegments->pluck('id')->toArray());
                        }
                    }
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        }
    }

    public function bonusSegments(): BelongsToMany
    {
        return $this->belongsToMany(BonusSegment::class, 'bonus_segment_bonus_schedule');
    }

    public function getWagerGamesSegmentId(): ?int
    {
        return $this->wager_games_segment_id;
    }

    public function setWagerGamesSegmentId(?int $wager_games_segment_id): void
    {
        $this->wager_games_segment_id = $wager_games_segment_id;
    }
}
