<?php

namespace App\PaymentsAPI\SequoiaPay;

use App\ApplicationForPayment;
use App\Helpers\Hosts;
use App\Models\PaymentMethodData;
use App\Models\PaymentSystems;
use App\MUser;
use App\Payin\Payin;
use App\PayoutTransaction;
use App\UserWallet;
use App\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * @docs https://swagger.sequoiapay.co/#/callbacks/postPayOutCallback
 */
final class SequoiaPayApi
{
//    private string $payin_url = 'https://sandbox.sequoia-pay.com';
//    private string $payout_url = 'https://api-sandbox.sequoiapay.co';
    private string $payin_url = 'https://hqpas.co';
    private string $payout_url = 'https://api.sequoiapay.co';
    private string $key = '';
    private string $webhook_key = '';


    public function __construct(string $key = "", string $webhook_key = "")
    {
        $this->key = $key;
        $this->webhook_key = $webhook_key;
    }

    public function depositP2p(PayoutTransaction $tr, MUser $user, array $data = [])
    {
        $payload = [
            'order_id' => $tr->getId(),
            'amount' => $tr->getSum(),
            'payment_method' => 1,
            'back_to_merchant_success_url' => route('pay.success'),
            'back_to_merchant_url' => route('pay.close-tab'),
            'date' => now()->format('Y-m-d H:i:s'),
            'callback_url' => Hosts::getFinHost($tr->getCurrency()) . "/api/pay/sequoia/deposit",
            'currency' => $tr->getCurrency(),
            'merchant_user_id' => (string)$user->getId(),
            'merchant_user_ip' => $user->last_ip,
        ];
        if (!empty($data['add'])) {
            $payload = array_merge($payload, $data['add']);
        }
        $payload['token'] = md5('' . $payload['order_id'] . $this->key);
        return $this->query($this->payin_url . '/api/pay', 'POST', $payload);
    }

    public function depositP2pQr(PayoutTransaction $tr, MUser $user, array $data = [])
    {
        $payload = [
            'order_id' => $tr->getId(),
            'amount' => $tr->getSum(),
            'payment_method' => 2,
            'back_to_merchant_failed_url' => route('pay.fail'),
            'back_to_merchant_url' => route('pay.close-tab'),
            'callback_url' => Hosts::getFinHost($tr->getCurrency()) . "/api/pay/sequoia/deposit",
            'currency' => $tr->getCurrency(),
            'merchant_user_id' => (string)$user->getId(),
            'merchant_user_ip' => $user->last_ip,
        ];
        if (!empty($data['add'])) {
            $payload = array_merge($payload, $data['add']);
        }
        $payload['token'] = md5('' . $payload['order_id'] . $this->key);
        return $this->query($this->payin_url . '/api/pay', 'POST', $payload);
    }

    public function payout(ApplicationForPayment $tr, string $card_number, array $data = [])
    {
        $payload = [
            'id' => $tr->getId(),
            'amount' => $tr->suma,
            'date' => now()->format('Y-m-d H:i:s'),
            'callback_url' => Hosts::getFinHost($tr->getCurrency()) . "/api/pay/sequoia/payout",
            'currency' => Wallet::currencyName($tr->getCurrency()),
            'card_number' => $card_number,
        ];
        if (!empty($data['add'])) {
            $payload = array_merge($payload, $data['add']);
        }
        $payload['token'] = md5('' . $payload['id'] . $this->key);
        return $this->query($this->payout_url . '/api/transactions', 'POST', $payload);
    }

    public function payoutSbp(ApplicationForPayment $tr, string $card_number, array $data = [])
    {
        $payload = [
            'id' => $tr->getId(),
            'amount' => $tr->suma,
            'date' => now()->format('Y-m-d H:i:s'),
            'callback_url' => Hosts::getFinHost($tr->getCurrency()) . "/api/pay/sequoia/payout",
            'currency' => Wallet::currencyName($tr->getCurrency()),
            'card_number' => $card_number,
            'wallet_provider' => $data['bank'],
        ];
        if (!empty($data['add'])) {
            $payload = array_merge($payload, $data['add']);
        }
        $payload['token'] = md5('' . $payload['id'] . $this->key);
        return $this->query($this->payout_url . '/api/transactions', 'POST', $payload);
    }

    public function payoutStatus(string $tr_id)
    {
        $payload = [
            'id' => $tr_id,
        ];
        $payload['token'] = md5( $payload['id'] . $this->key);
        return $this->query($this->payout_url . '/api/transactions', 'GET', $payload);
    }

    public function sbpBanks(): array
    {
        return [
            'AB_ROSSIYA' => 'AB_ROSSIYA',
            'NOVOBANK' => 'NOVOBANK',
            'ENERGOBANK' => 'ENERGOBANK',
            'DALNEVOSTOCHNYJ_BANK' => 'DALNEVOSTOCHNYJ_BANK',
            'NBD_BANK' => 'NBD_BANK',
            'BANK_SIAB' => 'BANK_SIAB',
            'BZHF_BANK' => 'BZHF_BANK',
            'BANK_VENECZ' => 'BANK_VENECZ',
            'KIVI_BANK' => 'KIVI_BANK',
            'POCHTA_BANK' => 'POCHTA_BANK',
            'MOSKOMBANK' => 'MOSKOMBANK',
            'VLADBIZNESBANK' => 'VLADBIZNESBANK',
            'AKB_SLAVIYA' => 'AKB_SLAVIYA',
            'AVTOTORGBANK' => 'AVTOTORGBANK',
            'ROSBANK' => 'ROSBANK',
            'NACINVESTPROMBANK' => 'NACINVESTPROMBANK',
            'BANK_EKATERINBURG' => 'BANK_EKATERINBURG',
            'KB_ROSTFINANS' => 'KB_ROSTFINANS',
            'FFIN_BANK_CIFRA_BANK' => 'FFIN_BANK_CIFRA_BANK',
            'VUZ_BANK' => 'VUZ_BANK',
            'URALFINANS' => 'URALFINANS',
            'KB_STROJLESBANK' => 'KB_STROJLESBANK',
            'RUSUNIVERSALBANK' => 'RUSUNIVERSALBANK',
            'BANK_SINARA' => 'BANK_SINARA',
            'ENISEJSKIJ_OBEDINENNYJ_BANK' => 'ENISEJSKIJ_OBEDINENNYJ_BANK',
            'IK_BANK' => 'IK_BANK',
            'CHELYABINVESTBANK' => 'CHELYABINVESTBANK',
            'PROBANK' => 'PROBANK',
            'GUTA_BANK' => 'GUTA_BANK',
            'MS_BANK_RUS' => 'MS_BANK_RUS',
            'FORA_BANK' => 'FORA_BANK',
            'PRIMSOCZBANK' => 'PRIMSOCZBANK',
            'BANK_ZARECHE' => 'BANK_ZARECHE',
            'ARESBANK' => 'ARESBANK',
            'NACIONALNYJ_STANDART' => 'NACIONALNYJ_STANDART',
            'AK_BARS_BANK' => 'AK_BARS_BANK',
            'NKO_YUMANI' => 'NKO_YUMANI',
            'BANK_RUSSKIJ_STANDART' => 'BANK_RUSSKIJ_STANDART',
            'TRANSKAPITALBANK' => 'TRANSKAPITALBANK',
            'EKSPOBANK' => 'EKSPOBANK',
            'BANK_URALSIB' => 'BANK_URALSIB',
            'FINAM' => 'FINAM',
            'REALIST_BANK' => 'REALIST_BANK',
            'UGLEMETBANK' => 'UGLEMETBANK',
            'GAZPROMBANK' => 'GAZPROMBANK',
            'BANK_RAUND' => 'BANK_RAUND',
            'BANK_BKF' => 'BANK_BKF',
            'KB_SOLIDARNOST' => 'KB_SOLIDARNOST',
            'NOKSSBANK' => 'NOKSSBANK',
            'SOCIUM_BANK' => 'SOCIUM_BANK',
            'BANK_ALEKSANDROVSKIJ' => 'BANK_ALEKSANDROVSKIJ',
            'SEVERNYJ_NARODNYJ_BANK' => 'SEVERNYJ_NARODNYJ_BANK',
            'KREDIT_URAL_BANK' => 'KREDIT_URAL_BANK',
            'ROSSELXOZBANK' => 'ROSSELXOZBANK',
            'TOJOTA_BANK' => 'TOJOTA_BANK',
            'MSP_BANK' => 'MSP_BANK',
            'BANK_FINSERVIS' => 'BANK_FINSERVIS',
            'BANK_MOSKVA_SITI' => 'BANK_MOSKVA_SITI',
            'ALEF_BANK' => 'ALEF_BANK',
            'TRANSSTROJBANK' => 'TRANSSTROJBANK',
            'BYSTROBANK' => 'BYSTROBANK',
            'LEVOBEREZHNYJ' => 'LEVOBEREZHNYJ',
            'KUBAN_KREDIT' => 'KUBAN_KREDIT',
            'SINKO_BANK' => 'SINKO_BANK',
            'BANK_IPB' => 'BANK_IPB',
            'DOLINSK' => 'DOLINSK',
            'RNKB_BANK' => 'RNKB_BANK',
            'UBRIR' => 'UBRIR',
            'YUNIKREDIT_BANK' => 'YUNIKREDIT_BANK',
            'BANK_MIR_PRIVILEGIJ' => 'BANK_MIR_PRIVILEGIJ',
            'GAZENERGOBANK' => 'GAZENERGOBANK',
            'TOCHKA_FK_OTKRYTIE' => 'TOCHKA_FK_OTKRYTIE',
            'NS_BANK' => 'NS_BANK',
            'AKB_CENTROKREDIT' => 'AKB_CENTROKREDIT',
            'CHELINDBANK' => 'CHELINDBANK',
            'KB_NOVYJ_VEK' => 'KB_NOVYJ_VEK',
            'GARANT_INVEST_BANK' => 'GARANT_INVEST_BANK',
            'AKB_EVROFINANS_MOSNARBANK' => 'AKB_EVROFINANS_MOSNARBANK',
            'TATSOCZBANK' => 'TATSOCZBANK',
            'BANK_KREMLEVSKIJ' => 'BANK_KREMLEVSKIJ',
            'ELITA' => 'ELITA',
            'URAL_FD' => 'URAL_FD',
            'SITIBANK' => 'SITIBANK',
            'ABSOLYUT_BANK' => 'ABSOLYUT_BANK',
            'DZHEJ_END_TI_BANK' => 'DZHEJ_END_TI_BANK',
            'RESO_KREDIT' => 'RESO_KREDIT',
            'BANK_OBEDINENNYJ_KAPITAL' => 'BANK_OBEDINENNYJ_KAPITAL',
            'NK_BANK' => 'NK_BANK',
            'GORBANK' => 'GORBANK',
            'BANK_RAZVITIE_STOLICZA' => 'BANK_RAZVITIE_STOLICZA',
            'OTP_BANK' => 'OTP_BANK',
            'KOSHELEV_BANK' => 'KOSHELEV_BANK',
            'MEZHDUNARODNYJ_FINANSOVYJ_KLUB' => 'MEZHDUNARODNYJ_FINANSOVYJ_KLUB',
            'ZEMSKIJ_BANK' => 'ZEMSKIJ_BANK',
            'NIKO_BANK' => 'NIKO_BANK',
            'RUSNARBANK' => 'RUSNARBANK',
            'XAJS' => 'XAJS',
            'AKB_TENDER_BANK' => 'AKB_TENDER_BANK',
            'NORVIK_BANK' => 'NORVIK_BANK',
            'OTKRYTIE' => 'OTKRYTIE',
            'ES_BI_AJ_BANK' => 'ES_BI_AJ_BANK',
            'BKS_BANK' => 'BKS_BANK',
            'SANKT_PETERBURG' => 'SANKT_PETERBURG',
            'FORSHTADT' => 'FORSHTADT',
            'PERVYJ_INVESTICIONNYJ_BANK' => 'PERVYJ_INVESTICIONNYJ_BANK',
            'BANK_ORENBURG' => 'BANK_ORENBURG',
            'MORSKOJ_BANK' => 'MORSKOJ_BANK',
            'GENBANK' => 'GENBANK',
            'BANK_ITURUP' => 'BANK_ITURUP',
            'VTB' => 'VTB',
            'OZON_BANK' => 'OZON_BANK',
            'TOLYATTIXIMBANK' => 'TOLYATTIXIMBANK',
            'BANK_VBRR' => 'BANK_VBRR',
            'XOUM_KREDIT_BANK_XOUM_BANK' => 'XOUM_KREDIT_BANK_XOUM_BANK',
            'BELGORODSOCZBANK' => 'BELGORODSOCZBANK',
            'LOKO_BANK' => 'LOKO_BANK',
            'ROSDORBANK' => 'ROSDORBANK',
            'TOMSKPROMSTROJBANK' => 'TOMSKPROMSTROJBANK',
            'AKIBANK' => 'AKIBANK',
            'TAVRICHESKIJ_BANK' => 'TAVRICHESKIJ_BANK',
            'YUG_INVESTBANK' => 'YUG_INVESTBANK',
            'KREDIT_EVROPA_BANK_ROSSIYA' => 'KREDIT_EVROPA_BANK_ROSSIYA',
            'BANK_ZENIT' => 'BANK_ZENIT',
            'NOVIKOMBANK' => 'NOVIKOMBANK',
            'PROMSVYAZBANK' => 'PROMSVYAZBANK',
            'AZIATSKO_TIXOOKEANSKIJ_BANK' => 'AZIATSKO_TIXOOKEANSKIJ_BANK',
            'AKB_DERZHAVA' => 'AKB_DERZHAVA',
            'MODULBANK' => 'MODULBANK',
            'GAZTRANSBANK' => 'GAZTRANSBANK',
            'BANK_INTEZA' => 'BANK_INTEZA',
            'TIMER_BANK' => 'TIMER_BANK',
            'SDM_BANK' => 'SDM_BANK',
            'RENESSANS_KREDIT' => 'RENESSANS_KREDIT',
            'AKB_PRIMORE' => 'AKB_PRIMORE',
            'MOSKOMMERCZBANK' => 'MOSKOMMERCZBANK',
            'DATABANK' => 'DATABANK',
            'METALLINVESTBANK' => 'METALLINVESTBANK',
            'SNGB' => 'SNGB',
            'MKB' => 'MKB',
            'BANK_SARATOV' => 'BANK_SARATOV',
            'VNESHFINBANK' => 'VNESHFINBANK',
            'BANK_SNEZHINSKIJ' => 'BANK_SNEZHINSKIJ',
            'ELPLAT' => 'ELPLAT',
            'BANK_AKCEPT' => 'BANK_AKCEPT',
            'ISHBANK' => 'ISHBANK',
            'BANK_KAZANI' => 'BANK_KAZANI',
            'CENTR_INVEST' => 'CENTR_INVEST',
            'MTS_BANK' => 'MTS_BANK',
            'SOLID_BANK' => 'SOLID_BANK',
            'METKOMBANK' => 'METKOMBANK',
            'SMP_BANK' => 'SMP_BANK',
            'BANK_VOLOGZHANIN' => 'BANK_VOLOGZHANIN',
            'AVANGARD' => 'AVANGARD',
            'YANDEKS_BANK' => 'YANDEKS_BANK',
            'TINKOFF' => 'TINKOFF',
            'KROKUS_BANK' => 'KROKUS_BANK',
            'BANK_AGROROS' => 'BANK_AGROROS',
            'KB_YUNISTRIM' => 'KB_YUNISTRIM',
            'ALFA_BANK' => 'ALFA_BANK',
            'DRAJV_KLIK_BANK' => 'DRAJV_KLIK_BANK',
            'INBANK' => 'INBANK',
            'ALMAZERGIENBANK' => 'ALMAZERGIENBANK',
            'AVTOGRADBANK' => 'AVTOGRADBANK',
            'URALPROMBANK' => 'URALPROMBANK',
            'TOCHKA_BANK' => 'TOCHKA_BANK',
            'KUZNECZKBIZNESBANK' => 'KUZNECZKBIZNESBANK',
            'SIBSOCZBANK' => 'SIBSOCZBANK',
            'RAJFFAJZEN_BANK' => 'RAJFFAJZEN_BANK',
            'BANK_MBA_MOSKVA' => 'BANK_MBA_MOSKVA',
            'BANK_DOM_RF' => 'BANK_DOM_RF',
            'ENERGOTRANSBANK' => 'ENERGOTRANSBANK',
            'BANK_AVERS' => 'BANK_AVERS',
            'SOVKOMBANK' => 'SOVKOMBANK',
            'KB_XLYNOV' => 'KB_XLYNOV',
            'BANK_BBR' => 'BANK_BBR',
            'SBERBANK' => 'SBERBANK',
            'XAKASSKIJ_MUNICIPALNYJ_BANK' => 'XAKASSKIJ_MUNICIPALNYJ_BANK',
            'KUBANTORGBANK' => 'KUBANTORGBANK',
            'SEVERGAZBANK' => 'SEVERGAZBANK',
            'BANK_PSKB' => 'BANK_PSKB',
            'LANTA_BANK' => 'LANTA_BANK',
            'PRIO_VNESHTORGBANK' => 'PRIO_VNESHTORGBANK',
            'INGOSSTRAX_BANK' => 'INGOSSTRAX_BANK',
            'VAJLDBERRIZ_BANK' => 'VAJLDBERRIZ_BANK',
            'TKB_BANK' => 'TKB_BANK',
            'BANK_BZHF' => 'BANK_BZHF',
            'BLANK_BANK' => 'BLANK_BANK',
            'CZMRBANK' => 'CZMRBANK',
            'DBO_FAKTURA' => 'DBO_FAKTURA',
            'FILIAL_OTKRYTIE_BM_BANK' => 'FILIAL_OTKRYTIE_BM_BANK',
            'FINSTAR_BANK' => 'FINSTAR_BANK',
            'IKBR_YARINTERBANK' => 'IKBR_YARINTERBANK',
            'KB_AGROPROMKREDIT' => 'KB_AGROPROMKREDIT',
            'KB_POJDYOM' => 'KB_POJDYOM',
            'KB_URAL_FD' => 'KB_URAL_FD',
            'KHAJS_BANK' => 'KHAJS_BANK',
            'MB_BANK' => 'MB_BANK',
            'MTS_DENGI_EKSI_BANK' => 'MTS_DENGI_EKSI_BANK',
            'MP_BANK' => 'MP_BANK',
            'NKO_MOBILNAYA_KARTA' => 'NKO_MOBILNAYA_KARTA',
            'PLAJT' => 'PLAJT',
            'PERVYJ_DORTRANSBANK' => 'PERVYJ_DORTRANSBANK',
            'RNKO_PLATEZHNYJ_KONSTRUKTOR' => 'RNKO_PLATEZHNYJ_KONSTRUKTOR',
            'STAVROPOLPROMSTROJBANK' => 'STAVROPOLPROMSTROJBANK',
            'ZHIVAGO_BANK' => 'ZHIVAGO_BANK',
            'ROCKETBANK' => 'ROCKETBANK',
        ];
    }


    public function walletProviders(string $currency)
    {
        $timestamp = time();
        $payload = [
            'timestamp' => $timestamp,
        ];
        $payload['token'] = md5('' . $timestamp . $this->key);
        return $this->query($this->payin_url . "/api/$currency/wallet-provider-list", 'POST', $payload);
    }

    public static function depositCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . 'callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info('signature:'.$request->header('Signature'));

        $data = $request->all();

        if (!isset($data['order_id'])) {
            Log::channel('pay')->error('order_id field not set');
            return response('', 500);
        }

        Log::channel('pay')->info('Start processing Sequoia transaction with id ' . $data['order_id']);

        /** @var PayoutTransaction $transaction */
        $transaction = PayoutTransaction::where('id', $data['order_id'])
            ->whereIn('status_id', [
                PayoutTransaction::STATUS_SEQUOIA_PENDING,
                PayoutTransaction::STATUS_SEQUOIA_FAIL,
            ])
            ->first();

        if ($transaction === null) {
            if (PayoutTransaction::where('id', $data['order_id'])
                    ->where('status_id', PayoutTransaction::STATUS_SEQUOIA_SUCCESS)
                    ->first() !== null) {
                Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is already complete found. Request: ' . PHP_EOL . var_export($request->all(), true));
                return response('ok', 200);
            }
            Log::channel('pay')->error('Transaction with id ' . $data['order_id'] . ' is not found. Request: ' . PHP_EOL . var_export($request->all(), true));
            return response('transaction not found', 404);
        }

        // Find user which create this transaction.
        /** @var MUser $user $user */
        $user = MUser::where('id', $transaction->user_id)->first();

        if ($user === null) {
            Log::channel('pay')->error('User of the transaction is not found.');
            return response('user not found', 500);
        }

        if ($data['status'] === 'expired') {
            Log::channel('pay')->error('status:' . $data['status']);
            $transaction->status_id = PayoutTransaction::STATUS_SEQUOIA_FAIL;
            $transaction->save();
            return response('ok', 200);
        }

        $p_method = PaymentSystems::where('method_alias', $transaction->method)
            ->where('type', PaymentSystems::TYPE_PAYIN)
            ->where('currency_id', Wallet::getCurrencyCode($transaction->currency))
            ->first();
        if ($p_method === null) {
            Log::channel('pay')->error('Payment method not found:' . $transaction->method);
        } else {
            $method_data = PaymentMethodData::getMethodData($p_method->id);
            $hook_key = $method_data['Webhook']?? '';
            $sign = hash_hmac('sha256', $request->getContent(), $hook_key);
            if ($sign !== $request->header('Signature')) {
                Log::channel('pay')->error('Wrong signature: currenct sign:' . $sign . ' header sign:' . $request->header('Signature'));
                return response('wrong signature', 403);
            }
        }

        if ($data['status'] !== 'success') {
            return 'ok';
        }

        $amount = $data['new_amount'] ?? $data['amount'];

        $currency = UserWallet::getCurrencyCode(strtoupper($data['currency']));
        if ($transaction->method === 'Sequoia.RUB:sbp') {
            $transaction->fee = $amount * 0.14;
        } elseif ($currency == Wallet::CURRENCY_RUB) {
            $transaction->fee = $amount * 0.115;
        } elseif ($currency == Wallet::CURRENCY_KZT) {
            $transaction->fee = $amount * 0.075;
        } else {
            $transaction->fee = 0;
        }
        $transaction->card_number = $data['card_number'] ?? '';

        Payin::completeTransaction($user, $transaction, $amount, PayoutTransaction::STATUS_SEQUOIA_SUCCESS, $currency);

        return response('ok', 200);
    }

    public static function payoutCallback(Request $request)
    {
        Log::channel('pay')->info(__CLASS__ . 'callback');
        Log::channel('pay')->info(var_export($request->all(), true));
        Log::channel('pay')->info('signature:'.$request->header('Signature'));
    }

    private function query($url, $httpMethod, array $data = [])
    {
        $ch = curl_init();

        if (strtolower($httpMethod) === 'post') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            if (!empty($data)) {
                $url .= '?' . http_build_query($data);
            }
        }

        Log::channel('pay')->info(__CLASS__ . 'request: ' . $url);
        Log::channel('pay')->info(var_export($data, true));

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $resp_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::channel('pay')->info('curl verbose');
        Log::channel('pay')->info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::channel('pay')->error($err);
        }

        Log::channel('pay')->info(__CLASS__ . 'resp: ');
        Log::channel('pay')->info('code:' . $resp_code);
        Log::channel('pay')->info($text);
        Log::channel('pay')->info(var_export($resp, true));
        return $resp;
    }
}
