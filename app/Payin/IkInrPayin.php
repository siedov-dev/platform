<?php


namespace App\Payin;


use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\InterKassa\IkInr;
use App\PaymentsAPI\InterKassa\InterKassa;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IkInrPayin extends Payin
{
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "InterKassaInr";
        $this->type = self::TYPE_IKINR;
        $this->min_sum = 50;
    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_INR => [
                'IkInr.UPI' => '/img/paysystem/UPI.png',
                'IkInr.PhonePe' => '/img/paysystem/upi/phonepe.png',
                'IkInr.MobiKwik' => '/img/paysystem/upi/mobikwik.png',
                'IkInr.Bank' => '/img/paysystem/netbank.png',
                'IkInr.Visa' => '/img/paysystem/obmenka/visacard_1.png',
                'IkInr.airtelmoney' => '/img/paysystem/upi/airtelmoney.png',
//                'IkInr.Wallet' => '/img/paysystem/wallet.png',
                'IkInr.paytm' => '/img/paysystem/upi/paytm.png',
                'IkInr.OlaMoney' => '/img/paysystem/upi/olamoney.png',
                'IkInr.Reliance' => '/img/paysystem/upi/reliance.png',
                'IkInr.freecharge' => '/img/paysystem/upi/freecharge.png',
            ],
        ];
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
        $transaction->operation = 'Пополнение счета через InterKassa';
        $transaction->status_id = PayoutTransaction::STATUS_IK_PENDING;
        $transaction->save();

        $api = new IkInr();
        $api->setCheckout($user_id);
        if ($method === 'IkInr.Visa') {
            $form = $api->redirectToSci($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName());
            Log::channel('pay')->info('form:' . var_export($form, true));
            return ['redirect_form' => $form];
            $resp = $api->redirectToSci($transaction->id, $amount, $user->getWallet($currency)->getCurrencyName());
            return ['url' => $resp];
        }
        $form = $api->getPayinForm($transaction->id, $amount, $method, $data, $user);
        Log::channel('pay')->info('form:' . var_export($form, true));
        return ['redirect_form' => $form];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method === 'IkInr.Visa') return parent::getFormRequisites($method, $pay_method);

        if ($method === 'IkInr.UPI') {
            return view('pay.requisites.payin.IkInr-UPI', ['method' => $method,])->render();
        }

        if ($method === 'IkInr.Bank') {
            return view('pay.requisites.payin.IkInr-Bank', ['method' => $method,])->render();
        }

        if ($method === 'IkInr.Wallet') {
            return view('pay.requisites.payin.IkInr-Wallet', ['method' => $method,])->render();
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

        $field = '<input type="hidden" name="mop" value="' . $mop . '">'.parent::getFormRequisites($method, $pay_method);
        return $field;
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
