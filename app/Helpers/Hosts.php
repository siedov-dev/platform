<?php

namespace App\Helpers;

class Hosts
{
    /** get hosts for finance callbacks
     * @param string $currency
     * @return string
     */
    public static function getFinHost(string $currency = 'RUB'): string
    {
        return match ($currency) {
            'RUB', 'KZT', 'UZS' => 'https://krcas.com',
            default => 'https://krakenfn.com'
        };
    }

    /** get all financial callbacks hosts
     * @return string[]
     */
    public static function getFinHosts(): array
    {
        return ['https://krcas.com', 'https://krakenfn.com', 'https://krakenfn.local'];
    }

    /** get all games callback hosts
     * @return string[]
     */
    public static function getGamesHosts(): array
    {
        return ['https://krakengm.com', 'https://krkfgm.com'];
    }
}
