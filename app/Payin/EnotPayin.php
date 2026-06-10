<?php


namespace App\Payin;


use App\MUser;
use App\PaymentsAPI\Enot;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * disabled
 * Class EnotPayin
 * @package App\Payin
 */
class EnotPayin extends Payin
{
    private $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "Enot";
        $this->type = self::TYPE_ENOT;
        $this->api = new Enot();
    }

    public function getPayMethods()
    {
        $methods = [];
        foreach($this->api->getPaymentMethods() as $enot_pymnt => $v){
            $methods[UserWallet::CURRENCY_RUB][$enot_pymnt] = "/img/enot/$enot_pymnt.png";
        }
        return $methods;
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction();

        $transaction->user_id = $this->user->id;
        $transaction->hall_id = $this->user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = 'RUB';
        $transaction->operation = 'Пополнение счета через Enot';
        $transaction->status_id = PayoutTransaction::STATUS_ENOT_PENDING;
        $transaction->save();

        $enot = new Enot();

        if (preg_match('#^.*_p2p$#', $method)) {
            $method = explode('_', $method)[0];
            $enot->setMode(Enot::MODE_P2P);
        }
        $url = $enot->genRedirectUrl($amount, $transaction->id, $method);
        Log::channel('pay')->info('enot url:'.$url);

        return ['url' => $url];
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_ENOT_PENDING,
            PayoutTransaction::STATUS_ENOT_SUCCESS,
            PayoutTransaction::STATUS_ENOT_FAIL,
        ];
    }
}
