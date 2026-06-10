<?php

namespace App\PaymentsAPI\EasyBits;

use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\Wallet;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EasyBits
{
    private string $code_name = "kraken";
    private string $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJyZXN1bHQiOjEsIm1lc3NhZ2UiOiJQdWJsaWMga2V5IHdhcyBzdWNjZXNzZnVsbHkgdXBkYXRlZC4gVGhhbmsgeW91LiJ9.Olfmek1E1L1fJx1tTALykM4i27_cZVUhc1G3VPxQTOsyzFyGt3zIJlYvLXuHNkpSbJY7lytb_wjcuE3srsWoOhzRkxhzGEmOAYk8co-7juBAFI0_-vieyU0WD9yBuYRzIgLm76yTxED5g55Lidmgyv3afcjrHF2vLuY8om9dMn7WHOrQZ7LP2lVEaUyLyhua4dHEcpfSTOTrYfEM7tAdLg_tJ61_ucxy7tivgtG8giD2SC8s28nIKULqyvHtQV4MAAPyvGtMbqJ3pygg8Zd4Of4o3KJ-RDU0HEeWfHTj9_Ru_8id7vLzTKL2Mb3yLg1T3aj4djcPBdsJ3Jm70bG1Dw";
    private string $endpoint = "https://capi.paycard.finance";

    private string $pub_key = "";
    private string $priv_key = "";

    public function __construct()
    {
        $this->pub_key = file_get_contents(__DIR__.'/pubkey.pem');
        $this->priv_key = file_get_contents(__DIR__.'/privkey.pem');
    }

    /**
     * Запрос депозита.
     * @param float $amount
     * @param string $tr_id
     * @param string $currency
     * @return array|null
     */
    public function deposit(float $amount, string $tr_id, string $currency = 'UAH', ?MUser $user = null): ?array
    {
        $url = $this->endpoint . "/v1/invoice/create";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'amount' => (string)$amount,
                'currency' => $currency,
                'externalId' => $tr_id,
                'feeCalculationType' => 'INNER',
                'expiresInMinutes' => 15,
                'customer' => [
                    'id' => (string)$user?->id,
                ],
                'callbackUrl' => Hosts::getFinHost('UAH').'/api/pay/easybits?pid={paymentId}&tid='.$tr_id,
                'returnUrl' => route('pay.close-tab'),
                'withQR' => 'NO',
            ]
        ];
        if ($user !== null && !empty($user->email)) {
            $data['data']['email'] = $user->email;
        }

        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');

        return $this->query($url, $data);
    }

    /**
     * Запрос выплаты.
     * @param float $amount
     * @param string $tr_id
     * @param string $card
     * @param string $currency
     * @return array|null
     */
    public function payout(float $amount, string $tr_id, string $card, string $currency = 'UAH'): ?array
    {
        $cents = floor($amount * 100);
        $url = $this->endpoint . "/v1/payment/out/create";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'amount' => $cents,
                'currency' => $currency,
                'externalId' => $tr_id,
                'cardTo' => [
                    'pan' => $card,
                ],
            ]
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');
        return $this->query($url, $data);
    }

    /**
     * Подтверждение выплаты.
     * @param string $pid
     * @return array|null
     */
    public function payoutConfirm(string $pid): ?array
    {
        $url = $this->endpoint . "/v1/payment/out/pay";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'paymentId' => $pid,
            ]
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');
        return $this->query($url, $data);
    }

    /**
     * Статус выплаты.
     * @param string $pid
     * @return array|null
     */
    public function payoutStatus(string $pid): ?array
    {
        $url = $this->endpoint . "/v1/payment/out/status";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'paymentId' => $pid,
            ]
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');
        return $this->query($url, $data);
    }

    /**
     * Запрос депозита.
     * @param float $amount
     * @param string $tr_id
     * @param string $currency
     * @return array|null
     */
    public function depositP2p(float $amount, string $tr_id, string $currency = 'UAH'): ?array
    {
        $cents = floor($amount * 100);
        $url = $this->endpoint . "/v1/payment/in/create/card";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'amount' => $cents,
                'currency' => $currency,
                'type' => 'CARD',
                'callbackUrl' => Hosts::getFinHost('UAH').'/api/pay/easybits?pid={paymentId}&tid='.$tr_id,
            ]
        ];

        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');

        return $this->query($url, $data);
    }

    /**
     * Возвращает данные об инвойсе.
     * @param string $pid
     * @return array|null
     */
    public function invoiceGet(string $pid): ?array
    {
        $url = $this->endpoint . "/v1/invoice/get";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'invoiceId' => $pid,
            ]
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');

        return $this->query($url, $data);
    }

    /**
     * Подтверждение п2п депозита
     * @param string $pid
     * @return array|null
     */
    public function confirmP2p(string $pid): ?array
    {
        $url = $this->endpoint . "/v1/payment/in/pay";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'paymentId' => $pid,
            ]
        ];

        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');

        return $this->query($url, $data);
    }

    /**
     * Возвращает данные о статусе транзакции.
     * @param string $pid
     * @return array|null
     */
    public function status(string $pid): ?array
    {
        $url = $this->endpoint . "/v1/payment/in/status";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [
                'paymentId' => $pid,
            ]
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');

        return $this->query($url, $data);
    }

    /**
     * Запрос к API.
     * @param string $url
     * @param array $data
     * @return mixed
     */
    private function query(string $url, array $data = [])
    {
        Log::channel('pay')->info('EasyBits request: '.$url);
        $json_data = json_encode($data, JSON_FORCE_OBJECT);
        Log::channel('pay')->info('EasyBits request body: '.$json_data);;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        $result = curl_exec($ch);
        Log::channel('pay')->info('EasyBits resp:');
        Log::channel('pay')->info($result);
        Log::channel('pay')->info("status:".curl_error($ch));
        Log::channel('pay')->info("status code:".curl_errno($ch));
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * Колбэк депозита.
     * @param Request $request
     */
    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->all(), true));

        $data = $request->all();

        if (!isset($data['tid'])) {
            Log::channel('pay')->error('tid field not set');
            return response('', 500);
        }

        $order_id = $data['tid'];

        Log::channel('pay')->info('Start processing EasyBits transaction with id ' . $order_id);

        DB::beginTransaction();
        try {
            /** @var PayoutTransaction $transaction */
            $transaction = PayoutTransaction::where('id', $order_id)
                ->whereIn('status_id', [
                    PayoutTransaction::STATUS_EASYBITS_PENDING,
                    PayoutTransaction::STATUS_EASYBITS_FAIL,
                ])
                ->lockForUpdate()
                ->first();

            if ($transaction?->getStatusId() === PayoutTransaction::STATUS_EASYBITS_FAIL) {
                Log::channel('pay')->error('Transaction with id ' . $order_id . ' in failed state found. Request: ' . PHP_EOL . var_export($request->all(), true));
            }

            if ($transaction === null) {
                DB::commit();
                if (PayoutTransaction::where('id', $order_id)
                        ->where('status_id', PayoutTransaction::STATUS_EASYBITS_SUCCESS)
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

            // получаем статус платежа
            $api = new EasyBits();
            $t_status = $api->status($data['pid']);
            if (empty($t_status)) {
                Log::channel('pay')->error(__CLASS__.' status is empty');
                return response('status is empty', 500);
            }

            $pay_status = $t_status['data']['status'];

            if ($pay_status === 'CANCELED' || $pay_status === 'REJECTED' || $pay_status === 'RETURNED' || $pay_status === 'EXPIRED') {
                Log::channel('pay')->error('status:' . $pay_status);
                $transaction->status_id = PayoutTransaction::STATUS_EASYBITS_FAIL;
                $transaction->coment = $pay_status;
                $transaction->save();
                DB::commit();
                return 'ok';
            }

            if ($pay_status !== 'PAID') {
                return 'ok';
            }

            $amount = $t_status['data']['amount'] / 100;

            $currency = Wallet::CURRENCY_UAH;

            $transaction->fee = $t_status['data']['fee']?? 0;
            $transaction->fee =  $transaction->fee / 100;
            $amount = $amount + $transaction->fee;

            Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_EASYBITS_SUCCESS, $currency);
        } catch (\Throwable $e) {
            Hlog::logException($e, 'pay');
        } finally {
            DB::commit();
        }


        return response('ok', 200);
    }

    /**
     * Устанавливает ключ.
     * Использовать только в первый раз.
     * @return null|array
     */
    public function publicKeyPut(): ?array
    {
        $url = $this->endpoint . "/v1/client/public/put";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
//                'token' => $this->token,
            ],
            'data' => [
                'clientPublicKey' => $this->pub_key,
            ],
        ];

        return $this->query($url, $data);
    }

    /**
     * Удаление публичного ключа.
     * Работает только в тестовой среде.
     * @return array
     */
    public function publicKeyDelete(): array
    {
        $url = $this->endpoint . "/v1/client/public/delete";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [],
        ];
        return $this->query($url, $data);
    }

    /**
     * Получение публичного ключа для верификации ответов.
     * @return array
     */
    public function publicKeyGet(): array
    {
        $url = $this->endpoint . "/v1/host/public/get";
        $data = [
            'auth' => [
                'clientCodeName' => $this->code_name,
            ],
            'data' => [],
        ];
        $data['auth']['token'] = JWT::encode($data['data'], $this->priv_key, 'RS256');
        return $this->query($url, $data);
    }

    /**
     * Генерация RSA ключей.
     * Сохранить ключи в pubkey.pem, privkey.pem.
     * @return void
     */
    public function genRsaKeys(): void
    {
        // Генерация новой пары ключей
        $resource = openssl_pkey_new([
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "private_key_bits" => 2048,
        ]);

        // Извлечение приватного ключа в переменную
        openssl_pkey_export($resource, $privateKey);

        // Получение публичного ключа из пары
        $publicKeyDetails = openssl_pkey_get_details($resource);
        $publicKey = $publicKeyDetails["key"];

        // Вывод ключей
        echo "Private Key:\n" . $privateKey . "\n";
        echo "Public Key:\n" . $publicKey . "\n";
    }
}
