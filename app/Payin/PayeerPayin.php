<?php


namespace App\Payin;


use App\Models\PaySystemsConfig;
use App\MUser;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Support\Facades\DB;

class PayeerPayin extends Payin
{
    protected int $shop_id = 793594675;
    protected string $key = "123";

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Payeer";
        $this->type = self::TYPE_PAYEER;

        $conf = PaySystemsConfig::getSystemConfig(self::TYPE_PIASTRIX);
        $this->shop_id = $conf['shop_id'] ?? 793594675;
        $this->key = $conf['key'] ?? '123';

    }

    public function getPayMethods()
    {
        return [
            UserWallet::CURRENCY_RUB => [
                'payeer' => '/img/payeer_logo.png',
            ],
        ];
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();
        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $this->currency_name;
        $transaction->operation = 'Пополнение счета через payeer';
        $transaction->status_id = 101;
        $transaction->save();

        $m_shop = $this->shop_id;
        $m_orderid = $transaction->id;
        $m_desc = base64_encode(__('wallet.refill_account'));
        $m_key = $this->key;

        $arHash = array(
            $m_shop,
            $m_orderid,
            number_format($amount , 2, '.', ''),
            $this->currency_name,
            $m_desc
        );

        $arHash[] = $m_key;
        $sign = strtoupper(hash('sha256', implode(':', $arHash)));

        return ['redirect_form' => view('pay.payeer-form', [
            'm_shop' => $m_shop,
            'm_orderid' => $m_orderid,
            'm_amount' => number_format($amount , 2, '.', ''),
            'm_curr' => $this->currency_name,
            'm_sign' => $sign,
            'm_desc' => $m_desc,
        ])->render()];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::PAID_IN_PAYEER,
        ];
    }
}
