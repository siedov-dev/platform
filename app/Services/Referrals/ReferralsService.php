<?php

namespace App\Services\Referrals;

use App\Helpers\Hlog;
use App\Services\NatsService\NatsService;
use App\Wallet;
use Illuminate\Support\Facades\Log;

class ReferralsService
{
    private static ?ReferralsService $_instance = null;

    public static function getInstance(): ReferralsService
    {
        if (self::$_instance === null) {
            self::$_instance = new ReferralsService();
        }
        return self::$_instance;
    }

    /**
     * Возвращает доступен ли сервис рефералов
     * @return bool
     */
    public function serviceEnabled(): bool
    {
        try {
            // если отключен в конфигурации
            if (!config('services.referralsEnabled', false)) return false;
            return NatsService::getInstance()->getClient()->dispatch("referrals.ping", null, 0.05)?->body === "pong";
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    /**
     * Обрабатывает переход по реферальной ссылке
     * @param string $ref_code
     * @param string|null $referer
     * @param string|null $fingerprint
     * @return void
     */
    public function refClick(string $ref_code, ?string $referer, ?string $fingerprint = null): void
    {
        try {
            $payload = [
                'ref_code' => $ref_code,
                'referer' => $referer?? '',
                'fingerprint' => $fingerprint,
            ];
            NatsService::getInstance()->getClient()->publish("referrals.click", $payload);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function transferRefMoney(int $user_id, int $currency_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency' => Wallet::currencyName($currency_id),
                'currency_id' => $currency_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.transferRefMoney", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [
            'cd' => 500,
            'err' => 'error',
        ];
    }

    public function transferAccumulatedMoney(int $user_id, int $currency_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency' => Wallet::currencyName($currency_id),
                'currency_id' => $currency_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.transferAccumMoney", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [
            'cd' => 500,
            'err' => 'error',
        ];
    }

    /**
     * Возвращает реферальный код пользователя
     * @param int $user_id
     * @return array{ref_code: string, ref_id: int}
     */
    public function getRefCode(int $user_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getRefCode", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return [
                        'ref_code' => $result['ref_code'],
                        'ref_id' => $result['ref_id'],
                    ];
                } else {
                    Log::error("getRefCode error user_id#".$user_id." cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getRefCode error user_id#".$user_id);
        return [
            "ref_code" => "",
            "ref_id" => 0,
        ];
    }

    /**
     * @param int $user_id
     * @param int $currency_id
     * @return array{ggr_prc: int, fd_sum: float}
     */
    public function getRefConfig(int $user_id, int $currency_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency' => Wallet::currencyName($currency_id),
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getRefConfig", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return [
                        'ggr_prc' => $result['ggr_prc'],
                        'fd_sum' => $result['fd_sum'],
                    ];
                } else {
                    Log::error("getRefConfig error user_id#".$user_id." cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getRefConfig error user_id#".$user_id);
        return [
            "ggr_prc" => 0,
            "fd_sum" => 0,
        ];
    }

    /**
     * Возвращает награды за достижения в валюте
     * @param int $currency_id
     * @return array
     */
    public function getAchievementsRewards(int $currency_id): array
    {
        try {
            $payload = [
                'currency' => Wallet::currencyName($currency_id),
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getAchievementRewards", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return $result['rewards'];
                } else {
                    Log::error("getAchievementsRewards cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getRefConfig error currency_id#".$currency_id);
        return [
        ];
    }

    /**
     * Возвращает пользователей реферала с пагинацией
     * @param int $ref_id
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function getReferralUsers(int $ref_id, int $page, int $per_page = 10): array
    {
        try {
            $payload = [
                'ref_id' => $ref_id,
                'page' => $page - 1,
                'per_page' => $per_page,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getReferralUsers", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return $result;
                } else {
                    Log::error("getAchievementsRewards cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getRefConfig error ref_id#".$ref_id);
        return [
        ];
    }

    public function getReferralEvents(int $ref_id, int $page, int $per_page = 10): array
    {
        try {
            $payload = [
                'ref_id' => $ref_id,
                'page' => $page - 1,
                'per_page' => $per_page,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getReferralEvents", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return $result;
                } else {
                    Log::error("getReferralEvents cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getReferralEvents error ref_id#".$ref_id);
        return [
        ];
    }

    /**
     * Возвращает к-во пользователей реферала у которых есть депозиты
     * @param int $ref_id
     * @return int
     */
    public function getReferralUsersCountWithDeps(int $ref_id): int
    {
        try {
            $payload = [
                'ref_id' => $ref_id,
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.getReferralUsersCountWithDeps", $payload)?->body;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 200) {
                    return $result['count'];
                } else {
                    Log::error("getAchievementsRewards cd#".$result['cd']." err#".$result['err']);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        Log::error("getRefConfig error ref_id#".$ref_id);
        return 0;
    }

    /**
     * Переводит все накопительные средства пользователей на реферальные счета
     * @return void
     */
    public function transferAllAccumulatedMoney(): void
    {
        try {
            $payload = [
            ];
            NatsService::getInstance()->getClient()->publish("referrals.transferAllRefAccumMoney", $payload);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function reload(): string
    {
        try {
            $payload = [
            ];
            $resp = NatsService::getInstance()->getClient()->dispatch("referrals.reload", $payload)?->body;
            return $resp;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return "unknown error";
    }
}
