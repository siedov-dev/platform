<?php

namespace App\Models;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Подписка пользователей на email ссылки.
 * Не используется.
 * @property int $user_id
 * @property MUser $user
 * @property int $status
 * @property string $hash
 */
class MailSubscribe extends Model
{
    use HasFactory;

    const STATUS_SUBSCRIBED = 0;
    const STATUS_UNSUBSCRIBED = 1;

    protected $fillable = ['user_id', 'status'];

    public function user()
    {
        return $this->hasOne(MUser::class, 'id', 'user_id');
    }

    /**
     * check if user is subscribed
     * @param int $user_id user id
     * @return bool
     */
    public static function isSubscribed(int $user_id): bool
    {
        return MailSubscribe::where('user_id', $user_id)->first()?->status === self::STATUS_SUBSCRIBED;
    }

    /**
     * return array of subscribed user ids
     * @return array
     */
    public static function getSubscribedIds(): array
    {
        return MailSubscribe::all()
            ->where('status', self::STATUS_SUBSCRIBED)
            ->pluck('user_id')->toArray();
    }

    /**
     * return array of subscribed user emails
     * @return array
     */
    public static function getSubscribedEmails(): array
    {
        return MUser::selectRaw('email')
            ->join('mail_subscribes', 'mail_subscribes.user_id', 'm_users.id')
            ->where('mail_subscribes.status', self::STATUS_SUBSCRIBED)
            ->pluck('email')
            ->toArray();
    }

    /**
     * adds user id to subscribed
     * @param int $user_id
     * @return void
     */
    public static function subscribeUser(int $user_id): void
    {
        if (self::isSubscribed($user_id)) return;
        /** @var self $subs */
        $subs = self::firstOrCreate(['user_id' => $user_id]);
        $subs->user_id = $user_id;
        $subs->status = self::STATUS_SUBSCRIBED;
        $subs->save();
    }

    /**
     * clears user id from subscribed
     * @param int $user_id
     * @return void
     */
    public static function unsubscribeUser(int $user_id): void
    {
        /** @var self $subs */
        $subs = self::where('user_id', $user_id)->first();
        if ($subs !== null) {
            $subs->status = self::STATUS_UNSUBSCRIBED;
            $subs->save();
        }
    }

    /**
     * gets an array of emails and returns the array of subscribed emails
     * @param array $emails
     * @return array
     */
    public static function filterEmails(array $emails): array
    {
        return MUser::whereIn('email', $emails)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->whereNull('email_verification_token')
//            ->join('mail_subscribes', 'mail_subscribes.user_id', 'm_users.id')
//            ->where('mail_subscribes.status', self::STATUS_SUBSCRIBED)
            ->selectRaw('email')
            ->pluck('email')
            ->toArray();
    }

    public static function isEmailSubscribed(string $email): bool
    {
        return true;
        return MUser::where('email', trim($email))
                ->join('mail_subscribes', 'mail_subscribes.user_id', 'm_users.id')
                ->where('mail_subscribes.status', self::STATUS_SUBSCRIBED)
                ->first() !== null;
    }

    public static function getSubscribeToken(MUser $user): string
    {
        return base64_encode($user->email . ':' . hash_hmac('sha256', $user->email, $user->getId() . '--128subs'));
    }
}
