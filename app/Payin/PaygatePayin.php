<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\PayGate10\PayGate10;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class PaygatePayin extends Payin
{
    protected PayGate10 $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayGate";
        $this->type = self::TYPE_PAYGATE;
        $this->api = new PayGate10();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        if (empty($data['email']) || !preg_match('/^([a-zA-Z0-9_\.-]+)@([a-zA-Z0-9_\.-]+)\.([a-zA-Z\.]{2,6})$/', $data['email'])) {
            $this->_error = __('wallet.email_required');
            return false;
        }

        if (!preg_match('#^\d{10}$#', $data['mobile'])) {
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

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = 'Пополнение PayGate';
        $transaction->status_id = PayoutTransaction::STATUS_PAYGATE_PENDING;
        $transaction->save();

        $resp = $this->api->deposit($transaction->id, $transaction->sum, $method, $data, $transaction->currency);

        if (!empty($resp) && $resp['status'] !== 'DECLINED' && !empty($resp['paymenturl'])) {
            return ['url' => $resp['paymenturl']];
        }

        $this->_error = $resp['message'] ?? 'system error';
        return false;
    }

    public function getRequestRequisites(string $method, array $data, int $method_id): array
    {
        //clear all non-numeric characters
        $data['mobile'] = preg_replace('#[^+\d]#', '', $data['mobile']);

        if (preg_match('#^(\+?)(91)?(\d{10})$#', $data['mobile'], $match)) {
            $data['mobile'] = $match[3];
        }

        if (preg_match('#^([a-zA-Z]{2}).*$#', $data['state'], $match)) {
            $data['state'] = $match[1];
        }

        return $data;
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $phone = preg_replace('#[^+\d]#', '', $this->user->phone ?? '');
        if (preg_match('#^(\+?)(91)(\d{10})$#', $this->user->phone, $match)) {
            $phone = $match[3];
        }

        return view('pay.requisites.payin.paygate', [
            'email' => $this->getLastRequisite('email') ?? $this->user->email,
            'mobile' => $this->getLastRequisite('phone') ?? $phone,
            'firstname' => $this->getLastRequisite('firstname') ?? $this->user->name ?? '',
            'lastname' => $this->getLastRequisite('lastname') ?? $this->user->surname ?? '',
            'address' => $this->getLastRequisite('address') ?? $this->user->address ?? '',
            'city' => $this->getLastRequisite('city') ?? $this->user->city ?? '',
            'state' => $this->getLastRequisite('state') ?? $this->user->addres ?? '',
            'postcode' => $this->getLastRequisite('postcode') ?? $this->user->post_index ?? '',
            'method' => $method,
        ]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYGATE_PENDING,
            PayoutTransaction::STATUS_PAYGATE_SUCCESS,
            PayoutTransaction::STATUS_PAYGATE_FAIL,
        ];
    }
}
