<?php

namespace App;

use App\Helpers\Popup;
use Exception;

/**
 * @deprecated
 */
class UrlSigner
{
    public $secretKey;
    public $tokenLength = 10;

    /**
     * Initializes component and checks if [secretKey] is set.
     * UrlSigner constructor.
     * @param string $key
     * @throws Exception
     */
    public function __construct($key)
    {
        $this->secretKey = $key;
    }

    /**
     * Creates signed URL.
     * @param string $url URL to be signed.
     * @param array $params query parameters.
     * @param int|string $expiresAfter Expiration time of the URL in seconds.
     *        Can be set as string: '2h', '10m', '30s'.
     * @return string
     */
    public function sign($url, $expiresAfter = '', $params = [])
    {
        $protocol = '';
        preg_match('/http(s)?:\/\//', $url, $matches);
        if(count($matches) > 0){
            $protocol = $matches[0];
        }
        $parsedUrl = parse_url($url);
        if(array_key_exists('query', $parsedUrl)){
            $queryVars = explode('&', $parsedUrl['query']);
            foreach ($queryVars as $var) {
                $varPair = explode('=', $var);
                if(!array_key_exists($varPair[0], $params) && isset($varPair[1])){
                    $params[$varPair[0]] = $varPair[1];
                }
            }
        }

        $params['expires'] = is_numeric($expiresAfter) ? $expiresAfter : $this->makeTime($expiresAfter);
        unset($params['signature']);
        $queryArr = [];
        foreach ($params as $k => $v) {
            $queryArr[] = $k.'='.urlencode($v);
        }

        $query = count($queryArr) === 0 ? '' : '?'.implode('&', $queryArr);
        $fullUrl = $parsedUrl['host'].$parsedUrl['path'].$query;
        $signature = $this->createSignature($fullUrl);
        return $protocol.$fullUrl.'&signature='.base64_encode($signature);
    }

    /**
     * Parses string and returns quantity of seconds + current timestamp.
     * @param $timeString
     * @return int
     */
    private function makeTime($timeString)
    {
        $items = explode(' ', $timeString);
        $total = 0;
        foreach($items as $item){
            $num = intval(substr($item, 0, strlen($item) - 1));
            $last = substr($item, -1);

            if($last === 'm'){
                $total += $num * 60;
            }else if($last === 'h'){
                $total += $num * 360;
            }else if($last === 's'){
                $total += $num;
            }
        }
        return time() + $total;
    }

    /**
     * Creates URL signature.
     * @param $data
     * @return string
     */
    public function createSignature($data)
    {
        return hash('sha256', $this->secretKey.$data.$this->secretKey);
    }


    /**
     * @param $url
     * @return bool
     */
    public function validateUrl($url)
    {
        $url = preg_replace('/http(s)?:\/\//', '', $url);
        $expiresRegex = '/[&?]expires=\d+/i';
        Popup::message($url);
        preg_match($expiresRegex, $url, $matches);
        if(count($matches) === 0) return false;
        $pos = strrpos($url, '&');

        $signature = base64_decode(urldecode(str_replace('&signature=', '', substr($url, $pos))));
        $url = substr($url, 0, $pos);

        $validSignature = $this->createSignature($url);

        $expires = intval(str_replace(['&', '?', 'expires='], '', $matches[0]));
        return $signature === $validSignature && time() < $expires;
    }
}
