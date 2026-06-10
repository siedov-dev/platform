<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GoldPartners SDK v1.0.0
 * Set namespace depending on your project.
 *
 * Usage example:
 *
 * // Create SDK instance.
 * $sdk = new GoldPartnersSdk(5, 'mySecretKey');
 *
 * // Create message to be sent.
 * $sdk->clientId = $clientId;
 * $msg = $sdk->createMessage(GoldPartnersSdk::EVENT_REGISTRATION);
 *
 * // Sending request to the server.
 * $sdk->send($msg);
 *
 * Please pay attention that some events can not be presented in the constants.
 * You can find list of all events on our website https://krakenpartners.net
 */

class GoldPartnersSdk
{
    const EVENT_REGISTRATION = 'reg';
    const EVENT_LOGIN        = 'login';
    const EVENT_MONEY_SPENT  = 'moneySpent';
    const EVENT_PAYOUT       = 'payout';
    const EVENT_VISIT        = 'visit';
    const EVENT_VISIT_ONLINE = 'visitOnline';
    const EVENT_DEPOSIT      = 'deposit';

//    const HOST_URL = "https://krakenpartners.net";
//    const HOST_URL = "https://partners.local";

    private static string $_host = "https://partners.local";
    private $_apiGateway; //= self::HOST_URL .'/api/track';
    private $_casinoId;
    private $_secretKey;

    // Parameters that will be sent if defined.
    public $clickId;
    public $clientId;
    public $clientName;
    public $time;
    public $amount;
    public $currency;
    public ?string $gameCid = null;
    public ?string $gameName = null;
    public ?string $source = null;
    public ?float $total_bet = null;
    public ?float $total_win = null;


    public function __construct(?int $casinoId = null , ?string $secretKey = null, string $apiGateway = '')
    {
        $this->_casinoId = $casinoId?? config('goldPartners.casinoId');
        $this->_secretKey = $secretKey?? config('goldPartners.secretKey');
        self::$_host = config('goldPartners.host');
        if ($apiGateway !== '') {
            $this->_apiGateway = $apiGateway;
        } else {
            $this->_apiGateway = self::$_host . '/api/track';
        }
    }


    /**
     * Creates signed message with given action and parameters.
     *
     * @param string $event
     * @return string
     */
    public function createMessage(string $event)
    {
        $data = ['event' => $event];
        $props = ['clickId', 'clientId', 'clientName', 'amount', 'currency', 'source', 'gameName', 'gameCid', 'total_bet', 'total_win',];
        foreach ($props as $prop) {
            if ($this->{$prop} !== null) {
                $data[$prop] = $this->{$prop};
            }
        }
        // Payouts must be sent as negative number.
        if ($event === self::EVENT_PAYOUT && isset($data['amount'])) {
            $data['amount'] = -abs($data['amount']);
        }
        $data['casinoId'] = $this->_casinoId;
        return $this->encodeMessage($data);
    }


    /**
     * Creates signed message from given data.
     *
     * @param array $data
     * @return string
     */
    private function encodeMessage(array $data) : string
    {
        $data['nonce'] = $this->generateNonce(8).time();
        $json = json_encode($data);
        return base64_encode($json).'|'.md5($json.$this->_secretKey);
    }


    /**
     * Parses given message and returns result.
     * Returns null if message signature is invalid.
     *
     * @param string $data
     * @return array|null
     */
    public function parseMessage(string $data)
    {
        $d = explode('|', $data);
        if (count($d) !== 2) return null;

        $json = base64_decode($d[0]);
        $signature = md5($json.$this->_secretKey);

        if ($signature === $d[1]) return json_decode($json, true);
        return null;
    }

    /**
     * Sends the given message.
     *
     * @param string $msg
     * @return bool
     */
    public function send(string $msg) : bool
    {
        Log::info("Sending data to GoldPartners ".$this->_apiGateway.var_export($msg, true));
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->_apiGateway);
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $msg);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($curl, CURLOPT_STDERR, $verbose);
        $res = curl_exec($curl);
        $success = !curl_errno($curl);
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
//
        Log::info('curl verbose');
        Log::info(htmlspecialchars($verboseLog));

        Log::info('GoldPartners resp' . $res);
        curl_close($curl);
        return $res === 'ok';
    }


    /**
     * Simply generates random string with given length.
     *
     * @param int $length
     * @return string
     */
    private function generateNonce(int $length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getClicksLink(int $click_id): ?int
    {
        return DB::table('papdb.clicks')->select('link_id')
            ->where('id', $click_id)->first()?->link_id;
    }

    public static function getClickId(int $link_id): int
    {
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );

        return intval(@file_get_contents(config('goldPartners.host') .'/'. App::getLocale() .'/track/' . $link_id . '?clickIdOnly=1', false, stream_context_create($arrContextOptions)));
    }
}
