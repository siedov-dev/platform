<?php

namespace App\PaymentsAPI;
use Illuminate\Support\Facades\Log;
/**
 * docs https://piastrix.docs.apiary.io/#introduction/pay/pay
 */
class Piastrix extends \App\Fillable
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
	//	$params['failed_url'] = $url;
	//	$params['success_url'] = $url_1;

		$params = array_merge($params, $optional);

		return 'https://pay.piastrix.com/' . $language . '/pay?' . http_build_query($params, '', '&');
	}
}
