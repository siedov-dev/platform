<?php

namespace App\Payin;

use App\Models\PaymentSystems;
use App\MUser;
use App\PaymentsAPI\Alphapo\AlphapoApi;
use App\PayoutTransaction;
use Illuminate\Support\Facades\Auth;

class AlphapoPayin extends Payin
{
    protected $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "CryptoCloud";
        $this->type = self::TYPE_CRYPTOCLOUD;
        $this->api  = new AlphapoApi();
    }

    public function canProceed(float $amount, string $method, array $data, int $method_id): bool
    {
        return true;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $method = explode('.', $method)[1];
        $currency = $method;
        $wallet_url = $this->user->getUserData()->getValue($method);
        if (empty($wallet_url)) {
            $data = $this->api->getAddress($this->user->id, $currency);
            if (empty($data['data']['address'])) throw new \Exception('can`t get wallet address');
            $wallet_url = $data['data']['address'];
            $this->user->getUserData()->setValue($method, $wallet_url);
        }
        return ['url' => action('PayController@showCryptoPayForm', ['wallet' => $wallet_url]), 'in_frame' => true];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        $method = explode('.', $method)[1];
        $currency = $method;
        $wallet_url = $this->user->getUserData()->getValue($method);
        if (empty($wallet_url)) {
            $data = $this->api->getAddress($this->user->id, $currency);
            if (empty($data['data']['address'])) throw new \Exception('can`t get wallet address');
            $wallet_url = $data['data']['address'];
            $this->user->getUserData()->setValue($method, $wallet_url);
        }
        return view('pay.requisites.payin.Alphapo', ['wallet' => $wallet_url, 'method' => $method,]);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ALPHAPO_PENDING,
            PayoutTransaction::STATUS_ALPHAPO_SUCCESS,
            PayoutTransaction::STATUS_ALPHAPO_FAIL,
        ];
    }
}
