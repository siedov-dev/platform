<?php

namespace App\Services\Users;

use App\GamesProviders\Game;
use App\Helpers\Hlog;
use App\MUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserManagerStage
{
    private static ?string $host = null;
    private static ?string $api_token = null;

    private static function getHost(): string
    {
        return "http://127.0.0.1:23099";
    }

    private static function getToken(): string
    {
        if (self::$api_token === null) {
            self::$api_token = config('services.kusersKey');
        }

        return self::$api_token;
    }

    /**
     * Get balance for currency
     * @param MUser $user
     * @param int $currency_id
     * @return array
     */
    public static function getBalance(int $user_id, int $currency_id): array
    {
        $method = '/balance';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    /**
     * Get all user wallets balances
     * @param MUser $user
     * @return array
     */
    public static function getBalances(int $user_id): array
    {
        $method = '/balances';
        $data = [
            'user_id' => $user_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceAdd(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/add';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceRem(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/rem';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function getLockedBalance(int $user_id, int $currency_id): array
    {
        $method = '/balance/locked';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceAddLocked(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/locked/add';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceRemLocked(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/locked/rem';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceSetLocked(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/locked/set';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function getBonusBalance(int $user_id, int $currency_id): array
    {
        $method = '/balance/bonus';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceAddBonus(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/bonus/add';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceRemBonus(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/bonus/rem';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceSetBonus(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/bonus/set';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }


    public static function getReferralBalance(int $user_id, int $currency_id): array
    {
        $method = '/balance/referral';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceAddReferral(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/referral/add';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceRemReferral(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/referral/rem';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function balanceSetReferral(int $user_id, int $currency_id, float $amount): array
    {
        $method = '/balance/referral/set';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    /**
     * Переводит реферальные средства на основной счет
     * @param int $user_id
     * @param int $currency_id
     * @return array
     */
    public static function balanceTransferReferral(int $user_id, int $currency_id): array
    {
        $method = '/balance/referral/transfer';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletSetActive(int $user_id, int $currency_id, bool $active): array
    {
        $method = '/wallet/active';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'active' => $active,
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletGetBonusId(int $user_id, int $currency_id): int
    {
        $method = '/wallet/getBonusId';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data)['bid']?? 0;
    }

    public static function walletSetBonusId(int $user_id, int $currency_id, int $bonus_id)
    {
        $method = '/wallet/setBonusId';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'bonus_id' => $bonus_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletSetTotalBet(int $user_id, int $currency_id, float $amount)
    {
        $method = '/wallet/setTotalBet';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletGetTotalBet(int $user_id, int $currency_id)
    {
        $method = '/wallet/getTotalBet';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletSetTotalWin(int $user_id, int $currency_id, float $amount)
    {
        $method = '/wallet/setTotalBet';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
            'amount' => $amount,
        ];

        return self::jsonPost($method, $data);
    }

    /**
     * get users online
     * @return array|mixed
     */
    public static function users()
    {
        $method = '/users';
        $data = [
        ];

        return self::jsonPost($method, $data);
    }

    public static function walletGetTotalWin(int $user_id, int $currency_id)
    {
        $method = '/wallet/getTotalBet';
        $data = [
            'user_id' => $user_id,
            'currency' => $currency_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function setPPlayToken(string $token, int $user_id)
    {
        $method = '/pplay/token';
        $data = [
            'user_id' => $user_id,
            'token' => $token,
        ];

        return self::jsonPost($method, $data);
    }


    /**
     * @param string $method
     * @param array $data
     * @return array|mixed
     */
    private static function jsonPost(string $method, array $data)
    {
        $time = time();
        return Http::acceptJson()->asJson()
            ->withHeaders(([
                'X-Sign' => self::sign($method, $data, $time),
                'X-Time' => (string)$time,
            ]))
            ->post(self::getHost().$method, $data)->json();
    }

    private static function sign(string $method, array $data, string $time): string
    {
        if (empty($data)) return hash_hmac("sha256", $method.$time, self::getToken());
        return hash_hmac("sha256", $method.json_encode($data).$time, self::getToken());
    }


    public static function startGameSession(MUser $user, Game $game, array $game_data, string $session_id)
    {
        $method = '/startGame';
        $data = [
            'user_id' => $user->getId(),
            'game_id' => $game->getId(),
            'game_provider_id' => $game->getProviderId(),
            'game_group' => $game->getGroupId(),
            'game_data' => $game_data,
            'game_mode' => $user->balance_mode,
            'session_id' => $session_id,
        ];
        return self::jsonPost($method, $data);
    }

    public static function closeGameSession(MUser $user)
    {
        $time = time();
        $method = "/closeGame";
        try {
            $data = [
                'user_id' => $user->getId(),
            ];
            return Http::asForm()->acceptJson()
                ->withHeaders(([
                    'X-Sign' => self::sign($method, $data, $time),
                    'X-Time' => (string)$time,
                ]))
                ->post(self::getHost()."/closeGame", $data)->json();
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public static function getGameSession(MUser $user)
    {
        $time = time();
        $method = "/getGame";
        return Http::acceptJson()
            ->withHeaders(([
                'X-Sign' => self::sign($method, [], $time),
                'X-Time' => (string)$time,
            ]))
            ->get(self::getHost()."/getGame/".$user->getId())
            ->json();
    }

    /**
     * @param MUser $user
     * @return array{cd:int, err:string, bid:int, st: int}
     */
    public static function getActiveBonusState(MUser $user)
    {
        return Http::get(self::getHost()."/user/".$user->getId()."/bonusStatus")->json();
    }

    public static function getCurrencyName(int $curr_id): string
    {
        try {
            $resp = Http::get(self::getHost()."/currency/$curr_id/name")->json();
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return $resp['cn'];
            }
            throw new \Exception($resp['err']?? 'get currency name error');
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return "";
        }
    }

    public static function getGameCurrencyName(int $curr_id): string
    {
        try {
            $resp = Http::get(self::getHost()."/currency/$curr_id/gameCurrency")->json();
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return $resp['cn'];
            }
            throw new \Exception($resp['err']?? 'get game currency name error');
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return "";
        }
    }

    /**
     * get currency internation code (980, 643...)
     * @param int $curr_id
     * @return int
     */
    public static function getCurrencyIntCode(int $curr_id): int
    {
        try {
            $resp = Http::get(self::getHost()."/currency/$curr_id/intCode")->json();
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return $resp['ic'];
            }
            return $resp['err']?? 0;
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return 0;
        }
    }

    public static function getCurrencyHallId(int $curr_id): int
    {
        try {
            $resp = Http::get(self::getHost()."/currency/$curr_id/hallId")->json();
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return $resp['hid'];
            }
            return $resp['err']?? 0;
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return 0;
        }
    }

    public static function getCurrencyMaxAutoPayout(int $curr_id): float
    {
        try {
            $resp = Http::get(self::getHost()."/currency/$curr_id/maxAutopayout")->json();
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return $resp['max'];
            }
            return $resp['err']?? 0;
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return 0;
        }
    }

    /**
     * add event to user event queue
     * @param int $user_id
     * @param string $event
     * @return array
     */
    public static function addEvent(int $user_id, string $event, $payload = null): array
    {
        $method = '/events/add';
        $data = [
            'user_id' => $user_id,
            'event' => $event,
            'payload' => json_encode($payload),
        ];

        try {
            return self::jsonPost($method, $data)?? [];
        } catch (\Throwable $e) {
            Hlog::logException($e);
            return ['err' => $e->getMessage(), 'cd' => 500];
        }
    }

    /**
     * pull users events
     * @param int $user_id
     * @return string[]
     */
    public static function pullEvents(int $user_id): array
    {
        $method = '/events/pull';
        $data = [
            'user_id' => $user_id,
        ];

        return self::jsonPost($method, $data);
    }

    public static function getUserValue(int $user_id, string $key)
    {
        $method = '/getUserValue';
        $data = [
            'user_id' => $user_id,
            'key' => $key,
        ];
        return self::jsonPost($method, $data);
    }

    public static function setUserValue(int $user_id, string $key, string $value)
    {
        $method = '/setUserValue';
        $data = [
            'user_id' => $user_id,
            'key' => $key,
            'value' => $value,
        ];
        return self::jsonPost($method, $data);
    }

    public static function deleteUserValue(int $user_id, string $key)
    {
        $method = '/deleteUserValue';
        $data = [
            'user_id' => $user_id,
            'key' => $key,
        ];
        return self::jsonPost($method, $data);
    }

    public static function deleteAllUserKeyValue(string $key)
    {
        $method = '/deleteAllUserKeyValue';
        $data = [
            'key' => $key,
        ];
        return self::jsonPost($method, $data);
    }

    public static function bonusUpdateStatusPad(int $user_id): bool
    {
        try {
            $method = '/bonus/updateStatusPad';
            $data = [
                'user_id' => $user_id,
            ];
            $resp = self::jsonPost($method, $data);
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return true;
            }
            Log::error('bonusUpdateStatusPad error', $resp);
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public static function reloadDepThresholds(): bool
    {
        try {
            $method = '/reloadDepThresholds';
            $data = [
            ];
            $resp = self::jsonPost($method, $data);
            if (!empty($resp['cd']) && $resp['cd'] === 200) {
                return true;
            }
            throw new \Exception($resp['err']?? 'reloadDepThresholds name error');
        } catch (\Throwable $e) {
            Hlog::logException($e);
        }
        return false;
    }
}
