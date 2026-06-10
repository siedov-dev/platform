<?php

namespace App\PaymentsAPI\XPayPro;

use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://merchant.basicpay.org/api-docs
 */
final class XPayPro
{
    private string $api_key = "mta3_eDgL6JzNWKRhnwcBfpYbXxjJ7AFzbTVeZgkzLgp7gWF7igA3vQoX0agRZKt";
//    private string $url = "https://api.test.xpaypro.dev";
    private string $url = "https://api.basicpay.org";

    public function balance(): array
    {
        $endpoint = $this->url . "/v1/merchant-api/balance";
        $data = [
            'currency' => 'USDT',
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function createP2pInvoice(string $tr_id, string $client_id, float $amount, string $method, string $currency_name = 'RUB'): array
    {
        $endpoint = $this->url . "/v1/merchant-api/txs/p2p/invoice";
        $data = [
            'fiat_currency' => $currency_name,
            'fiat_amount' => number_format($amount, 2, '.', ''),
            'crypto_currency' => 'USDT',
            'payment_method' => $method,
            'merchant_tx_id' => $tr_id,
            'merchant_client_id' => $client_id,
        ];

        return $this->query($endpoint, $data);
    }

    public function listWidgets(): array
    {
        $endpoint = $this->url . "/v1/merchant-api/widgets";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function getWidgetOrder(string $order_id): array
    {
        $endpoint = $this->url . "/v1/merchant-api/widget-orders/{order_id}";
        $data = [
            'order_id' => $order_id,
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function createWidgetP2pOrder(string $tr_id, string $client_id, float $amount, string $widget_id, string $currency_name = 'RUB'): array
    {
        $endpoint = $this->url . "/v1/merchant-api/widgets/$widget_id/order/p2p/invoice";
        $data = [
            'fiat_currency' => $currency_name,
            'fiat_amount' => number_format($amount, 2, '.', ''),
            'crypto_currency' => 'USDT',
//            'variable_fiat_amount' => true,
            'is_variable_amount' => true,
            'merchant_tx_id' => $tr_id,
            'merchant_client_id' => $client_id,
            'redirect_url_success' => route('pay.success', [], true),
            'redirect_url_fail' => route('pay.fail', [], true),
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function createWidgetC2cOrder(string $tr_id, string $client_id, float $amount, string $widget_id, string $currency_name = 'RUB'): array
    {
        $endpoint = $this->url . "/v1/merchant-api/widgets/$widget_id/order/c2c/invoice";
        $data = [
            'geo' => 'TJ',
            'fiat_currency' => $currency_name,
            'fiat_amount' => number_format($amount, 2, '.', ''),
            'crypto_currency' => 'USDT',
//            'variable_fiat_amount' => true,
            'is_variable_amount' => true,
            'merchant_tx_id' => $tr_id,
            'merchant_client_id' => $client_id,
            'redirect_url_success' => route('pay.success', [], true),
            'redirect_url_fail' => route('pay.fail', [], true),
        ];
        return $this->query($endpoint, $data, 'post');
    }

    public function getTransaction(string $tx_id): array
    {
        $endpoint = $this->url . "/v1/merchant-api/txs/$tx_id";
        $data = [
        ];
        return $this->query($endpoint, $data, 'get');
    }

    public function createP2pWithdraw(string $client_id, float $amount, string $card, string $method,
                                      array $requisites, string $currency_name = 'RUB'): array
    {
        $endpoint = $this->url . "/v1/merchant-api/txs/p2p/withdraw";
        $data = [
            'fiat_currency' => $currency_name,
            'fiat_amount' => number_format($amount, 2, '.', ''),
            'crypto_currency' => 'USDT',
            'payment_method' => $method,
            'payment_requisite' => $card,
            'merchant_client_id' => $client_id,
        ];
        if ($method === 'BANK_CARD') {
            $data['bank_name'] = $requisites['bank_name'];
        }
        if ($method === 'SBP') {
            $data['payment_requisite'] = $requisites['phone'];
            $data['bank_code'] = $requisites['ncpk'];
        }
        return $this->query($endpoint, $data, 'post');
    }

    private function query(string $url, array $data = [], string $httpMethod = 'post')
    {
        Log::channel('pay')->info(__CLASS__ . " query $url method $httpMethod data:", $data);

        if (mb_strtolower($httpMethod) === 'get') {
            $result = Http::withToken($this->api_key)->acceptJson()->get($url, $data)->json();
        } else {
            $result = Http::withToken($this->api_key)->acceptJson()->post($url, $data)->json();
        }

        Log::channel('pay')->info(__CLASS__ . " query $url response:", $result);
        return $result;
    }

    public static function webhook(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . " webhook:", $request->all());
        $data = $request->all();

        if (!isset($data['tx']['merchant_tx_id'])) {
            Log::channel('pay')->error('merchant_tx_id field not set');
            return response('', 404);
        }

        $order_id = $data['tx']['merchant_tx_id'];

        Log::channel('pay')->info('Start processing XPayPro transaction with id ' . $order_id);

        try {
            DB::beginTransaction();

            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                        PayoutTransaction::STATUS_XPAYPRO_PENDING,
                        PayoutTransaction::STATUS_XPAYPRO_FAIL,
                    ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->status_id === PayoutTransaction::STATUS_XPAYPRO_FAIL) {
                Log::channel('pay')->warning('Transaction with id ' . $order_id . ' already failed found. Processing');
            }

            if ($transaction === null) {
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_XPAYPRO_SUCCESS)
                        ->first() !== null) {
                    Log::channel('pay')->error('Transaction with id ' . $order_id . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                    return response('ok', 200);
                }
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('transaction not found', 404);
            }

            // Find user which create this transaction.
            /** @var MUser $user $user */
            $user = MUser::where('id', $transaction->user_id)->first();

            if ($user === null) {
                Log::channel('pay')->error('User of the transaction is not found.');
                return response('user not found', 500);
            }

            if (!str_ends_with($data['tx']['tx_direction'], '_IN')) {
                Log::channel('pay')->info('Not payin direction ' . $data['tx']['tx_direction']);
                return 'ok';
            }

            if ($data['tx']['tx_status'] === 'TX_CANCELLED' || $data['tx']['tx_status'] === 'TX_EXPIRED') {
                Log::channel('pay')->error('event:' . $data['tx']['tx_status']);
                $transaction->status_id = PayoutTransaction::STATUS_XPAYPRO_FAIL;
                $transaction->coment = $data['tx']['tx_status'] ?? ' tx_status error';
                $transaction->save();
                return 'ok';
            }

            if ($data['tx']['tx_status'] !== 'TX_SUCCESS' && $data['tx']['tx_status'] !== 'TX_RECALCULATED') {
                $transaction->coment = $data['tx']['tx_status'] ?? 'pending status';
                $transaction->save();
                return 'ok';
            }

            $amount = $data['tx']['in_amount'];

            $currency = UserWallet::getCurrencyCode($data['tx']['in_currency']);
            $transaction->fee = 0;
            if (!empty($data['tx']['fee_pct'])) {
                $transaction->fee = $amount * $data['tx']['fee_pct'] / 100;
            }
            $transaction->card_number = $data['tx']['payment_requisite'] ?? '';

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_XPAYPRO_SUCCESS, $currency);

        } catch (\Throwable $e) {
            DB::commit();
            Log::channel('pay')->error($e->getMessage());
        } finally {
            DB::commit();
        }

        return response('ok', 200);
    }
}
