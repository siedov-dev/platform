<?php

namespace App\Payin;

use App\MUser;
use App\PaymentsAPI\Antarex\Antarex;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AntarexPayin extends Payin
{

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Antarex";
        $this->type = self::TYPE_ANTAREX;
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
        $transaction->operation = 'Пополнение счета через Antarex';
        $transaction->status_id = PayoutTransaction::STATUS_ANTAREX_PENDING;
        $transaction->save();

        $api = new Antarex();
        $method = explode('.', $method)[1];

        if ($method === 'sber') {

            $resp = $api->depositCps($amount, $currency, $transaction);

            if (empty($resp['success']) || $resp['success'] !== true) {
                $this->_error = $resp['errorMessage'] ?? 'system error';
                return false;
            }

            $transaction->o_tr_id = $resp['externalId'];
            $transaction->save();

            return ['url' => $resp['paymentURL'], 'in_frame' => true];

        } elseif ($method === 'sbp') {

            $resp = $api->depositFps($amount, $currency, $transaction);

            if (empty($resp['success']) || $resp['success'] !== true) {
                $this->_error = $resp['errorMessage'] ?? 'system error';
                return false;
            }

            $transaction->o_tr_id = $resp['externalID'];
            $transaction->save();

            return ['url' => $resp['formURL'], 'in_frame' => true];

        } elseif (explode('_', $method)[1] === 'p2p') {

            $resp = $api->depositPtp($amount, $currency, $transaction);
            if (empty($resp['success']) || $resp['success'] !== true) {
                $this->_error = $resp['errorMessage'] ?? 'system error';
                return false;
            }
            $transaction->o_tr_id = $resp['externalID'];
            $transaction->save();
            return ['url' => $resp['formURL'], 'in_frame' => true];

        } else {

            $resp = $api->bankSelectUrl($amount, $currency, $transaction->id);

            if (empty($resp['paymentLink'])) {
                $this->_error = $resp['errorMessage'] ?? 'system error';
                return false;
            }

            $transaction->coment = $resp['txId'] ?? '';
            $transaction->save();
            return ['url' => $resp['paymentLink']];
        }
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ANTAREX_PENDING,
            PayoutTransaction::STATUS_ANTAREX_SUCCESS,
            PayoutTransaction::STATUS_ANTAREX_FAIL,
        ];
    }
}
