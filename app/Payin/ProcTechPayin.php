<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\ProcTech\ProcTechApi;
use App\PayoutTransaction;
use App\ResultOrErr;
use Illuminate\Support\Facades\Auth;

class ProcTechPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "ProcTech";
        $this->type = self::TYPE_PROCTECH;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
//        return ['modal_data' => view('pay.requisites.payin.otp-confirm',
//            ['otpid' => "test", 'pid' => self::TYPE_PROCTECH, 'mid' => $method_id])->render()];
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
        $transaction->operation = 'Пополнение счета через ProcTech';
        $transaction->status_id = PayoutTransaction::STATUS_PROCTECH_PENDING;
        $transaction->save();

        $api = new ProcTechApi();
        $method = explode('.', $method)[1];

        $resp = $api->deposit($transaction, $data);
        if (empty($resp['success'])) {
            $this->_error = $resp['errors'][0]['description']?? 'system error';
            return false;
        }

        $transaction->o_tr_id = $resp['token']??'';
        $transaction->save();
        return ['modal_data' => view('pay.requisites.payin.otp-confirm',
            ['otpid' => $resp['token'], 'pid' => self::TYPE_PROCTECH, 'mid' => $method_id])->render()];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PROCTECH_PENDING,
            PayoutTransaction::STATUS_PROCTECH_SUCCESS,
            PayoutTransaction::STATUS_PROCTECH_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        return view('pay.requisites.payin.phone-mail', ['method' => $method, 'pay_method' => $pay_method])->render();
    }

    public function confirmOtp(int $method_id, int $pay_system_id, string $otpid, string $otp_code): ResultOrErr
    {
//        sleep(3);
//        return ResultOrErr::error("asdasd");
        $api = new ProcTechApi();
        $resp = $api->confirmOtp($otpid, $otp_code);
        if (!empty($resp['success'])) {
            return ResultOrErr::success(true);
        }
        return ResultOrErr::error($resp['errors'][0]['description']??'OTP confirmation error');
    }

    public function resendOtp(int $method_id, int $pay_system_id, string $otpid): ResultOrErr
    {
//        sleep(3);
//        return ResultOrErr::success(true);
        $api = new ProcTechApi();
        $resp = $api->resentOtp($otpid);
        if (!empty($resp['success'])) {
            return ResultOrErr::success(true);
        }
        return ResultOrErr::error($resp['errors'][0]['description']??'OTP resend error');
    }
}
