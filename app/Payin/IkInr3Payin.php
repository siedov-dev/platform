<?php


namespace App\Payin;


use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\InterKassa\IkInr3;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IkInr3Payin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "InterKassaInr3";
        $this->type = self::TYPE_IKINR3;
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        $this->_error = null;

        $pay_system = PaymentSystems::getPayinSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
            throw new \Exception('PaymentSystems not found for alias ' . $method);
        }

        if ($pay_system->disabled) {
            $this->_error = "Payment system is disabled";
            return false;
        }

        if ($pay_system->min_sum > 0 && $amount < $pay_system->min_sum) {
            $this->_error = __('wallet.minimum_refill_amount', ['amount' => $pay_system->min_sum, 'curr' => 'inr']);
            return false;
        }

        if ($pay_system->additionalCheck()) {
            $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
                ->where('user_id', $this->user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->groupBy('user_id')
                ->first();

            if (!$payed || $payed->cnt < 3) {
                $this->_error = __('wallet.required_other_payins', ['pay_count' => 3]);
                return false;
            }
        }

//        if (empty($user->phone) || !$user->isPhoneVerified()) {
//            $this->_error = __('wallet.phone_not_verified');
//            return false;
//        }
//        if (empty($user->phone) || !$user->isEmailVerified()) {
//            $this->_error = __('wallet.email_not_verified');
//            return false;
//        }
//        if (!preg_match('#^(\+91)?([6,7,8,9][0-9]{9})$#', $user->phone)) {
//            $this->_error = __('wallet.wrong_inr_phone');
//            return false;
//        }

        return parent::canProceed($amount, $method, $data, $method_id);
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
        $transaction->operation = 'Пополнение счета InterKassa Inr3';
        $transaction->status_id = PayoutTransaction::STATUS_IK_PENDING;
        $transaction->coment = 'IkInr3';
        $transaction->save();

        $api = new IkInr3();
//        $api->setCheckout($user_id);
        $form = $api->getPayinFormData($transaction->id, $amount, $method, $data, $user);
        $resp = $api->getSciPayinForm($form);
        Log::channel('pay')->info('resp:' . var_export($resp, true));
        return ['url' => $resp['resultData']['paymentForm']['action']];
    }

    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        if (isset($data['phone'])) {
            //clear all non-numeric characters
            $data['phone'] = preg_replace('#[^+\d]#', '', $data['phone']);

            if (preg_match('#^(\+)?(91)?(\d{10})$#', $data['phone'], $match)) {
                $data['phone'] = $match[3];
            }
        }
        return $data;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $email = $this->getLastRequisite('email')?? $this->user->email;
        $phone = $this->getLastRequisite('phone')?? $this->user->phone;

        $phone =  preg_replace('#[^+\d]#', '', $phone??'');
        if (preg_match('#^(\+?)(91)?(\d{10})$#', $phone, $match)) {
            $phone = $match[3];
        }

        if ($method === 'IkInr3.UPI') {
            return view('pay.requisites.payin.IkInr3-UPI', [
                'email' => $email,
                'phone' => $phone,
                'name' => $this->getLastRequisite('first_name'),
                'surname' => $this->getLastRequisite('last_name'),
                'upi' => $this->getLastRequisite('upi'),
                'method' => $method,
            ])->render();
        }

        if ($method === 'IkInr3.Bank') {
            return view('pay.requisites.payin.IkInr3-Bank', [
                'email' => $email,
                'phone' => $phone,
                'name' => $this->getLastRequisite('first_name'),
                'surname' => $this->getLastRequisite('last_name'),
                'mop' => $this->getLastRequisite('mop'),
                'method' => $method,
            ])->render();
        }

        if ($method === 'IkInr3.Wallet') {
            return view('pay.requisites.payin.IkInr3-Wallet', ['method' => $method,])->render();
        }

        $wallet = explode('.', $method)[1];
        $mop = 101;
        switch ($wallet) {
            case 'paytm':
                $mop = 101;
                break;
            case 'airtelmoney':
                $mop = 103;
                break;
            case 'freecharge':
                $mop = 113;
                break;
            case 'MobiKwik':
                $mop = 102;
                break;
            case 'OlaMoney':
                $mop = 107;
                break;
            case 'PhonePe':
                $mop = 115;
                break;
            case 'Reliance':
                $mop = 106;
                break;
        }

        return view('pay.requisites.payin.IkInr3-Wallet', [
            'email' => $email,
            'phone' => $phone,
            'name' => $this->getLastRequisite('first_name'),
            'surname' => $this->getLastRequisite('last_name'),
            'mop' => $mop,
            'method' => $method,
        ])->render();
    }

    public function isBlocked($method): bool
    {
        $this->_error = null;

        $pay_system = PaymentSystems::getPayinSystemForMethod($method);

        if ($pay_system === null) {
            Log::channel('pay')->error('PaymentSystems not found for alias ' . $method);
            $this->_error = "system error";
            throw new \Exception('PaymentSystems not found for alias ' . $method);
        }

        if ($pay_system->additionalCheck()) {
            $payed = PayoutTransaction::selectRaw('user_id, sum(`sum`) as sum, count(id) as cnt')
                ->where('user_id', $this->user->id)
                ->whereIn('status_id', PayoutTransaction::getIncomingStatuses())
                ->groupBy('user_id')
                ->first();

            if (!$payed || $payed->cnt < 3) {
                return true;
            }
        }

        return false;
    }

    public function blockReason($method): string
    {
        return __('wallet.required_other_payins', ['pay_count' => 3]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_IK_PENDING,
            PayoutTransaction::STATUS_IK_SUCCESS,
            PayoutTransaction::STATUS_IK_FAIL,
        ];
    }

}
