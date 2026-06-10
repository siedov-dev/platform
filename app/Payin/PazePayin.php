<?php


namespace App\Payin;


use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Paze\PazeApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PazePayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Paze";
        $this->type = self::TYPE_PAZE;
        $this->api = new PazeApi();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        if (empty($data['email']) || !preg_match('/^([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})$/', $data['email'])) {
            $this->_error = __('wallet.email_required');
            return false;
        }

        if (!preg_match('#^\d{10}$#', $data['phone'])) {
            $this->_error = __('wallet.wrong_10_phone');
            return false;
        }

        return parent::canProceed($amount, $method, $data, $method_id);
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $method = explode('.', $method)[1];
        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $currency = $this->user->currency;
        $data['phone'] = '91 ' . $data['phone'];

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение счета через Paze';
        $transaction->status_id = PayoutTransaction::STATUS_PAZE_PENDING;
        $transaction->save();

        $resp = $this->api->createPayment($transaction->id, PazeApi::TYPE_DEPOSIT, $transaction->sum, $data, $transaction->currency, $method);

        if (!empty($resp) && $resp['status'] == 200 && !empty($resp['result']['redirectUrl'])) {
            return ['url' => $resp['result']['redirectUrl']];
        }

        $this->_error = $resp['message'] ?? 'system error';
        return false;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $phone = '';
        if (preg_match('#^(\+?)(91)(\d+)$#', $this->user->phone, $match)) {
            $phone = $match[3];
        }

        return view('pay.requisites.payin.paze', [
            'email' => $this->getLastRequisite('email') ?? $this->user->email,
            'phone' => $this->getLastRequisite('phone') ?? $phone,
            'firstName' => $this->getLastRequisite('firstName') ?? $this->user->name ?? '',
            'lastName' => $this->getLastRequisite('lastName') ?? $this->user->surname ?? '',
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYLAMA_PENDING,
            PayoutTransaction::STATUS_PAYLAMA_SUCCESS,
            PayoutTransaction::STATUS_PAYLAMA_FAIL,
        ];
    }
}
