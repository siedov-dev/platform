<?php

namespace App\PaymentsAPI\Kvitum;
use App\Helpers\Hosts;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
/**
 * @see https://qostiqapiru.docs.apiary.io/
 * @docs https://feennexdocsapiaryio.docs.apiary.io/#/introduction/%D1%81%D1%86%D0%B5%D0%BD%D0%B0%D1%80%D0%B8%D0%B9-%D1%81%D0%B0%D0%BC%D0%BE%D1%81%D1%82%D0%BE%D1%8F%D1%82%D0%B5%D0%BB%D1%8C%D0%BD%D0%B0%D1%8F-%D0%B8%D0%BD%D1%82%D0%B5%D0%B3%D1%80%D0%B0%D1%86%D0%B8%D1%8F-host-to-host
 */
class Kvitum extends \App\Fillable
{
	/**
	 * @var int $shop_id Shop id on Piastrix system
	 */
	public $shop_id;
	/**
	 * @var string $key Secret key. Generated in Ptx. system
	 */
	public $key;

	/**
	 * 	Sort required params and gets HEX value
	 *	@see https://piastrix.docs.apiary.io/#reference/bill,-invoice-callback
	 *	@param array $param Key-value pairs
	 *	@param boolean $prepare Prepare values.
	 *	@return string
	 */
	public function sign($params, $prepare = false)
	{
		if ($prepare) {
			$params = $this->prepareSign($params);
		}
		ksort($params);
		$sign = implode(':', array_values($params)) . $this->key;
		return hash('sha256', $sign);
	}

	/**
	 *	Preapare params for generating HEX value
	 *	@param array $params Key-value pairs
	 *	@return array
	 */
	protected function prepareSign($params)
	{
		unset($params['sign']);

		return array_filter($params, function($value) {
			return !empty($value);
		});
	}

	/**
	 *	Check obtained sign with real sign
	 *	@param array $params Key-value pairs
	 *	@return bool
	 */
	public function checkSign($data)
	{
		if (!empty($data['sign'])) {
			return $this->sign($data, true) === $data['sign'];
		}

		return false;
	}

	/**
	 *	Generate invoice url
	 *	@param int|float $amount
	 *	@param int $currency Numeric ISO code. 840 for USD, 643 - RUB, 978 - EUR
	 *	@param string $shop_order_id String [0-255] symbols. Generate unique value
	 *	@param string $language Page language (ru, en)
	 *	@param array $optional Optional parameters that do not reinforce signature formation
	 *	@return string
	 */
	public function getInvoiceUrl($amount, $currency, $shop_order_id, $language = 'ru', $optional = [])
	{
		$params = [
			'amount' => $amount,
			'currency' => $currency,
			'shop_id' => $this->shop_id,
			'shop_order_id' => $shop_order_id,
		];

		$params['sign'] = $this->sign($params);
		$params['failed_url'] = action('PayController@fail');
		$params['success_url'] = action('PayController@success');

		$params = array_merge($params, $optional);

		return 'https://pay.feennex.com/' . $language . '/pay?' . http_build_query($params, '', '&');
	}

    public function createInvoice(float $amount, string $tr_id, string $customer_id, $method = 'card_uah', $add_data = [])
    {
        $url = "https://core.feennex.com";
        $client = new KvitumClient($this->shop_id, $this->key, 10, $url);
        $extra = [
            "customer_id" => $customer_id,
            "success_url" => route('pay.success'),
            "failed_url" => route('pay.fail'),
            "callback_url" => Hosts::getFinHost('UAH'). "/api/pay/kvitum",
        ];
        $extra = array_merge($extra, $add_data);

        return json_decode($client->invoice($amount, 980, $tr_id, $method, $extra), true);
    }

    //kvitum callback
    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info('Start processing Kvitum transaction with id ' . ($_POST['shop_order_id']??''));
        Log::channel('pay')->info('Kvitum raw ' . var_export($request->getContent(), true));
        Log::channel('pay')->info('Kvitum data ' . var_export($request->all(), true));

        $targetUrl = "http://127.0.0.1:23106/pay/kvitum";
        $response = Http::withHeaders($request->headers->all())
            ->withBody($request->getContent(), $request->header('Content-Type'))
            ->send($request->method(), $targetUrl);
        Log::channel('pay')->info('Kvitum response ' . var_export($response->body(), true));
        return response($response->body(), $response->status())->withHeaders($response->headers());

//        if (!isset($_POST['shop_order_id'])) return response('', 500);
//
//        // Find transaction.
//        /** @var PayoutTransaction $transaction */
//        $transaction = PayoutTransaction::where('id', $_POST['shop_order_id'])
//            ->whereIn('status_id', [
//                PayoutTransaction::STATUS_KVITUM_PENDING,
//                PayoutTransaction::STATUS_KVITUM_FAIL,
//            ])
//        ->first();
//
//        if ($transaction?->status_id === PayoutTransaction::STATUS_KVITUM_FAIL) Log::channel('pay')
//            ->info(__CLASS__." transaction was in fail state");
//
//        if ($transaction === null) {
//
//            $transaction = PayoutTransaction::where([
//                ['id', '=', $_POST['shop_order_id']],
//                ['status_id', '=', PayoutTransaction::STATUS_KVITUM_SUCCESS],
//            ])->first();
//
//            if ($transaction !== NULL) {
//                Log::channel('pay')->error('Transaction with id ' . $_POST['shop_order_id'] . ' is already completed. Response: ' . PHP_EOL . json_encode($_POST));
//                return response('OK', 200);
//            }
//
//            Log::channel('pay')->error('Transaction with id ' . $_POST['shop_order_id'] . ' is not found. Response: ' . PHP_EOL . json_encode($_POST));
//            return response('OK', 404);
//        }
//
//        // Find user which create this transaction.
//        /** @var MUser $user $user */
//        $user = MUser::where('id', $transaction->user_id)->first();
//
//        if ($user === null) {
//            Log::channel('pay')->error('User of the transaction is not found.');
//            return response('User not found.', 500);
//        }
//
//        $amount = $_POST['shop_amount'];
//
//        $transaction->fee = $_POST['shop_amount'] - $_POST['shop_refund'];
//        $transaction->total_sum = $_POST['shop_refund'];
//
//        if (!empty($request->post('ps_data'))) {
//            try {
//                $ps = json_decode($request->post('ps_data'), true);
//                if (!empty($ps['ps_payer_account'])) {
//                    $transaction->card_number = $ps['ps_payer_account'];
//                }
//            } catch (\Exception $e) {
//                Log::channel('pay')->error($e->getMessage());
//            }
//        }
//
//        $currency_code = $_POST['shop_currency'];
//
//        $currency = UserWallet::getIdByIntCode($currency_code);
//
//        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_KVITUM_SUCCESS, $currency);
//        return response('OK', 200);
    }
}
