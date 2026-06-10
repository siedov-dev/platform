<?php

namespace App\Helpers;

use App\Models\BonusCurrencyConfig;
use App\Models\ExchangeRateCache;
use App\UserWallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use phpDocumentor\Reflection\Types\Collection;

class Currency
{
    const USD = 'USD';
    const EUR = 'EUR';
    const RUB = 'RUB';
    const UAH = 'UAH';
    const INR = 'INR';
    const USDT = 'USDT';
    const BTC = 'BTC';
    const mBTC = 'mBTC';
    const KZT = 'KZT';
    const UZS = 'UZS';

    public static array $crypto = [
        'USDT',
        'BTC',
        'mBTC',
    ];

    public static array $crypto_id = [
        UserWallet::CURRENCY_USDT,
        UserWallet::CURRENCY_mBTC,
    ];

    private static ?array $_courses = null;
    private static ?array $_cr_courses = null;

    /**
     * Converts given amount of money from one currency to another one.
     * Example:
     * $sumInUah = Currency::convert(100, Currency::RUB, Currency::UAH);
     * @param float $amount
     * @param string $from
     * @param string $to
     * @return float
     */
    public static function convert(float $amount, string $from, string $to = self::RUB): float
    {
        $from = strtoupper(trim($from));
        $to = strtoupper(trim($to));
        if ($from === 'USDT') $from = 'USD';
        if ($to === 'USDT') $to = 'USD';

        if ($from === 'mBTC' || $to === 'mBTC') {
            self::getCryptoCourses();
            self::$_cr_courses['mBTC'] = self::$_cr_courses['BTC'] / 1000;
        }
        //if from is cryptocurrency convert ro usd
        if (array_key_exists($from, self::getCryptoCourses())) {
            $amount = $amount * self::getCryptoCourses()[$from];
            $from = self::USD;
        }
        if (array_key_exists($to, self::getCryptoCourses())) {
            if ($from !== self::USD) {
                $amount = self::convert($amount, $from, self::USD);
            }
            return $amount / self::getCryptoCourses()[$to];
        }

        $amountInRub = $amount * self::getCourses()[$from];
        if ($to === self::RUB) {
            return $amountInRub;
        }
        return $amountInRub / self::getCourses()[$to];
    }

    /**
     * Returns array with courses multipliers.
     * The key is currency and value is multiplier.
     * All courses are based on RUB.
     * @return array
     */
    public static function getCourses(): array
    {
        if (self::$_courses === null) {
            $exchange_rate_cache = ExchangeRateCache::where('is_crypto', false)->latest('rate_date')->first();
            if ($exchange_rate_cache) {
                self::$_courses = json_decode($exchange_rate_cache->data, true);
            }
        }
        return self::$_courses;
    }

    public static function getCryptoCourses(): array
    {
        if (self::$_cr_courses === null) {
            $exchange_rate_cache = ExchangeRateCache::where('is_crypto', true)->latest('rate_date')->first();
            if ($exchange_rate_cache) {
                self::$_cr_courses = json_decode($exchange_rate_cache->data, true);
            }
        }
        return self::$_cr_courses;
    }

    public static function cacheData()
    {
        $date = date("d/m/Y"); // Сегодняшняя дата в необходимом формате
        $link = "https://www.cbr.ru/scripts/XML_daily.asp?date_req=$date"; // Ссылка на XML-файл с курсами валют
//            @$content = file_get_contents($link); // Скачиваем содержимое страницы
        //disable ssl
        @$content = file_get_contents($link, false, stream_context_create(['http' => ['timeout' => 2], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        if ($content) {
            @$xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);
            $json = json_encode($xml);
            $array = json_decode($json, true);
            $values = $array['Valute'];
            $data = [];
            foreach ($values as $val) {
                $data[strtoupper($val['CharCode'])] = str_replace(',', '.', $val['Value']) / $val['Nominal'];
            }
            $data[self::RUB] = 1;

            $exchange_rate_cache = ExchangeRateCache::where('bank_name', 'cb')->first();

            if (!$exchange_rate_cache) $exchange_rate_cache = new ExchangeRateCache();

            $exchange_rate_cache->bank_name = 'cb';
            $exchange_rate_cache->data = json_encode($data);
            $exchange_rate_cache->rate_date = now()->format('Y-m-d H:i:s');
            $exchange_rate_cache->save();
        }

        $link = "http://www.floatrates.com/daily/rub.json";
        @$content = file_get_contents($link, false, stream_context_create(['http' => ['timeout' => 2], 'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]));
        if ($content) {
            $array = json_decode($content, true);
            $data = [];
            foreach ($array as $v) {
                $data[$v['code']] = $v['inverseRate'];
            }
            $data[self::RUB] = 1;

            $exchange_rate_cache = ExchangeRateCache::where('bank_name', 'floatrates')->first();

            if (!$exchange_rate_cache) $exchange_rate_cache = new ExchangeRateCache();

            $exchange_rate_cache->bank_name = 'floatrates';
            $exchange_rate_cache->data = json_encode($data);
            $exchange_rate_cache->rate_date = Carbon::parse($array['usd']['date'])->format('Y-m-d H:i:s');
            $exchange_rate_cache->save();
        }

        //bitinfo
        try {
            $data = self::parseBitinfo();
            if (!empty($data)) {
                $exchange_rate_cache = ExchangeRateCache::where('bank_name', 'bitinfo')->first();

                if (!$exchange_rate_cache) $exchange_rate_cache = new ExchangeRateCache();

                $exchange_rate_cache->bank_name = 'bitinfo';
                $exchange_rate_cache->data = json_encode($data);
                $exchange_rate_cache->is_crypto = true;
                $exchange_rate_cache->rate_date = now()->format('Y-m-d H:i:s');
                $exchange_rate_cache->save();

            }
        } catch (\Exception $e) {
            Log::channel('default')->error($e->getMessage());
        }
    }

    /**
     * Checks if given currency is valid.
     * @param string $currency
     * @return bool
     */
    public static function isValid(string $currency): bool
    {
        return $currency === self::USD
            || $currency === self::EUR
            || $currency === self::RUB
            || $currency === self::UAH
            || $currency === self::INR
            || $currency === self::USDT
            || $currency === self::BTC
            || $currency === self::mBTC
            || $currency === self::KZT
            || $currency === self::UZS;
    }

    /**
     * Checks if given currency is active.
     * @param string $currency
     * @return bool
     */
    public static function isActive(string $currency): bool
    {
        if (!self::isValid($currency)) return false;
        return in_array(UserWallet::getCurrencyCode($currency), UserWallet::activeCurrencies());
    }

    public static function parseBitinfo()
    {
        $url = "https://bitinfocharts.com/ru/crypto-kurs/";
        @$data = file_get_contents($url);
        $rates = [];
        if ($data !== null) {
            preg_match_all('#<tr.*?>.*?<td.*?>(\w{3,5})\s?+<br>.*?\$\s*?([0-9\.\,]+)#is', $data, $matches);
            if (!empty($matches)) {
//                dump($matches);
                foreach ($matches[1] as $n => $v) {
                    $rates[$v] = doubleval(str_replace(',', '', $matches[2][$n]));
                }
            }
        }
        return $rates;
    }

    public static function getMoneyCoef($currency_id): float
    {
        $params = BonusCurrencyConfig::getBonusConfig($currency_id);

        $money_k = 1;
        if ($params === null) {
            Log::channel('stack')->warning('bonus currency params not found! Currency:' . $currency_id);
        } else {
            $money_k = $params->money_k;
        }

        return $money_k;
    }

    public static function getLangCurrencyMap()
    {
        return [
            'en' => 'INR',
            'ru' => 'RUB',
            'hi' => 'INR',
            'ua' => 'UAH',
            'kz' => 'KZT',
            'de' => 'EUR',
            'es' => 'EUR',
            'fr' => 'EUR',
            'it' => 'EUR',
            'pt' => 'EUR',
            'sk' => 'EUR',
            'uz' => 'UZS',
        ];
    }

    public static function getCountryCurrencyMap()
    {
        return [
            'ua' => 'UAH',
            'ru' => 'RUB',
            'kz' => 'KZT',
            'by' => 'RUB',
            'in' => 'INR',
            'uz' => 'UZS',
        ];
    }


    /**
     * function get currency symbol by currency code
     * @see https://gist.github.com/champsupertramp/95493faa7ba12b61bf6e
     **/
    public static function getCurrencySymbol($currency = '')
    {
        $symbols = array(
            'AED' => '&#1583;.&#1573;', // ?
            'AFN' => '&#65;&#102;',
            'ALL' => '&#76;&#101;&#107;',
            'AMD' => '&#1423;',
            'ANG' => '&#402;',
            'AOA' => '&#75;&#122;', // ?
            'ARS' => '&#36;',
            'AUD' => '&#36;',
            'AWG' => '&#402;',
            'AZN' => '&#1084;&#1072;&#1085;',
            'BAM' => '&#75;&#77;',
            'BBD' => '&#36;',
            'BDT' => '&#2547;', // ?
            'BGN' => '&#1083;&#1074;',
            'BHD' => '.&#1583;.&#1576;', // ?
            'BIF' => '&#70;&#66;&#117;', // ?
            'BMD' => '&#36;',
            'BND' => '&#36;',
            'BOB' => '&#36;&#98;',
            'BRL' => '&#82;&#36;',
            'BSD' => '&#36;',
            'BTC' => '&#3647;',
            'BTN' => '&#78;&#117;&#46;', // ?
            'BWP' => '&#80;',
            'BYR' => '&#112;&#46;',
            'BYN' => '&#66;&#114;',
            'BZD' => '&#66;&#90;&#36;',
            'CAD' => '&#36;',
            'CDF' => '&#70;&#67;',
            'CHF' => '&#67;&#72;&#70;',
            'CLF' => '', // ?
            'CLP' => '&#36;',
            'CNY' => '&#165;',
            'COP' => '&#36;',
            'CRC' => '&#8353;',
            'CUC' => '&#8396;',
            'CUP' => '&#8396;',
            'CVE' => '&#36;', // ?
            'CZK' => '&#75;&#269;',
            'DJF' => '&#70;&#100;&#106;', // ?
            'DKK' => '&#107;&#114;',
            'DOP' => '&#82;&#68;&#36;',
            'DZD' => '&#1583;&#1580;', // ?
            'EGP' => '&#163;',
            'ERN' => '&#78;&#102;&#107;', // ?
            'ETB' => '&#66;&#114;',
            'EUR' => '&#8364;',
            'FJD' => '&#36;',
            'FKP' => '&#163;',
            'GBP' => '&#163;',
            'GEL' => '&#4314;', // ?
            'GGP' => '&#163;',
            'GHS' => '&#162;',
            'GIP' => '&#163;',
            'GMD' => '&#68;', // ?
            'GNF' => '&#70;&#71;', // ?
            'GTQ' => '&#81;',
            'GYD' => '&#36;',
            'HKD' => '&#36;',
            'HNL' => '&#76;',
            'HRK' => '&#107;&#110;',
            'HTG' => '&#71;', // ?
            'PKE' => '&#36;',
            'HUF' => '&#70;&#116;',
            'IDR' => '&#82;&#112;',
            'ILS' => '&#8362;',
            'IMP' => '&#163;',
            'INR' => '&#8377;',
            'IQD' => '&#1593;.&#1583;', // ?
            'IRR' => '&#65020;',
            'IRT' => '&#65020;',
            'ISK' => '&#107;&#114;',
            'JEP' => '&#163;',
            'JMD' => '&#74;&#36;',
            'JOD' => '&#74;&#68;', // ?
            'JPY' => '&#165;',
            'KES' => '&#75;&#83;&#104;', // ?
            'KGS' => '&#1083;&#1074;',
            'KHR' => '&#6107;',
            'KMF' => '&#67;&#70;', // ?
            'KPW' => '&#8361;',
            'KRW' => '&#8361;',
            'KWD' => '&#1583;.&#1603;', // ?
            'KYD' => '&#36;',
            'KZT' => '&#1083;&#1074;',
            'LAK' => '&#8365;',
            'LBP' => '&#163;',
            'LKR' => '&#8360;',
            'LRD' => '&#36;',
            'LSL' => '&#76;', // ?
            'LTL' => '&#76;&#116;',
            'LVL' => '&#76;&#115;',
            'LYD' => '&#1604;.&#1583;', // ?
            'MAD' => '&#1583;.&#1605;.', //?
            'MDL' => '&#76;',
            'MGA' => '&#65;&#114;', // ?
            'MKD' => '&#1076;&#1077;&#1085;',
            'MMK' => '&#75;',
            'MNT' => '&#8366;',
            'MOP' => '&#77;&#79;&#80;&#36;', // ?
            'MRO' => '&#85;&#77;', // ?
            'MUR' => '&#8360;', // ?
            'MVR' => '.&#1923;', // ?
            'MWK' => '&#77;&#75;',
            'MXN' => '&#36;',
            'MYR' => '&#82;&#77;',
            'MZN' => '&#77;&#84;',
            'NAD' => '&#36;',
            'NGN' => '&#8358;',
            'NIO' => '&#67;&#36;',
            'NOK' => '&#107;&#114;',
            'NPR' => '&#8360;',
            'NZD' => '&#36;',
            'OMR' => '&#65020;',
            'PAB' => '&#66;&#47;&#46;',
            'PEN' => '&#83;&#47;&#46;',
            'PGK' => '&#75;', // ?
            'PHP' => '&#8369;',
            'PKR' => '&#8360;',
            'PLN' => '&#122;&#322;',
            'PYG' => '&#71;&#115;',
            'QAR' => '&#65020;',
            'RON' => '&#108;&#101;&#105;',
            'RSD' => '&#1044;&#1080;&#1085;&#46;',
            'RUB' => '&#1088;&#1091;&#1073;',
            'RWF' => '&#1585;.&#1587;',
            'SAR' => '&#65020;',
            'SBD' => '&#36;',
            'SCR' => '&#8360;',
            'SDG' => '&#163;', // ?
            'SEK' => '&#107;&#114;',
            'SGD' => '&#36;',
            'SHP' => '&#163;',
            'SLL' => '&#76;&#101;', // ?
            'SOS' => '&#83;',
            'SPL' => '&#163;',
            'SRD' => '&#36;',
            'STD' => '&#68;&#98;', // ?
            'SVC' => '&#36;',
            'SYP' => '&#163;',
            'SZL' => '&#76;', // ?
            'THB' => '&#3647;',
            'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
            'TMT' => '&#109;',
            'TND' => '&#1583;.&#1578;',
            'TOP' => '&#84;&#36;',
            'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
            'TTD' => '&#36;',
            'TVD' => '&#36;',
            'TWD' => '&#78;&#84;&#36;',
            'TZS' => '',
            'UAH' => '&#8372;',
            'UGX' => '&#85;&#83;&#104;',
            'USD' => '&#36;',
            'UYU' => '&#36;&#85;',
            'UZS' => '&#1083;&#1074;',
            'VEF' => '&#66;&#115;',
            'VND' => '&#8363;',
            'VUV' => '&#86;&#84;',
            'WST' => '&#87;&#83;&#36;',
            'XAF' => '&#70;&#67;&#70;&#65;',
            'XCD' => '&#36;',
            'XDR' => '',
            'XOF' => '',
            'XPF' => '&#70;',
            'ZAR' => '&#82;',
            'ZMW' => '&#90;&#75;',
        );
        if (isset($symbols[$currency])) {
            return html_entity_decode($symbols[$currency]);
        }
        return $currency;
    }

    /**
     * returns hex color associated with currency
     * @param $currency
     * @return string
     */
    public static function getCurrencyColor($currency): string
    {
        $colors = [
            'UAH' => 'FCC339',
            'RUB' => 'EF8E8B',
            'USD' => 'A2DC8D',
            'EUR' => '787BF7',
            'INR' => 'b000db',
            'BTC' => 'b000db',
            'MBTC' => 'b000db',
            'USDT' => '81b070',
            'KZT' => '00a8c9',
            'UZS' => '5ccbec',
        ];
        return $colors[mb_strtoupper($currency)]?? substr(md5($currency), 0, 6);
    }

    public static function isCrypto(string $currency): bool
    {
        return in_array($currency, self::$crypto);
    }
}
