<?php

namespace App\GamesProviders\TVBet;

use App\GamesProviders\GameProviderApi;
use App\MUser;

class TVBetGamesProviderApi extends GameProviderApi
{
    const GATEWAY = 'https://tvbetapiproxy.top/mis-api/api/v2';

    private $_secret;

    public function __construct()
    {
        $this->_secret = config('game_providers_keys.tvBetSecret');
    }

    public function getAllGames(): array
    {
        return [];
    }

    public function startSession(MUser $user, string $game_id, string $session_id = ""): string
    {
        return "";
    }

    public function closeSession(MUser $user, string $session_id = ""): bool
    {
        return true;
    }


    private function sendDataToTvBet(string $url, array $data)
    {

    }


    private function post(string $url, array $data)
    {

    }

    public function getAccessToken(int $clientId)
    {
        $data = [
            'PartnerClientId' => $clientId,
            'Secret'          => $this->_secret,
        ];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, self::GATEWAY . '/auth/token');
        curl_setopt($curl, CURLOPT_VERBOSE, 0);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json; charset=utf-8',
            'Accept: application/json',
        ]);

        $res = curl_exec($curl);
        $success = !curl_errno($curl);
        curl_close($curl);
        return $res;
    }
}
