<?php

namespace App\Payin;

use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\PayOnHost;
use App\PaymentsAPI\Repay\Repay;
use App\PaymentsAPI\XPayPro\XPayPro;
use App\PayoutTransaction;
use App\UserWallet;

class XPayProPayin extends Payin
{

    protected XPayPro $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = class_basename(XPayPro::class);
        $this->type = self::TYPE_XPAYPRO;
        $this->api = new XPayPro();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id): bool|array
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $user = MUser::getAuth();

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date(format: 'Y-m-d H:i:s');
        $transaction->sum = $amount;
        $transaction->currency = $user->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_XPAYPRO_PENDING;
        $transaction->method = $method;
        $transaction->save();

//        $widget_id = $this->api->listWidgets()['widgets'][0]['widget_id'] ?? '';
//        if ($widget_id === '') {
//            $this->_error = "No widget id";
//            return false;
//        }
        $widget_id = 'nPPEVfHhk8JpvaNmDAzcWy';

        if ($method === 'XPayPro.C2C') {
            $response = $this->api->createWidgetC2cOrder(
                tr_id: $transaction->getId(),
                client_id: $user->getId(),
                amount: $amount,
                widget_id: $widget_id,
            );
        } elseif ($method === 'XPayPro.P2P') {
            $response = $this->api->createWidgetP2pOrder(
                tr_id: $transaction->getId(),
                client_id: $user->getId(),
                amount: $amount,
                widget_id: $widget_id,
            );
        }

        if (!empty($response['order']['widget_url'])) {

            $transaction->o_tr_id = $response['order']['order_id'];
            $transaction->save();

            return ['url' => $response['order']['widget_url'], 'in_frame' => true];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_XPAYPRO_FAIL;
            $transaction->coment = "can't get widget url";
            $transaction->save();

            return false;
        }

    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_XPAYPRO_PENDING,
            PayoutTransaction::STATUS_XPAYPRO_SUCCESS,
            PayoutTransaction::STATUS_XPAYPRO_FAIL,
        ];
    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        if ($system->method_alias === 'XPayPro.P2P') {
            $po = UserMoneyStat::getStat(MUser::getAuth(), $currency_id, UserMoneyStat::TYPE_TOTAL);
            if (($po?->getPayoutCount() ?? 0) < 2  || $deposits_count < 5) return true;
        }
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

}
