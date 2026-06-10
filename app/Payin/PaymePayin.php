<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\Betatransfer\Betatransfer;
use App\PaymentsAPI\BitZone\BitZone;
use App\PaymentsAPI\Payme\Payme;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PaymePayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Payme";
        $this->type = self::TYPE_PAYME;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Payme';
        $transaction->status_id = PayoutTransaction::STATUS_PAYME_PENDING;
        $transaction->save();

        $pay_method = "";
        $fiat = 0;
        if (str_contains($method, ":")) {
            $pay_method = explode(':', $method)[1];
            $fiat = 1;
            if (str_contains($method, "nonfiat")) $fiat = 0;
        }

        $api = new Payme();
        $resp = $api->depositForm($transaction->getId(), $amount, $user->getWallet($currency)->getCurrencyName(), $user_id, $fiat, $pay_method);
        if (empty($resp['url'])) {
            $this->_error = $resp['message'] ?? 'system error';
            return false;
        }
        return ['url' => $resp['url']];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        $method = $system?->method_alias;
        if ($method === 'Payme.RUB_amobile:card_number' && $deposits_count < 3) return true;
        if ($method === 'Payme.RUB_amobile:by_mobile' && $deposits_count < 3) return true;
        return false;
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYME_PENDING,
            PayoutTransaction::STATUS_PAYME_SUCCESS,
            PayoutTransaction::STATUS_PAYME_FAIL,
        ];
    }
}
