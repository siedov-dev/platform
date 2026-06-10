<?php

namespace App\Payin;

use App\Helpers\Device;
use App\Models\PaymentSystemGroup;
use App\Models\PaymentSystems;
use App\Models\UserMoneyStat;
use App\MUser;
use App\PaymentsAPI\PayOnHost;
use App\PaymentsAPI\SkyPay;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayOnHostPayin extends Payin
{

    protected PayOnHost $api;

    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "PayOnHost";
        $this->type = self::TYPE_PAYONHOST;
        $this->api = new PayOnHost();
    }

    public function process($amount, $method, array $data, ?PayoutTransaction $transaction, int $method_id)
    {
        if ($transaction === null) $transaction = new PayoutTransaction;

        $user_id = Auth::id();
        /** @var MUser $user */
        $user = MUser::find($user_id);

        $add_fields = [];

        $ps = PaymentSystems::where('method_alias', $method)->firstOrFail();

        if ($ps->currency_id == UserWallet::CURRENCY_KZT && $ps->system_group == PaymentSystemGroup::Bank) {
            if ($ps->bank_alias == 'PayOnHost.KZT_STD' && ($user->getDepositsCount() < 3 || $user->getPayoutsCount() < 2)) {
                $ps = PaymentSystems::where('bank_alias', 'PayOnHost.KZT_FTD')->firstOrFail();
            }

            $pd = $user->personalData();

            $add_fields = [
                'first_name' => $pd->name,
                'last_name' => $pd->surname,
            ];
        }

        if ($method == 'PayOnHost.27533:32033:12954' || $method == 'PayOnHost.27533:32033:12955') {
            $add_fields = [
                'first_name' => $data['first_name']?? '',
                'last_name' => $data['last_name']?? '',
            ];
        }

        if ($method == 'PayOnHost.27533:33155:13315' || $method == 'PayOnHost.27533:33155:13316') {
            $add_fields = [
                'first_name' => $data['first_name']?? '',
                'last_name' => $data['last_name']?? '',
            ];
        }

        $currency = $this->user->currency;

        $transaction->user_id = $user->id;
        $transaction->hall_id = $user->hall_id;
        $transaction->date = date("Y-m-d H:i:s");
        $transaction->sum = $amount;
        $transaction->currency = $user->getWallet($currency)->getCurrencyName();
        $transaction->operation = class_basename(Payin::class) . ' ' . $this->name;
        $transaction->status_id = PayoutTransaction::STATUS_PAYONHOST_PENDING;
        $transaction->method = $ps->method_alias;
        $transaction->save();

        $response = $this->api->in(
            transaction: $transaction,
            add_fields: $add_fields
        );

        Log::channel('pay')->info(__CLASS__.' resp:'. var_export($response, true));

        /*$response = (object)["error" => [
                                "code" => 0,
                                "title" => "Success"
                            ],
                            "response" => (object)[
                                    "result" => (object)[
                                        "pay_url" => "https://pay........"
                                    ],
                                "id" => 780750896,
                                "status" => 10,
                                "external_transaction_id" => "1759212_1657879365",
                                "external_order_id" => "1759212",
                                "external_customer_id" => "1759212",
                                "is_test" => false,
                                "account_id" => 123,
                                "wallet_id" => 123,
                                "service_id" => 123,
                                "point_id" => 111,
                                "amount" => 500,
                                "amount_currency" => "UAH"
                            ]
                        ];*/

        if (!empty($response->response->result->pay_url)) {

            $transaction->o_tr_id = $response->response->id;
            $transaction->save();

            return ['url' => $response->response->result->pay_url];
        } else {
            $this->_error = 'System error. Please try again later.';

            $transaction->status_id = PayoutTransaction::STATUS_PAYONHOST_FAIL;;
            $transaction->coment = $response?->error?->title ?? $this->_error;
            $transaction->save();

            return false;
        }

    }

    public function isHidden(int $currency_id, int $deposits_count, float $deposits_sum, ?PaymentSystems $system = null): bool
    {
        //Google Pay
        if ($system->method_alias == 'PayOnHost.27533:31781:12877') {
            $device_info = Device::current();
            if ($device_info->platform() === 'iOS') return true;
            if ($device_info->browser() === 'Safari') return true;
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($user->created_at > now()->subMonth() || $po->getDepositCount() < 3 || $po->getPayoutCount() < 1) return true;
        }
        //Apple Pay
        if ($system->method_alias == 'PayOnHost.27533:31781:12876') {
            $device_info = Device::current();
            if ($device_info->deviceType() === 'Desktop' && $device_info->browser() !== 'Safari') {
                return true;
            }
            if ($device_info->deviceType() !== 'Desktop' && $device_info->platform() !== 'iOS') {
                return true;
            }
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($user->created_at > now()->subMonth() || $po->getDepositCount() < 3 || $po->getPayoutCount() < 1) return true;
        }
        // ACQ KZT SP STD
        if ($system->method_alias == 'PayOnHost.27533:32033:12954') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if ($user->created_at > now()->subMonths(2) || $po->getDepositCount() < 3 || $po->getPayoutCount() < 1) return true;
        }
        // MC KZT STD
        if ($system->method_alias == 'PayOnHost.27533:33155:13316') {
            $user = $this->user;
            $po = UserMoneyStat::getStat($user, $currency_id, UserMoneyStat::TYPE_TOTAL);
            if (!$user->isVerified() || $po->getDepositCount() < 3 || $po->getPayoutCount() < 2) return true;
        }
        return parent::isHidden($currency_id, $deposits_count, $deposits_sum, $system);
    }

    public function getStatusCodes(): array
    {
        return [
            PayoutTransaction::STATUS_PAYONHOST_PENDING,
            PayoutTransaction::STATUS_PAYONHOST_SUCCESS,
            PayoutTransaction::STATUS_PAYONHOST_FAIL,
        ];
    }

    public function getFormRequisites($method, ?PaymentSystems $pay_method): string
    {
        if ($method == 'PayOnHost.27533:32033:12954' || $method == 'PayOnHost.27533:32033:12955') {
            return view('pay.requisites.payin.first-last-name', ['method' => $method])->render();
        }
        if ($method == 'PayOnHost.27533:33155:13315' || $method == 'PayOnHost.27533:33155:13316') {
            return view('pay.requisites.payin.first-last-name', ['method' => $method])->render();
        }
        return parent::getFormRequisites($method, $pay_method);
    }

}
