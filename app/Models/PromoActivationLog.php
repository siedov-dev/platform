<?php

namespace App\Models;

use App\MUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Логи активаций промокодов.
 *
 * @property int $id
 * @property int $user_id
 * @property int $promo_id
 * @property boolean $success
 * @property boolean $registration
 * @property string $promocode
 */
class PromoActivationLog extends Model
{
    use HasFactory;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    public function getPromoId(): int
    {
        return $this->promo_id;
    }

    public function setPromoId(int $promo_id): void
    {
        $this->promo_id = $promo_id;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): void
    {
        $this->success = $success;
    }

    public function getPromocode(): string
    {
        return $this->promocode;
    }

    public function setPromocode(string $promocode): void
    {
        $this->promocode = $promocode;
    }

    public function isRegistration(): bool
    {
        return $this->registration;
    }

    public function setRegistration(bool $registration): void
    {
        $this->registration = $registration;
    }

    public static function userActivatedCount(MUser $user): int
    {
        return self::where('user_id', $user->getId())
            ->where('success', true)
            ->count();
    }
}
