<?php

namespace App\Services\KPay;

use App\MUser;
use App\PaymentsAPI\Pspy\PspyApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Http;

class KPayTests
{
    public static function pspyCallback(int $user_id, float $amount, int $currency_id)
    {
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $transaction = new PayoutTransaction();
        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency_id)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Pspy test';
        $transaction->status_id = PayoutTransaction::STATUS_PSPY_PENDING;
        $transaction->save();

        $payload = [
            'id' => 'test'.time(),
            'referenceId' => $transaction->id,
            'created' => now()->toDateTimeString(),
            'paymentType' => 'DEPOSIT',
            'state' => 'COMPLETED',
            'amount' => $amount,
            'currency' => $transaction->currency,
            'errorCode' => '0000',
            'errorMessage' => '',
            'shopName' => 'test pspy',
        ];
        $api = new PspyApi();
        $sign = $api->sign(json_encode($payload));
        $resp = Http::asJson()
            ->withHeaders([
                'SignatureAuth' => $sign,
            ])
            ->post('http://localhost:23106/pay/pspy/deposit', $payload);
        dump($resp->body());
    }
}
