<?php

namespace App\PaymentsAPI\Repay;

use App\ApplicationForPayment;
use App\Helpers\Hlog;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PaymentsAPI\ApiBase;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class Repay extends ApiBase
{
    protected string $api_url = 'https://repay.cx/';
    protected string $cassa_id = '6819b617-9490-463c-b213-ca0cb3916f30';
    protected string $cassa_id_out = 'ed43dc92-31bd-4a07-98ae-ad0a555011be';
    protected string $api_key = '3c795105-c5a2-4689-9cdd-49ad50e08ef2';
    protected string $api_key_out = '34b8ba18-85c8-498a-a417-a56635c7ff7c';
    protected string $secret_key = 'b80fdb37-bb1b-4898-81a3-67319b1cc124';
    protected string $secret_key_2 = 'd9fe4547-7a8e-4ff1-aacd-cab4f423bbd3';

    public function get(string $transaction_id, bool $out = false): object|bool
    {
        $this->headers['api-key'] = $out ? $this->api_key_out : $this->api_key;

        return $this->request(
            uri: !$out ? 'api/v1/external/deals/' . $transaction_id : 'payout_api/v1/external/payout_deals/' . $transaction_id,
            post: false
        );
    }

    public function in(PayoutTransaction $transaction, bool $trusted = false, string $currency = 'RUB'): object|bool
    {
        list($system_name, $system_method) = explode('.', $transaction->method);
        $amount = (int)$transaction->sum;
        $request = request();

        $this->headers['api-key'] = $this->api_key;

        return $this->request(
            uri: 'api/v1/private/deals/',
            params: [
                'cassa_id' => $this->cassa_id,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method_id' => $this->getPaymentMethodIdByKey($system_method),
                'order_id' => (string)$transaction->id,
                'success_url' => route('pay.success'),
                'error_url' => route('pay.fail'),
                'cancel_url' => route('pay.close-tab'),
                'webhook_url' => Hosts::getFinHost('RUB') . '/api/pay/repay/callback',
                'signature' => $this->genSignature(
                    cassa_id: $this->cassa_id,
                    amount: $amount
                ),
                'payload' => [
                    'user_id' => (string)$transaction->user_id,
                    'user_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'trusted' => $this->getPaymentMethodTrustedByKey($system_method),
                ]
            ],
        );
    }

    public function out(ApplicationForPayment $transaction, int $amount, string $bank_id, string $card_number, $type = 'card_number', string $option_id = ""): object|bool
    {
        $this->headers['api-key'] = $this->api_key_out;
        $params = [
            'amount' => $amount,
            'bank_id' => $bank_id,
            'requisites' => $card_number,
            'requisites_type' => $type,
            'cashier_id' => $this->cassa_id_out,
            'webhook_url' => Hosts::getFinHost('RUB') . '/api/payout/repay/callback',
            'order_id' => (string)$transaction->id,
        ];
        if (!empty($option_id)) {
            $params['option_id'] = $option_id;
        }

        return $this->request(
            uri: 'payout_api/v1/external/payout_deals/',
            params: $params,
        );
    }

    public function balance()
    {
        $this->headers['api-key'] = $this->api_key;

        return $this->request(
            uri: 'api/v1/external/cashiers/balance',
            params: [],
            post: false,
        );
    }

    public function balanceOut()
    {
        $this->headers['api-key'] = $this->api_key_out;

        return $this->request(
            uri: 'api/v1/external/cashiers/balance',
            params: [],
            post: false,
        );
    }

    public function sbpBanks(): array
    {
        return [
            "Морской Банк" => "408d4aba-1995-447b-af57-ea1967299d71",
            "Т-Банк" => "19235c82-622e-4286-888f-2ecd25c0b009",
            "Синара" => "14240e9a-ab93-46a6-a97f-c9872bd92894",
            "Солидарность Банк" => "68e0f916-7f89-496c-9102-def5bd6573b4",
            "Банк Россия" => "a01be51e-eb0b-4efd-9ccc-46cebc2a682c",
            "Цифра Банк" => "f797990d-cbd7-4b5e-9558-e9ec3d94ec4d",
            "СберБанк" => "a28333c4-db42-4805-b560-4db95ad50dca",
            "Альфа Банк" => "691103fb-7351-4480-8a6d-03a91f1a54b5",
            "Райффайзен Банк" => "f2d25a0d-65bc-4dbe-9e98-6c3015bbd90a",
            "Россельхозбанк" => "62381f29-0776-48c2-90ee-d7d68fe371ec",
            "Банк Уралсиб" => "c6ab69e2-dd38-4a1f-a313-69ea32fe5981",
            "Яндекс Банк" => "314b2f17-b03e-4eba-9c00-e5cab25c31db",
            "УБРиР" => "2a539b2c-b054-4c1c-95c0-ae5b098c0895",
            "ДОМ РФ" => "e7a77873-4020-429c-898d-01156aa12cf0",
            "Универсал банк Абхазия" => "d452a044-6586-4790-9ed0-50da6e9a3911",
            "Промсвязьбанк" => "e5cce7cb-665a-4aa3-8240-2002694869ee",
            "Совкомбанк" => "9bcc36cb-09a1-478b-9c9d-0758aa8b806c",
            "Ак Барс Банк" => "10831233-07ff-461f-9cb2-414e3a003786",
            "Хоум Кредит Банк (Хоум Банк)" => "ef10f896-5d53-47b0-bbf6-5b0f8f92027e",
            "Банк ФК Открытие" => "b028103a-7dc9-4c60-bb2b-dcee8656b65e",
            "ОТП Банк" => "e7de19bc-0af6-4288-b6ac-66c41a3af389",
            "Абсолют Банк" => "3041a129-2d56-4c8d-bba7-ea573538957c",
            "Амра-Банк" => "1503c1a9-8eb7-4e4f-bb92-79076c967537",
            "Русский стандарт" => "76d4d0ff-b1b3-4aea-bac8-ea3a45cfdbb0",
            "РНКБ Банк" => "b585dad8-a503-4b5e-a1c8-b981763accf3",
            "Почта Банк" => "60fe6929-a0ac-4d2a-84d3-1f1324e871d1",
            "Газпромбанк" => "0634762b-63a2-4b85-8e79-9d12a61874aa",
            "ВТБ" => "e93488d9-d2c9-404b-85f9-b37eb35a2f99",
            "Озон Банк (Ozon)" => "84a3cc4e-f94f-4081-ac5c-92420fb3e836",
            "ЮниКредит Банк" => "32ce7bfa-f0e2-4f04-9731-31a39bcd8c0c",
            "Wildberries Банк" => "4dd525f9-ab01-4043-a525-87237b16e7b9",
            "Росбанк" => "504d81e3-55d5-4313-bf45-5eae52879150",
            "МКБ" => "6ad58e62-1f44-4f40-8843-e7359e988db7",
            "Точка Банк" => "85445625-c9df-4d32-8c49-f84ee3e256c2",
            "VK Pay" => "a6d99cf7-16fe-40ff-8644-a88eb87e3968",
            "МТС-Банк" => "b3d8508e-82bb-4fb7-96c6-8dca6bee3c41",
            "Банк Авангард" => "b9a2a338-ccd7-4bb1-8bf3-e7b626f9ac37",
            "Банк Элита" => "d70ffacd-2020-4d27-851a-a823573038de",
        ];
    }

    public function callback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . ' callback deposit request');
        Log::channel('pay')->info(var_export($request->getUri(), true));
        Log::channel('pay')->info(var_export($request->getContent(), true));
        Log::channel('pay')->info(var_export($request->all(), true));

        $transaction_id = $request->get('order_id');
        $deal_id = $request->get('deal_id');

        try {
            DB::beginTransaction();
            $transaction = PayoutTransaction::where('id', $transaction_id)
                ->where('id', $transaction_id)
                ->lockForUpdate()
                ->first();

            if ($transaction === null) {
                return response('transaction not found', status: 404);
            }
            if ($transaction->status_id == PayoutTransaction::STATUS_REPAY_SUCCESS) {
                Log::channel('pay')->info(__CLASS__ . 'transaction already completed #' . $transaction_id);
                return response('OK', status: 200);
            }

            $response = $this->get($deal_id);
            $payload = $response?->payload;

            switch ($payload->state) {
                case 'created':
                case 'waiting_payment':
                case 'proceedings':
                case 'proceedingsN':
                    $transaction->status_id = PayoutTransaction::STATUS_REPAY_PENDING;
                    break;
                case 'completed':
                    if ($transaction->status_id != PayoutTransaction::STATUS_REPAY_SUCCESS) {
                        $user = MUser::where('id', $transaction->user_id)->first();

                        $amount = $payload->amount;
                        $transaction->fee = $amount * 0.09;

                        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_REPAY_SUCCESS, UserWallet::getCurrencyCode($payload->currency));
                    }
                    break;
                case 'rejected_timeout':
                case 'reject_by_office':
                case 'reject_manual_by_client':
                case 'rejected_by_system':
                case 'rejected_by_new_deal':
                    $transaction->status_id = PayoutTransaction::STATUS_REPAY_FAIL;
                    break;
            }

            $transaction->coment = 'status code: ' . $response->status_code;
            $transaction->save();
        } catch (\Exception $e) {
            Hlog::logException($e);
        } finally {
            DB::commit();
        }

        return response('OK', 200);

    }

    public function callback_payout(Request $request)
    {

        Log::channel('payout')->info(__CLASS__ . ' callback request');
        Log::channel('payout')->info(var_export($request->getUri(), true));
        Log::channel('payout')->info(var_export($request->getContent(), true));
        Log::channel('payout')->info(var_export($request->all(), true));

        return response('OK', 200);

    }

    protected function getPaymentMethodIdByKey(string $key): string
    {
        return match ($key) {
            'SBERPAY_STD' => 'b58f4f69-2122-4cf7-b9b4-a9b810dc6530',
            'SBERPAY_FTD' => 'da8f391a-7988-480a-9668-00857267ae2d',
            'SBP_STD', 'SBP_FTD' => '7c5cdc4b-7095-4e27-be80-9497faf60e00',
            'TPAY' => 'f93bb783-9b40-4d98-a167-0efa5acb32f0',
            'AlfaPay' => 'c2436fe9-687d-4b30-8379-4a30e24cabf3',
            'OneClick' => '5668b314-c955-4d30-8713-abb9221b4d7a',
            default => '',
        };
    }

    protected function getPaymentMethodTrustedByKey(string $key): bool
    {
        return in_array($key, ['SBERPAY_FTD', 'SBP_FTD']);
    }

    protected function genSignature(string $cassa_id, int $amount): string
    {
        return md5($cassa_id . ':' . $amount . ':' . $this->secret_key);
    }

}
