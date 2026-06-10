<?php

namespace App\Models;

use App\Services\KPay\KPay;

/**
 * Криптокошельки пользователей.
 *
 * @property int $id
 * @property int $user_id
 * @property string $payment_type
 * @property string $currency
 * @property string $fiat_currency
 * @property string $system_id
 * @property string $address
 * @property string $service_user_id
 * @property string $service_outer_id
 */
class UserCryptoWallet // extends Model
{
//    protected $fillable  ['payment_type', 'currency', 'system_id', 'address'];

    public int $id;
    public int $user_id;
    public string $currency;
    public string $system_id;
    public string $address;
    public string $service_user_id;
    public string $service_outer_id;

    public function __construct(array $attributes = [])
    {
        $this->id = $attributes['id'] ?? 0;
        $this->user_id = $attributes['user_id'] ?? 0;
        $this->currency = $attributes['currency'] ?? '';
        $this->system_id = $attributes['system_id'] ?? '';
        $this->address = $attributes['address'] ?? '';
        $this->service_user_id = $attributes['service_user_id'] ?? '';
        $this->service_outer_id = $attributes['service_outer_id'] ?? '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getSystemId(): string
    {
        return $this->system_id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public static function getForUser(int $user_id, string $system_id, string $pay_type, string $currency, string $fiat_currency = null): UserCryptoWallet
    {
        $res = KPay::getInstance()->cryptoWalletGetOrCreate($user_id, $system_id, $pay_type, $currency, $fiat_currency);
        if ($res->isSuccess()) return $res->getResult();
        throw new \Exception($res->getError());
//        $wallet = self::where('user_id', $user_id)
//            ->where('currency', $currency)
//            ->where('system_id', $system_id)
//            ->first();
//
//        if ($wallet === null) {
//            $wallet = new UserCryptoWallet();
//            $wallet->user_id = $user_id;
//            $wallet->currency = $currency;
//            $wallet->system_id = $system_id;
//            $wallet->address = "";
//            $wallet->save();
//        }
//        return $wallet;
    }

    /**
     * @param int $user_id
     * @param string $system_id
     * @param string $token
     * @return UserCryptoWallet[]
     */
    public static function getPayoutWallets(int $user_id, string $system_id, string $token): array
    {
        $resp  = KPay::getInstance()->cryptoGetPayoutWallets($user_id, $system_id, $token);
        if ($resp->isSuccess()) return $resp->getResult();
        return [];
    }

    public function save(): bool
    {
        return KPay::getInstance()
            ->cryptoWalletUpdate($this->id, $this->address, $this->service_user_id, $this->service_outer_id)
            ->isSuccess();
    }

}
