<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\PayCraft;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayCraftPayin extends Payin
{

    protected PayCraft $api;

    const STATUS_PENDING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_ERROR = 3;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayCraft";
        $this->type = self::TYPE_PAYCRAFT;
        $this->api = new PayCraft;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        /*if ($transaction === null) $transaction = new PayoutTransaction();*/

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;

        /*$transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Payin '. PayCraft::class;
        $transaction->status_id = PayoutTransaction::STATUS_PENDING;
        $transaction->save();*/

        $transaction = PayoutTransaction::firstOrCreate(
            [
                'user_id' => $user->id,
                'hall_id' => $user->hall_id,
                'sum' => $amount,
                'currency' => $user->getWallet($currency)->getCurrencyName(),
                'operation' => 'Payin '. PayCraft::class,
                'status_id' => PayoutTransaction::STATUS_PAYCRAFT_PENDING,
            ],
            [
                'date' => date("Y-m-d H:i:s")
            ],
        );

        if(!$transaction->wasRecentlyCreated) return ['modal_data' => view('pay.p2p.payin.paycraft', ['transaction' => $transaction])->render()];

        try {

            $response = $this->api->in(
                order_id: $transaction->id,
                amount: $amount,
                callback_url: route('payin.paycraft.check_status', ['transaction_id' => $transaction->id])
            );

            if(is_object($response) && isset($response->success) && $response->success){

                $transaction->card_number = $response->address;
                $transaction->o_tr_id = $response?->external_id ?? null;
                $transaction->save();

                return ['modal_data' => view('pay.p2p.payin.paycraft', ['transaction' => $transaction])->render()];

            }else{

                $this->_error = $response->reason ?? $response->message ?? 'System error. Please try again later.';

                $transaction->status_id = PayoutTransaction::STATUS_PAYCRAFT_FAIL;
                $transaction->coment = $this->_error;
                $transaction->save();

                return false;

            }

        } catch (\Exception $e) {

            Log::channel('pay')->error($e->getMessage());
            $this->_error = 'System error.';
            return false;
        }

    }

    public static function checkStatus(PayoutTransaction $transaction)
    {
        $response = PayCraft::checkStatus(
            order_id: $transaction->id,
            mode: 'in',
        );

        $msg = 'Unknown payment status.';

        if(!isset($response->status)) return response(['status' => false, 'message' => 'return status failed']);

        switch($response->status){
            case PayCraftPayin::STATUS_PENDING:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_PENDING;
                $msg = 'Waiting for payment.';
                break;
            case PayCraftPayin::STATUS_SUCCESS:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_SUCCESS;
                $msg = 'Payment successful.';
                break;
            case PayCraftPayin::STATUS_ERROR:
                $transaction->status = PayoutTransaction::STATUS_PAYCRAFT_FAIL;
                $msg = 'Payment error.';
                break;
        }

        $transaction->save();

        return [
            'status' => $transaction->status,
            'msg' => $msg,
            'changed' => $transaction->wasChanged()
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.paycraft', ['method' => $method])->render();
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYCRAFT_PENDING,
            PayoutTransaction::STATUS_PAYCRAFT_SUCCESS,
            PayoutTransaction::STATUS_PAYCRAFT_FAIL,
        ];
    }

}
