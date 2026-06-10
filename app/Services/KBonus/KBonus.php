<?php

namespace App\Services\KBonus;

use App\Helpers\Hlog;
use App\Models\Bonuses\BonusFactory;
use App\Models\Bonuses\KUserBonus;
use App\Models\Bonuses\Params\BonusMultiDaysParam;
use App\Models\BonusMenuParam;
use App\Models\BonusPatterns;
use App\MUser;
use App\Wallet;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class KBonus
{
    private static ?KBonus $_instance = null;
    private string $token = "";
    private string $host = "http://127.0.0.1:23102";

    private function __construct()
    {
        $this->token = config('services.ServicesToken');
        $this->host = config('services.kBonusHost');
    }

    public static function getInstance(): KBonus
    {
        if (self::$_instance === null) {
            self::$_instance = new KBonus();
        }
        return self::$_instance;
    }
//
    public function serviceEnabled(): bool
    {
        try {
            return $this->ping() === "ok";
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    public function ping(): string
    {
        try {
            $endpoint = "http://127.0.0.1:23102";
            return Http::withToken($this->token)->get($endpoint)->body();
        } catch (\Exception $e) {
            Hlog::logException($e);
            return false;
        }
    }

    public function getBonus(int $bonus_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("getBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getBonusBalanceInCents(int $bonus_id): int
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("getBonusBalanceInCents", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus', $result);
                    return 0;
                }
                return $result['balance'] ?? 0;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function getRegPattern(string $currency, string $type, int $num): ?BonusPatterns
    {
        try {
            $payload = [
                'currency' => $currency,
                'type' => $type,
                'num' => $num,
            ];
            $resp = $this->restRequest("getRegPattern", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return null;
                }
                $attributes = $this->convertAttributes($result['attributes']);
                return new BonusPatterns($attributes);
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getPatternByName(string $name): ?BonusPatterns
    {
        try {
            $payload = [
                'name' => $name,
            ];
            $resp = $this->restRequest("getPatternByName", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return null;
                }
                $attributes = $this->convertAttributes($result['attributes']);
                return new BonusPatterns($attributes);
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getPatternById(int $id): ?BonusPatterns
    {
        try {
            $payload = [
                'id' => $id,
            ];
            $resp = $this->restRequest("getPatternById", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 404) {
                    return null;
                }
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return null;
                }
                $attributes = $this->convertAttributes($result['attributes']);
                return new BonusPatterns($attributes);
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getRegPatternIds(string $currency): array
    {
        try {
            $payload = [
                'currency' => $currency,
            ];
            $resp = $this->restRequest("getRegPatternIds", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return [];
                }
                return $result['ids'] ?? [];
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getRegFsPatternIds(string $currency): array
    {
        try {
            $payload = [
                'currency' => $currency,
            ];
            $resp = $this->restRequest("getRegFsPatternIds", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return [];
                }
                return $result['ids'] ?? [];
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getRegNonDepPatternIds(string $currency): array
    {
        try {
            $payload = [
                'currency' => $currency,
            ];
            $resp = $this->restRequest("getRegNonDepPatternIds", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus pattern', $result);
                    return [];
                }
                return $result['ids'] ?? [];
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }


    public function getBonusAvailable(int $bonus_id, int $currency_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getBonusAvailable", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function createFromBonusAvailable(int $bonus_id, int $user_id, int $currency_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("createBonusFromAvailable", $payload);
            $bonus = $this->bonusFromResponse($resp);
            if ($bonus?->getId() > 0) {
                $bonus->afterCreated();
            }
            return $bonus;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function cancelFromBonusAvailable(int $bonus_id, int $user_id): bool
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'user_id' => $user_id,
            ];
            $resp = $this->restRequest("cancelBonusFromAvailable", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus', $result);
                    return false;
                }
                return $result['data'] ?? false;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function getFirstActiveBonus(int $user_id, int $currency_id = 0): ?KUserBonus
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getFirstActiveBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getFirstNotActiveBonus(int $user_id, int $currency_id = 0): ?KUserBonus
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getFirstNotActiveBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getFirstWaitingDepositBonus(int $user_id, int $currency_id = 0): ?KUserBonus
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getFirstWaitingDepositBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function hasCancelableOnPayout(int $user_id): bool
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->restRequest("hasCancelableOnPayout", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get first not active bonus', $result);
                    return false;
                }
                return $result['data'] ?? false;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    /**
     * @param int $user_id
     * @param int $currency_id
     * @return KUserBonus[]
     */
    public function getUserBonuses(int $user_id, int $currency_id): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getUserBonuses", $payload);;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get user bonuses', $result);
                    return [];
                }
                $bonuses = [];
                foreach ($result['bonuses'] ?? [] as $bonus) {
                    $attributes = $this->convertAttributes($bonus['attributes']);
                    $meta = $bonus['meta'] ?? [];
                    $bonuses[] = BonusFactory::createFromType($attributes, $meta);
                }
                return $bonuses;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getAvailableBonusesCount(int $user_id, int $currency_id = 0): int
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getAvailableBonusCount", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get user bonuses count', $result);
                    return 0;
                }
                return $result['count'] ?? 0;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function getUserCompletedBonuses(int $user_id, int $currency_id = 0): array
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getUserCompletedBonuses", $payload);;;
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get user completed bonuses', $result);
                    return [];
                }
                $bonuses = [];
                foreach (($result['bonuses']?? []) as $bonus) {
                    $attributes = $this->convertAttributes($bonus['attributes']);
                    $meta = $bonus['meta'] ?? [];
                    $bonuses[] = BonusFactory::createFromType($attributes, $meta);
                }
                return $bonuses;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function actualizeUserBonuses(int $user_id): void
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->restRequest("actualizeUserBonuses", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error actualizeUserBonuses', $result);
                }
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function createFromParams(
        int     $bonus_type,
        int     $user_id,
        string  $params,
        ?int    $currency = null,
        ?bool   $keep_params = false,
        ?bool   $tmp = false,
        ?int    $pattern_id = 0,
        ?int    $creator_type = 0,
        ?int    $creator_id = 0,
        ?string $title = '',
        ?string $img_url = '',
        ?int    $marker = 0,
        ?string $allowed_games = "",
        ?int $wager_game_segment_id = null,
    ): ?KUserBonus
    {
        try {
            if ($currency === null) {
                if ($user_id > 0) {
                    $currency = MUser::find($user_id)?->getCurrency() ?? Wallet::CURRENCY_EUR;
                } else {
                    $currency = Wallet::CURRENCY_EUR;
                }
            }
            $payload = [
                'bonus_type' => $bonus_type,
                'user_id' => $user_id,
                'params' => $params,
                'currency' => $currency,
                'keep_params' => $keep_params,
                'tmp' => $tmp,
                'pattern_id' => $pattern_id,
                'creator_type' => $creator_type,
                'creator_id' => $creator_id,
                'title' => $title,
                'img_url' => $img_url,
                'marker' => $marker,
                'allowed_games' => $allowed_games,
                'wager_game_segment_id' => $wager_game_segment_id,
            ];
            $resp = $this->restRequest("createFromParams", $payload);
            $bonus = $this->bonusFromResponse($resp);
            if ($bonus?->getId() > 0) {
                $bonus->afterCreated();
            }
            return $bonus;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function createFromPatternParams(
        int     $user_id,
        int     $pattern_id,
        ?int    $currency = null,
        ?int    $creator_type = 0,
        ?int    $creator_id = 0,
        ?string $title = '',
        ?string $img_url = '',
        ?int    $marker = 0,
        ?string $allowed_games = "",
        ?bool   $tmp = false,
    ): ?KUserBonus
    {
        try {
            if ($currency === null) {
                if ($user_id > 0) {
                    $currency = MUser::find($user_id)?->getCurrency() ?? Wallet::CURRENCY_EUR;
                } else {
                    $currency = Wallet::CURRENCY_EUR;
                }
            }
            $payload = [
                'user_id' => $user_id,
                'pattern_id' => $pattern_id,
                'currency' => $currency,
                'creator_type' => $creator_type,
                'creator_id' => $creator_id,
                'title' => $title,
                'img_url' => $img_url,
                'marker' => $marker,
                'allowed_games' => $allowed_games,
                'tmp' => $tmp,
            ];
            $resp = $this->restRequest("createFromPatternParams", $payload);
            $bonus = $this->bonusFromResponse($resp);
            if ($bonus?->getId() > 0) {
                $bonus->afterCreated();
            }
            return $bonus;
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function activateBonus(int $bonus_id, float $value = 0): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'value' => $value,
            ];
            $resp = $this->restRequest("activateBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function cancelBonus(int $bonus_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("cancelBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function saveBonus(array $attributes): ?KUserBonus
    {
        try {
            $payload = [
                'attributes' => $this->convertUserBonusAttributesToSqlNull($attributes),
            ];
            Log::info('saveBonus', $payload);
            $resp = $this->restRequest("saveBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function dropBonus(int $bonus_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("dropBonus", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function pauseOrResumeBonus(int $bonus_id, bool $pause): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'pause' => $pause,
            ];
            $resp = $this->restRequest("bonusPauseOrResume", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getBonusByPatternId(int $user_id, int $pattern_id): ?KUserBonus
    {
        try {
            $payload = [
                'user_id' => $user_id,
                'pattern_id' => $pattern_id,
            ];
            $resp = $this->restRequest("getBonusByPatternID", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function confirmPayment(int $bonus_id, float $amount, int $currency_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'amount' => $amount,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("confirmPayment", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function onDeposit(int $bonus_id, float $amount, int $currency_id): ?KUserBonus
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'amount' => $amount,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("onDeposit", $payload);
            return $this->bonusFromResponse($resp);
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function transferBonusMoney(int $bonus_id): array
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("transferBonusMoney", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error transferBonusMoney bonus', $result);
                    return $result;
                }
                $result['attributes'] = $this->convertAttributes($result['attributes']);
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    /**
     * @param int $bonus_type
     * @param int $currency_id
     * @return BonusMenuParam[]
     */
    public function getBonusParams(int $bonus_type, int $currency_id): array
    {
        try {
            $payload = [
                'bonus_type' => $bonus_type,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("getBonusParamsByTypeAndCurrency", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus menu', $result);
                    return [];
                }
                $params = [];
                foreach ($result['params'] as $p) {
                    $attr = $this->convertAttributes($p);
                    $params[] = new BonusMenuParam($attr);
                }
                return $params;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function paramsFindInRange(int $bonus_type, int $currency_id, float $value): ?BonusMenuParam
    {
        try {
            $payload = [
                'bonus_type' => $bonus_type,
                'currency_id' => $currency_id,
                'value' => $value,
            ];
            $resp = $this->restRequest("paramsFindInRange", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus menu', $result);
                    return null;
                }
                $attr = $this->convertAttributes($result['param']);
                return new BonusMenuParam($attr);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function paramGetByKeyValue(int $bonus_type, int $currency_id, float $value): ?BonusMenuParam
    {
        try {
            $payload = [
                'bonus_type' => $bonus_type,
                'currency_id' => $currency_id,
                'value' => $value,
            ];
            $resp = $this->restRequest("paramsGetByKeyValue", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus menu', $result);
                    return null;
                }
                $attr = $this->convertAttributes($result['param']);
                return new BonusMenuParam($attr);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function paramLowestMinValue(int $bonus_type, int $currency_id): float
    {
        try {
            $payload = [
                'bonus_type' => $bonus_type,
                'currency_id' => $currency_id,
            ];
            $resp = $this->restRequest("paramsLowestMinValue", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus menu', $result);
                    return 0;
                }
                return $result['value'] ?? 0;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return 0;
    }

    public function addBonusToSegmentsIds(int $bonus_id, array $segment_ids): bool
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'segment_ids' => $segment_ids,
            ];
            $resp = $this->restRequest("addToSegmentIds", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 0) {
                    Log::error('Error get bonus', $result);
                    return false;
                }
                return $result['data'] ?? false;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function addBonusToSegmentByName(int $bonus_id, string $segmentName): bool
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'segment_name' => $segmentName,
            ];
            $resp = $this->restRequest("addToSegmentName", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 0) {
                    Log::error('Error get bonus', $result);
                    return false;
                }
                return $result['data'] ?? false;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function cancelBonusesOnPayout(int $user_id): void
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->restRequest("cancelBonusesOnPayout", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error CancelBonusesOnPayout', $result);
                    return;
                }
                return;
            }

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
    }

    public function multidaysParamsForDay(int $day): ?BonusMultiDaysParam
    {
        try {
            $payload = [
                'day' => $day,
            ];
            $resp = $this->restRequest("multidaysGetParamsForDay", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 404) {
                    return null;
                }
                if ($result['cd'] !== 200) {
                    Log::error('Error get bonus menu', $result);
                    return null;
                }
                $attr = $this->convertAttributes($result['param']);
                return New BonusMultiDaysParam($attr);
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function bonusStatusPad(int $bonus_id): array
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("bonusGetStatusPad", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error bonusGetStatusPad', $result);
                }
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function reloadBonusAvailable(int $bonus_id): array
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
            ];
            $resp = $this->restRequest("reloadBonusAvailable", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error reloadBonusAvailable', $result);
                }
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function reloadBonusIfCached(int $bonus_id, int $user_id): array
    {
        try {
            $payload = [
                'bonus_id' => $bonus_id,
                'user_id' => $user_id,
            ];
            $resp = $this->restRequest("reloadBonusIfCached", $payload);
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] !== 200) {
                    Log::error('Error reloadBonusIfCached', $result);
                }
                return $result;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function addUserToBonusBlackList(int $user_id): bool
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->request("addToBonusBlackList", $payload);
            return $resp->status() === 200;
        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    public function removeUserFromBonusBlackList(int $user_id): bool
    {
        try {
            $payload = [
                'user_id' => $user_id,
            ];
            $resp = $this->request("removeFromBonusBlackList", $payload);
            return $resp->status() === 200;
        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    public function reloadService(): string
    {
        try {
            $payload = [];
            $resp = $this->request("reloadService", $payload);
            return $resp->status()." ".$resp->body();
        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    private function convertAttributes(array &$in_attr): array
    {
        foreach ($in_attr as $k => $attr) {
            if (is_array($attr)) {
                if ($attr['Valid'] === true) {
                    foreach ($attr as $ak => $attr_v) {
                        if ($ak === 'Valid') continue;
                        $in_attr[$k] = $attr_v;
                    }
                } else {
                    $in_attr[$k] = null;
                }
            } else {
                $in_attr[$k] = $attr;
            }
        }
        return $in_attr;
    }

    /**
     * Конвертирует плоские атрибуты user_bonus в структуру, совместимую с Go sql.Null*.
     * Для sql.NullString -> {"String": "...", "Valid": bool}
     * Для sql.NullInt64  -> {"Int64": 123,  "Valid": bool}
     * Для sql.NullFloat64-> {"Float64": 1.2,"Valid": bool}
     * Для sql.NullTime   -> {"Time": "RFC3339", "Valid": bool}
     */
    private function convertUserBonusAttributesToSqlNull(array &$attrs): array
    {
        $nullStringFields = [
            'bonus_prizes','given_prizes','enable_after',
            'allowed_games','json_params','game_provider',
            'game_id','title','img_url',
        ];
        $nullInt64Fields = ['pattern_id','creator_id','wager_games_segment_id'];
        $nullFloat64Fields = ['max_win'];
        $nullTimeFields = ['active_to','completed_at','created_at','updated_at'];

        $wrapNullString = static function ($v): array {
            $valid = $v !== null && $v !== '';
            return ['String' => $valid ? (string)$v : '', 'Valid' => $valid];
        };
        $wrapNullInt64 = static function ($v): array {
            $valid = $v !== null && $v !== '';
            return ['Int64' => $valid ? (int)$v : 0, 'Valid' => $valid];
        };
        $wrapNullFloat64 = static function ($v): array {
            $valid = $v !== null && $v !== '';
            return ['Float64' => $valid ? (float)$v : 0.0, 'Valid' => $valid];
        };
        $wrapNullTime = static function ($v): array {
            if ($v === null || $v === '' || $v === '0000-00-00 00:00:00') {
                // НЕ возвращаем ключ Time, чтобы Go не пытался парсить пустую строку
                return ['Valid' => false];
            }
            $s = (string)$v;
            // Попробуем нормализовать к RFC3339, если это не уже RFC3339
            try {
                $dt = new DateTime($s);
                $s = $dt->format(DateTime::RFC3339); // 2006-01-02T15:04:05Z07:00
            } catch (Throwable $e) {
                // если не парсится PHP, оставим как есть — в вашем кейсе уже приходит RFC3339Nano
            }
            return ['Time' => $s, 'Valid' => true];
        };

        foreach ($nullStringFields as $f) {
            if (array_key_exists($f, $attrs)) {
                $attrs[$f] = $wrapNullString($attrs[$f]);
            }
        }
        foreach ($nullInt64Fields as $f) {
            if (array_key_exists($f, $attrs)) {
                $attrs[$f] = $wrapNullInt64($attrs[$f]);
            }
        }
        foreach ($nullFloat64Fields as $f) {
            if (array_key_exists($f, $attrs)) {
                $attrs[$f] = $wrapNullFloat64($attrs[$f]);
            }
        }
        foreach ($nullTimeFields as $f) {
            if (array_key_exists($f, $attrs)) {
                $attrs[$f] = $wrapNullTime($attrs[$f]);
            }
        }

        return $attrs;
    }

    private function restRequest(string $method, array $payload)
    {
        $endpoint = $this->host."/api/$method";
        $resp = Http::withToken($this->token)->acceptJson()->post($endpoint, $payload)->body();
//        Log::info($resp);
        return $resp;
    }

    private function request(string $method, array $payload)
    {
        $endpoint = $this->host."/api/$method";
        return Http::withToken($this->token)->post($endpoint, $payload);
    }

    private function bonusFromResponse(string $resp): ?KUserBonus
    {
        try {
            $result = json_decode($resp, true);
            if (is_array($result)) {
                if ($result['cd'] === 404) return null;
                if ($result['cd'] !== 200) {
                    Log::error('Error bonus', $result);
                    return null;
                }
                $attributes = $this->convertAttributes($result['attributes']);
                $meta = $result['meta'] ?? [];
                $bonus = BonusFactory::createFromType($attributes, $meta);
                return $bonus;
            }
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }
}
