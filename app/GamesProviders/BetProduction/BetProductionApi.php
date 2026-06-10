<?php

namespace App\GamesProviders\BetProduction;

use App\GamesProviders\GameProviderApi;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BetProductionApi extends GameProviderApi
{
    private $_secret;

    public function __construct()
    {
        $this->_secret = 'wcyPCJpba8AJOs4SaUuw3HZaI_wtzuNi';
    }

    public function getAllGames(): array
    {
        return [];
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        return "";
    }

    public function transferMoneyInGame(MUser $user, float $amount): bool
    {
        return true;
    }

    public function transferMoneyBack(MUser $user): bool
    {
        return true;
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        return true;
    }

    public function getBalance(MUser $user, string &$currency): float
    {
        return $user->getWallet(UserWallet::getCurrencyCode($currency))->getBalance();
    }

    /**
     * get or generate current user auth token
     * @param MUser $user
     * @return string
     */
    public function getToken(MUser $user): string
    {
        if (empty($user->getWallet()->getSportToken())) {
            $user->getWallet()->setSportToken( md5($user->id . time() . Str::random(8)))->save();
        }
        return $user->getWallet()->getSportToken();
    }

    /**
     * refresh and get new user's token
     * @param MUser $user
     * @return string
     */
    public function refreshToken(MUser $user): string
    {
        $user->getWallet()->setSportToken( md5($user->id . time() . Str::random(8)))->save();
        return $user->getWallet()->getSportToken();
    }

    public static function getPlayUrl(string $type = "all"): string
    {
        return route('games.BetProduction', [
            'gameId' => $type,
            'mode' => 'real',
            'compoundId' => 'bet-production|bet-production|all',
        ]);
    }

    public function sign(array $data): string
    {
        $json_string = json_encode($data);
        $md5_hex_string = md5($json_string . $this->_secret);
        $sign = base64_encode(hex2bin($md5_hex_string));
        return $sign;
    }

    public function getUserWalletByToken(string $token): ?UserWallet
    {
        return UserWallet::where('sport_token', $token)->first();
    }

    public function getPlayersHistory(string $player_id, int $st_date, int $end_date)
    {
        $data = [
            'start' => $st_date,
            'end' => $end_date,
            'pid' => $player_id,
        ];
        $url = "https://siteapi.bigstarbet.com/iframe-bet-history";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'SecretKey: ' . $this->_secret,
            'language: ru',
        ]);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        if (!empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        Log::info('Bet production request: '.$url);

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($ch, CURLOPT_USERAGENT, 'KrakenCassino');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_VERBOSE, true);

        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $text = curl_exec($ch);
        $err = curl_error($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $resp = json_decode($text, true);

        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);

        Log::info('curl verbose');
        Log::info(htmlspecialchars($verboseLog));

        if ($err) {
            Log::error($err);
        }

        Log::info('bet prod resp:');
        Log::info('code:'.$httpcode);
        Log::info($text);
        Log::info(var_export($resp, true));
        return $resp;
    }
}
