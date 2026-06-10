<?php

namespace App\Services\KPay;

use App\Helpers\Hlog;
use App\Models\PaymentSystems;
use App\Models\UserCryptoWallet;
use App\Models\UserMoneyStat;
use App\ResultOrErr;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class KPay
{
    private static ?KPay $_instance = null;
    private string $token = "";
    private string $host = "http://127.0.0.1:23106";

    private function __construct()
    {
        $this->token = config('services.ServicesToken');
        $this->host = config('services.kPayHost');
    }

    public static function getInstance(): KPay
    {
        if (self::$_instance === null) {
            self::$_instance = new KPay();
        }
        return self::$_instance;
    }

    public function ping()
    {
        return Http::withToken($this->token)->acceptJson()->get($this->host, [])->body();
    }

    /**
     * @param int $user_id
     * @param int $currency_id
     * @param int $pay_type
     * @param bool $testMethods
     * @param int $payGroup
     * @return Collection|PaymentSystems[]
     */
    public function getPayMethods(int $user_id, int $currency_id, int $pay_type, bool $testMethods, int $payGroup, ?UserStatsData $userStats = null): Collection
    {
        try {
            if ($userStats === null && $user_id === 0) $userStats = new UserStatsData();
            $payload = [
                "user_id" => $user_id,
                "currency_id" => $currency_id,
                'pay_type' => $pay_type,
                'test_mode' => $testMethods,
                'pay_group' => $payGroup,
                'user_data' => $userStats,
            ];
            $resp = json_decode($this->request("getPayMethods", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return collect([]);
            $m = [];
            foreach ($resp['methods'] as &$method) {
                $m[] = $this->PayMethodFromGoData($method);
            }
            return collect($m);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return collect([]);
    }

    public function getPayMethodById(int $id): ?PaymentSystems
    {
        try {
            $payload = [
                "pay_method_id" => $id,
            ];
            $resp = json_decode($this->request("getPayMethodByID", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? 'err ' . __METHOD__);
            return $this->PayMethodFromGoData($resp['method']);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getPayMethodByTypeAndAlias(int $type, string $alias, int $currency_id = 0): ?PaymentSystems
    {
        try {
            $payload = [
                "pay_type" => $type,
                "alias" => $alias,
                "currency_id" => $currency_id,
            ];
            $resp = json_decode($this->request("getPayMethodByTypeAlias", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? "err getPayMethodByTypeAlias");
            return $this->PayMethodFromGoData($resp['method']);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getUserMoneyStats(int $user_id, string $type, int $currency_id, bool $update = false): ?UserMoneyStat
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "stats_type" => $type,
                "currency_id" => $currency_id,
                "update" => $update,
            ];
            $resp = json_decode($this->request("getUserMoneyStats", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? "err " . __METHOD__);
            return $this->moneyStatsFromGo($resp['stats']);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function userMoneyStatsAddStat(int $user_id, string $type, int $currency_id, float $amount, string $pay_type): ?UserMoneyStat
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "stats_type" => $type,
                "currency_id" => $currency_id,
                "amount" => $amount,
                "pay_type" => $pay_type,
            ];
            $resp = json_decode($this->request("addUserMoneyStat", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? "err " . __METHOD__);
            return $this->moneyStatsFromGo($resp['stats']);

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    /**
     * @param int $currency_id
     * @param int $pay_type
     * @param bool $testMethods
     * @return array
     */
    public function getPayGroups(int $currency_id, int $pay_type, bool $testMethods): array
    {
        try {
            $payload = [
                "currency_id" => $currency_id,
                'pay_type' => $pay_type,
                'test_mode' => $testMethods,
            ];
            $resp = json_decode($this->request("getPayGroups", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? 'err ' . __METHOD__);
            if (empty($resp['groups'])) return [];
            ksort($resp['groups']);
            return $resp['groups'];
        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getPayMethodDataByKey(int $method_id, string $key): ?string
    {
        try {
            $payload = [
                "pay_method_id" => $method_id,
                "key" => $key,
            ];
            $resp = json_decode($this->request("methodDataByKey", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? __METHOD__);
            return $resp['data']['value']['String'] ?? null;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getPayMethodData(int $method_id): array
    {
        try {
            $payload = [
                "pay_method_id" => $method_id,
            ];
            $resp = json_decode($this->request("methodData", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? __METHOD__);
            $data = [];
            foreach ($resp['data'] ?? [] as $key => $d) {
                $data[$key] = $d['value']['String'];
            }
            return $data;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function getPaySystemParamByKey(int $system_id, string $key): ?string
    {
        try {
            $payload = [
                "system_id" => $system_id,
                "key" => $key,
            ];
            $resp = json_decode($this->request("systemParamByKey", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? __METHOD__);
            return $resp['data'] ?? null;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return null;
    }

    public function getPaySystemParams(int $system_id): array
    {
        try {
            $payload = [
                "system_id" => $system_id,
            ];
            $resp = json_decode($this->request("systemParams", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? __METHOD__);
            return $resp['data'] ?? [];

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return [];
    }

    public function paySystemSetParam(int $system_id, string $key, string $value): bool
    {
        try {
            $payload = [
                "system_id" => $system_id,
                "key" => $key,
                "value" => $value,
            ];
            $resp = json_decode($this->request("systemParamSet", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) throw new \Exception($resp['err'] ?? __METHOD__);
            return true;

        } catch (\Exception $e) {
            Hlog::logException($e);
        }
        return false;
    }

    public function processDeposit(PayInData $data): ResultOrErr
    {
        Log::info("deposit data:" . json_encode($data));
        try {
            $payload = [
                "deposit_data" => $data,
            ];
            $resp = json_decode($this->request("depositProcess", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['pay_data'] ?? []);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function otpConfirm(int $user_id, int $method_id, int $system_id, string $otp_id, string $otp_code): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "method_id" => $method_id,
                "system_id" => $system_id,
                "otp_id" => $otp_id,
                "otp_code" => $otp_code,
            ];
            $resp = json_decode($this->request("otpConfirm", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['success'] ?? false);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function otpResend(int $user_id, int $method_id, int $system_id, string $otp_id): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "method_id" => $method_id,
                "system_id" => $system_id,
                "otp_id" => $otp_id,
            ];
            $resp = json_decode($this->request("otpResend", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['success'] ?? false);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function isMultidepBlocked(int $user_id, int $method_id): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "method_id" => $method_id,
            ];
            $resp = json_decode($this->request("depositIsMultidepBlocked", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['block_to'])) {
                $resp['block_to'] = Carbon::parse($resp['block_to']);
            } else {
                $resp['block_to'] = null;
            }
            return ResultOrErr::success($resp['block_to'] ?? null);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function payoutCreateApplication(int $user_id, float $amount, int $currency_id, string $pay_method, array $requisites, bool $process_autopay = false): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "amount" => $amount,
                "currency_id" => $currency_id,
                "pay_method" => $pay_method,
                "requisites" => $requisites,
                "process_autopay" => $process_autopay,
            ];
            Log::info("payoutCreateApplication", $payload);
            $resp = json_decode($this->request("payoutCreateApplication", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['application_id']);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function payoutCancelApplication(int $user_id, int $application_id): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "application_id" => $application_id,
            ];
            Log::info("payoutCancelApplication", $payload);
            $resp = json_decode($this->request("payoutCancelApplication", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function payoutProcessApplication(int $application_id): ResultOrErr
    {
        try {
            $payload = [
                "application_id" => $application_id,
            ];
            Log::info("payoutProcessApplication", $payload);
            $resp = json_decode($this->request("payoutProcessApplication", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function payoutChangeState(int $application_id, int $new_state, string $reason = ""): ResultOrErr
    {
        try {
            $payload = [
                "application_id" => $application_id,
                "new_state" => $new_state,
                "reason" => $reason,
            ];
            Log::info("payoutChangeState", $payload);
            $resp = json_decode($this->request("payoutChangeState", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function payoutGetRequisites(int $method_id): ResultOrErr
    {
        try {
            $payload = [
                "method_id" => $method_id,
            ];
            Log::info("payoutGetRequisites", $payload);
            $resp = json_decode($this->request("payoutGetRequiredRequisites", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['requisites'] ?? []);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoWalletsGet(int $user_id): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("cryptoWalletsGet", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            $wallets = [];
            if (!empty($resp['wallets'])) {
                foreach ($resp['wallets'] as $w) {
                    $wallets[] = $this->CryptoWalletFromGoData($w);
                }
            }
            return ResultOrErr::success($wallets);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoWalletGetById(int $wallet_id): ResultOrErr
    {
        try {
            $payload = [
                "wallet_id" => $wallet_id,
            ];
            $resp = json_decode($this->request("cryptoWalletGetByID", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['wallet'])) {
                return ResultOrErr::success($this->CryptoWalletFromGoData($resp['wallet']));
            }
            return ResultOrErr::error("no wallet in response");
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoWalletGet(int $user_id, string $system_id, string $payment_type, string $currency, string $fiat_currency = null): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "system_id" => $system_id,
                "payment_type" => $payment_type,
                "currency" => $currency,
            ];
            if (!empty($fiat_currency)) $payload['fiat_currency'] = $fiat_currency;

            $resp = json_decode($this->request("cryptoWalletGet", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['wallet'])) {
                return ResultOrErr::success($this->CryptoWalletFromGoData($resp['wallet']));
            }
            return ResultOrErr::error("no wallet in response");
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoWalletGetOrCreate(int $user_id, string $system_id, string $payment_type, string $currency, string $fiat_currency = null): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "system_id" => $system_id,
                "payment_type" => $payment_type,
                "currency" => $currency,
            ];
            if (!empty($fiat_currency)) $payload['fiat_currency'] = $fiat_currency;

            $resp = json_decode($this->request("cryptoWalletGetOrCreate", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['wallet'])) {
                return ResultOrErr::success($this->CryptoWalletFromGoData($resp['wallet']));
            }
            return ResultOrErr::error("no wallet in response");
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoWalletUpdate(int $wallet_id, string $address, string $service_user_id, string $service_outer_id): ResultOrErr
    {
        try {
            $payload = [
                "wallet_id" => $wallet_id,
                "address" => $address,
                "service_user_id" => $service_user_id,
                "service_outer_id" => $service_outer_id,
            ];

            $resp = json_decode($this->request("cryptoWalletUpdate", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoCourseGet(int $system_type, string $token, string $fiat_currency): ResultOrErr
    {
        try {
            $payload = [
                "system_type" => $system_type,
                "token" => $token,
                "fiat_currency" => $fiat_currency,
            ];
            $resp = json_decode($this->request("cryptoCourseGet", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (empty($resp['course'])) return ResultOrErr::error("no course in response");
            return ResultOrErr::success($resp['course']);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoGetDepositAddress(PayInData $payInData, int $method_id): ResultOrErr
    {
        try {
            $payload = [
                "payin_data" => $payInData,
                "pay_method_id" => $method_id,
            ];
            $resp = json_decode($this->request("cryptoGetDepositAddress", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (empty($resp['address'])) return ResultOrErr::error("no address in response");
            return ResultOrErr::success($resp['address']);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoGetPayoutWallets(int $user_id, string $system_id, string $currency): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "system_id" => $system_id,
                "currency" => $currency,
            ];
            $resp = json_decode($this->request("cryptoGetPayoutWallets", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            $wallets = [];
            if (!empty($resp['wallets'])) {
                foreach ($resp['wallets'] as $w) {
                    $wallets[] = $this->CryptoWalletFromGoData($w);
                }
            }
            return ResultOrErr::success($wallets);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cryptoPayoutWalletGetOrCreate(int $user_id, string $system_id, string $currency, string $address): ResultOrErr
    {
        try {
            $payload = [
                "user_id" => $user_id,
                "system_id" => $system_id,
                "currency" => $currency,
                "address" => $address,
            ];
            if (!empty($fiat_currency)) $payload['fiat_currency'] = $fiat_currency;

            $resp = json_decode($this->request("cryptoGetOrCreatePayoutWallet", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['wallet'])) {
                return ResultOrErr::success($this->CryptoWalletFromGoData($resp['wallet']));
            }
            return ResultOrErr::error("no wallet in response");
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function getDepositCascades(int $user_id, int $currency_id, bool $testMethods, ?UserStatsData $userStats = null): ResultOrErr
    {
        try {
            if ($userStats === null && $user_id === 0) $userStats = new UserStatsData();
            $payload = [
                "user_id" => $user_id,
                "currency_id" => $currency_id,
                'test_mode' => $testMethods,
                'user_data' => $userStats,
            ];
            $resp = json_decode($this->request("cascadeGetDepositCascades", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            if (!empty($resp['cascades'])) return ResultOrErr::success($resp['cascades']);
            return ResultOrErr::error("no cascades in response");
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function cascadeProcessDeposit(int $cascade_id, PayInData $data): ResultOrErr
    {
        Log::channel('pay')->info("cascade deposit id:" . $cascade_id);
        Log::channel('pay')->info("cascade deposit data:" . json_encode($data));
        try {
            $payload = [
                "cascade_id" => $cascade_id,
                "deposit_data" => $data,
            ];
            $resp = json_decode($this->request("cascadeDepositProcess", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                $result = ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
                if (!empty($resp['next_sum'])) {
                    $result->setResult(['next_sum' => $resp['next_sum']]);
                }
                return $result;
            }
            return ResultOrErr::success($resp['pay_data']);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function getCascadeRequisitesType(int $cascade_id): ResultOrErr
    {
        try {
            $payload = ["cascade_id" => $cascade_id];
            $resp = json_decode($this->request("cascadeGetRequisitesType", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success($resp['data']);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function depositConfirm(int $transaction_id, int $user_id): ResultOrErr
    {
        try {
            $payload = [
                "transaction_id" => $transaction_id,
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("depositConfirm", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function depositCancel(int $transaction_id, int $user_id): ResultOrErr
    {
        try {
            $payload = [
                "transaction_id" => $transaction_id,
                "user_id" => $user_id,
            ];
            $resp = json_decode($this->request("depositCancel", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) return ResultOrErr::error($resp['err'] ?? __METHOD__ . " error");
            return ResultOrErr::success(true);
        } catch (\Exception $e) {
            Hlog::logException($e);
            return ResultOrErr::error($e->getMessage());
        }
    }

    public function reloadService(): ?string
    {
        try {
            $payload = [
            ];
            $resp = json_decode($this->request("serviceReload", $payload), true);
            if (empty($resp) || $resp['cd'] !== 200) {
                throw new \Exception($resp['err'] ?? "Error reloadService");
            };
            return null;

        } catch (\Exception $e) {
            Hlog::logException($e);
            return $e->getMessage();
        }
    }

    private function PayMethodFromGoData(array &$data): PaymentSystems
    {
        $data['bank_alias'] = $data['bank_alias']['Valid'] ? $data['bank_alias']['String'] : '';
        $data['min_sum'] = $data['min_sum']['Valid'] ? $data['min_sum']['Float64'] : 0;
        $data['max_sum'] = $data['max_sum']['Valid'] ? $data['max_sum']['Float64'] : 0;
        return new PaymentSystems($data);
    }

    private function moneyStatsFromGo(array &$data): UserMoneyStat
    {
        $data['first_dep'] = $data['first_dep']['Valid'] ? Carbon::parse($data['first_dep']['Time']) : null;
        $data['last_dep'] = $data['last_dep']['Valid'] ? Carbon::parse($data['last_dep']['Time']) : null;
        $data['first_payout'] = $data['first_payout']['Valid'] ? Carbon::parse($data['first_payout']['Time']) : null;
        $data['last_payout'] = $data['last_payout']['Valid'] ? Carbon::parse($data['last_payout']['Time']) : null;
        return new UserMoneyStat($data);
    }

    private function CryptoWalletFromGoData(array &$data): UserCryptoWallet
    {
        $data['fiat_currency'] = $data['fiat_currency']['Valid'] ? $data['fiat_currency']['String'] : '';
        $data['service_user_id'] = $data['service_user_id']['Valid'] ? $data['service_user_id']['String'] : '';
        $data['service_outer_id'] = $data['service_outer_id']['Valid'] ? $data['service_outer_id']['String'] : '';
        return new UserCryptoWallet($data);
    }

    private function request(string $method, array $payload)
    {
        $endpoint = $this->host . "/api/$method";
        $resp = Http::withToken($this->token)->acceptJson()->post($endpoint, $payload)->body();
        Log::info($resp);
        return $resp;
    }
}
