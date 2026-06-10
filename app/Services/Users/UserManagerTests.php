<?php

namespace App\Services\Users;

use App\GamesProviders\GameData;
use App\GamesProviders\GamesManager;
use App\GamesProviders\Slotegrator\SlotegratorApi;
use App\Http\Controllers\Endpoints\SlotegratorEndpointController;
use App\MUser;
use App\Wallet;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserManagerTests
{

    protected $merchant_id = "f409d3517e094d60cac45462a9b347f0";
    protected $key = "702411089f962240d408b5d041baf806cc4747a3";

    public static function testStartGame(int $user_id = 12)
    {
        $session_id = Str::random(16);
        $user = MUser::find($user_id);
        $user->balance_mode = MUser::BALANCE_MODE_MAIN;
        $user->save();

        $game_data = new GameData($user);
        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', 'f868bdcd4d71ed1b1fc197405c60f48db19f4568');// sleg amatic admiral nelson
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency)->save();
        dump(UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id));
        dump(UserManager::getGameSession($user));
        $ct = new self();
        $round_id = Str::random(16);
        $ct->testGoEndpoint('bet', 100, $session_id, $user->getId().'RUB', 'RUB', round_id: $round_id);
        $ct->testGoEndpoint('win', 500, $session_id, $user->getId().'RUB', 'RUB', round_id: $round_id);
        dump(UserManager::getGameSession($user));
        dump(UserManager::closeGameSession($user));
        dump(UserManager::getGameSession($user));
    }

    public static function testRefund(int $user_id = 12)
    {
        $session_id = Str::random(16);
        $user = MUser::find($user_id);
        $user->balance_mode = MUser::BALANCE_MODE_MAIN;
        $user->save();

        $game_data = new GameData($user);
        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', 'f868bdcd4d71ed1b1fc197405c60f48db19f4568');// sleg amatic admiral nelson
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency)->save();
        dump(UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id));
        dump(UserManager::getGameSession($user));
        $ct = new self();
        $tr_id = Str::random(16);
        $ct->testGoEndpoint('bet', 100, $session_id, $user->getId().'RUB', 'RUB', $tr_id);
//        sleep(3);
        $ct->testGoEndpoint('refund', 500, $session_id, $user->getId().'RUB', 'RUB', '', $tr_id);
        dump(UserManager::getGameSession($user));
        dump(UserManager::closeGameSession($user));
        dump(UserManager::getGameSession($user));
    }

    public static function testWinOutOfSession(int $user_id = 12)
    {
        $session_id = Str::random(16);
        $user = MUser::find($user_id);
        $user->balance_mode = MUser::BALANCE_MODE_MAIN;
        $user->save();

        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', 'f868bdcd4d71ed1b1fc197405c60f48db19f4568');// sleg amatic admiral nelson
        dump(UserManager::getGameSession($user));
        $ct = new SlotegratorEndpointController();
        $ct->testGoEndpoint('win', 500, $session_id, $user->getId(), 'RUB');
        dump(UserManager::getGameSession($user));
    }

    public static function slegSelfValidate(int $user_id = 12)
    {
        $session_id = Str::random(16);
        /** @var MUser $user */
        $user = MUser::find($user_id);
        $user->getWallet(Wallet::CURRENCY_EUR);
        $user->balance_mode = MUser::BALANCE_MODE_MAIN;
        $user->currency = Wallet::CURRENCY_EUR;
        $user->save();
        $user->getWallet(Wallet::CURRENCY_EUR)->addMoney(100);

        $game_data = new GameData($user);
        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', '07f4946af835a0726f71b1d68a862f22a1e4daca');// sleg amatic admiral nelson
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency)->save();
        dump(UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id));
        dump(UserManager::getGameSession($user));
        $api = new SlotegratorApi();
        $init = $api->initGame($game->getId(), $user);
        dump($init);
        dump("run game");
        sleep(25);
        $result = $api->selfValidate();
        dump("self validate result:");
        dump($result);
        file_put_contents(storage_path('logs/sleg_self_validate.log'), var_export($result, true) . PHP_EOL, FILE_APPEND);
        dump(UserManager::closeGameSession($user));
    }

    public static function testBonusGame(int $user_id = 12)
    {
        $session_id = Str::random(16);
        $user = MUser::find($user_id);
        $user->balance_mode = MUser::BALANCE_MODE_BONUS;
        $user->save();

        $game_data = new GameData($user);
        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', 'f868bdcd4d71ed1b1fc197405c60f48db19f4568');// sleg amatic admiral nelson
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency);
        dump(UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id));
        dump(UserManager::getGameSession($user));
        $ct = new self();
        $ct->testGoEndpoint('bet', 100, $session_id, 'bonus'.$user->getId().'RUB', 'RUB');
        $ct->testGoEndpoint('win', 500, $session_id, 'bonus'.$user->getId().'RUB', 'RUB');
        dump(UserManager::closeGameSession($user));
    }

    public static function testBonusGameBetWin(int $user_id = 12)
    {
        $user = MUser::find($user_id);
        $ct = new self();
        $ct->testGoEndpoint('bet', 100, 'test', 'bonus'.$user->getId().'RUB', 'RUB');
        $ct->testGoEndpoint('win', 500, 'test', 'bonus'.$user->getId().'RUB', 'RUB');
    }

    public static function testBetWin(int $user_id = 12)
    {
        $user = MUser::find($user_id);
        $ct = new self();
        $ct->testGoEndpoint('bet', 20, 'test', $user->getId().'RUB', 'RUB');
        $ct->testGoEndpoint('win', 5, 'test', $user->getId().'RUB', 'RUB');
    }

    public function testGoEndpoint(string $action, float $amount, string $session_id, string $user_id, string $currency = 'RUB', $tr_id = '', $bet_transaction_id = '', $round_id = '')
    {
        if (empty($tr_id)) {
            $tr_id = Str::random(16);
        }
        $headers = [
            'X-Merchant-Id' => $this->merchant_id,
            'X-Timestamp' => time(),
            'X-Nonce' => Str::uuid()->toString(),
        ];

        $data = [
            'player_id' => $user_id,
            'action' => $action,
            'amount' => $amount,
            'currency' => $currency,
            'game_uuid' => 'f868bdcd4d71ed1b1fc197405c60f48db19f4568',
            'session_id' => $session_id,
            'transaction_id' => $tr_id,
            'bet_transaction_id' => $bet_transaction_id,
            'type' => $action,
            'round_id' => $round_id,
        ];

        $XSign = $this->sign($data, $headers);
        $headers['X-Sign'] = $XSign;

        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100/api/slotegcb', $data);
        dump($resp->status());
        dump($resp->json());
        dump($resp->body());
    }

    public static function testBonusFsGame(int $user_id = 12)
    {
        $session_id = Str::random(16);
        $user = MUser::find($user_id);
        $user->balance_mode = MUser::BALANCE_MODE_BONUS;
        $user->save();

        $game_data = new GameData($user);
        $provider = GamesManager::getProvider("sleg");
        $game = $provider->getGame('', 'f868bdcd4d71ed1b1fc197405c60f48db19f4568');// sleg amatic admiral nelson
        $game_data->loadDataForGame($game->getProviderId(), $game->getId(), $user->currency);
        dump(UserManager::startGameSession($user, $game, $game_data->toaArray(), $session_id));
        dump(UserManager::getGameSession($user));
        $ct = new self();
        $fs_count = 9;
        for ($i = $fs_count; $i >= 0; $i--) {
            $ct->testGoFreespin('win', 100, $session_id, 'bonus'.$user->getId().'RUB', $game_data->bonus_id, $i,'RUB');
        }
        $ct->testGoEndpoint('bet', 100, $session_id, 'bonus'.$user->getId().'RUB', 'RUB');
        $ct->testGoEndpoint('win', 500, $session_id, 'bonus'.$user->getId().'RUB', 'RUB');
        dump(UserManager::closeGameSession($user));
    }

    public function testGoFreespin(string $action, float $amount, string $session_id, string $user_id, $freespin_id, $quantity, string $currency = 'RUB')
    {
        $headers = [
            'X-Merchant-Id' => $this->merchant_id,
            'X-Timestamp' => time(),
            'X-Nonce' => Str::uuid()->toString(),
        ];

        $data = [
            'player_id' => $user_id,
            'action' => $action,
            'amount' => $amount,
            'currency' => $currency,
            'game_uuid' => 'f868bdcd4d71ed1b1fc197405c60f48db19f4568',
            'session_id' => $session_id,
            'transaction_id' => Str::random(16),
            'bet_transaction_id' => '',
            'type' => "freespin",
            'freespin_id' => $freespin_id,
            'quantity' => $quantity,
        ];

        $XSign = $this->sign($data, $headers);
        $headers['X-Sign'] = $XSign;

        $resp = Http::asForm()->withHeaders($headers)->post('http://127.0.0.1:23100/api/slotegcb', $data);
        dump($resp->status());
        dump($resp->json());
        dump($resp->body());
    }

    /**
     * sign a request
     * @param array $requestParams
     * @param $headers
     * @return string
     */
    protected function sign(array $requestParams, $headers): string
    {
        $mergedParams = array_merge($requestParams, $headers);
        ksort($mergedParams);
        $hashString = http_build_query($mergedParams);
        $this->merchant_id = config('keys.slotegrator.merchant_id');
        $sign = hash_hmac('sha1', $hashString, config('keys.slotegrator.key'));

        return $sign;
    }
}
