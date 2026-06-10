<?php

namespace App\Services\NatsService;

use App\Helpers\Hlog;
use App\Wallet;

/**
 * События пользователя, отправляемые в NATS
 */
final class NatsUserEvents
{

    /**
     * Регистрация
     * @param int $user_id
     * @param int $currency_id
     * @param int|null $ref_id
     * @param int|null $click_id
     * @param string|null $fingerprint
     * @return void
     */
    public static function registered(int $user_id, int $currency_id, ?int $ref_id = 0, ?int $click_id = 0, ?string $fingerprint = null): void
    {
        try {
            NatsService::getInstance()->getClient()->publish('userevents.registered', [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
                'currency' => Wallet::currencyName($currency_id),
                'referral_id' => $ref_id ?? 0,
                'click_id' => $click_id ?? 0,
                'fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    /**
     * Первый депозит
     * @param int $user_id
     * @param float $amount
     * @param int $currency_id
     * @param int|null $ref_id
     * @param int|null $click_id
     * @param string|null $fingerprint
     * @return void
     */
    public static function firstDep(int $user_id, float $amount, int $currency_id, ?int $ref_id = 0, ?int $click_id = 0, ?string $fingerprint = null): void
    {
        try {
            NatsService::getInstance()->getClient()->publish('userevents.first-dep', [
                'user_id' => $user_id,
                'amount' => $amount,
                'currency_id' => $currency_id,
                'currency' => Wallet::currencyName($currency_id),
                'referral_id' => $ref_id ?? 0,
                'click_id' => $click_id ?? 0,
                'fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    /**
     * Трата/выигрыш денег
     * @param int $user_id
     * @param float $amount (отрицательное - выигрыш)
     * @param int $currency_id
     * @param int|null $ref_id
     * @param int|null $click_id
     * @param string|null $fingerprint
     * @return void
     */
    public static function moneySpent(int $user_id, float $amount, int $currency_id, ?int $ref_id = 0, ?int $click_id = 0, ?string $fingerprint = null): void
    {
        try {
            NatsService::getInstance()->getClient()->publish('userevents.money-spent', [
                'user_id' => $user_id,
                'amount' => $amount,
                'currency_id' => $currency_id,
                'currency' => Wallet::currencyName($currency_id),
                'referral_id' => $ref_id ?? 0,
                'click_id' => $click_id ?? 0,
                'fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }

    /**
     * Завершено достижение
     * @param int $user_id
     * @param int $ach_id
     * @param int $currency_id
     * @param int|null $ref_id
     * @param int|null $click_id
     * @param string|null $fingerprint
     * @return void
     */
    public static function achievementCompleted(int $user_id, int $ach_id, int $currency_id, ?int $ref_id = 0, ?int $click_id = 0, ?string $fingerprint = null): void
    {
        try {
            NatsService::getInstance()->getClient()->publish('userevents.achievement-completed', [
                'user_id' => $user_id,
                'ach_id' => $ach_id,
                'currency_id' => $currency_id,
                'currency' => Wallet::currencyName($currency_id),
                'referral_id' => $ref_id ?? 0,
                'click_id' => $click_id ?? 0,
                'fingerprint' => $fingerprint,
            ]);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
    }
}
