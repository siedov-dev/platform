<?php


namespace App\GamesProviders\PragmaticPlay;


use App\Events\MoneySpent;
use App\Gameproviders\PragmaticPlay\Models\PragGame;
use App\Gameproviders\PragmaticPlay\Models\PragWallet;
use App\Models\TopWin;
use App\MUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * @deprecated
 */
class PragmaticPlayStage
{

    protected $login = "krkn";
    protected $key = "testKey";
    protected $ProviderId = "PragmaticPlay";

    protected $game_server_domain = "krakencasino.prerelease-env.biz";
    protected $demo_server_domain = "demogamesfree.pragmaticplay.net";
    protected $game_url = "https://krakencasino.prerelease-env.biz/gs2c/playGame.do?key=<KEY_VALUE>&stylename={stylename}";
    protected $api_url = "https://api.prerelease-env.biz/IntegrationService/v3/http";

    public function getGames()
    {
        if(Redis::exists('pragplay_cached_games')) return json_decode(Redis::get('pragplay_cached_games'), true);

        $resp = Cache::remember('pragplay_games', now()->addMinutes(2), function() {
            $method = '/CasinoGameAPI/getCasinoGames/';
            $params = [
                'secureLogin' => $this->login,
            ];

            $resp = $this->query($method, $params, true);
            if ($resp === false) return [];
            return $resp['gameList'];
        });
        return $resp;
    }

    public function getUncachedGames()
    {
            $method = '/CasinoGameAPI/getCasinoGames/';
            $params = [
                'secureLogin' => $this->login,
                'options' => 'GetDataTypes',
            ];

            $resp = $this->query($method, $params, true);
            if ($resp === false) return [];
            return $resp['gameList'];
    }

    public static function cacheGames()
    {
        $api = new PragmaticPlayStage();
        $items = $api->getUncachedGames();
        //store game for 10 mins
        if(!empty($items)) Redis::set('pragplay_cached_games', json_encode($items));
        else Redis::del('pragplay_cached_games');

        return $items;
    }

    public function findGame($id)
    {
        $games = $this->getGames();
        foreach ($games as $game){
            if($game['gameID'] == $id) return $game;
        }
        return false;
    }

    public static function getGameImg($game_id)
    {
        $img = "/pragimg/" . $game_id . ".png";
        if (!file_exists(public_path() . $img)) {
            $img = '/images/noimage.jpg';
        }
        return $img;
    }

    public function initDemoGame($game_id)
    {
        $language = "ru";
        $currency_symbol = "RUB";
        $URL = action('IndexController@index');
        return "https://{$this->demo_server_domain}/gs2c/openGame.do?gameSymbol={$game_id}&lang={$language}&cur={$currency_symbol}&lobbyUrl={$URL}&stylename={$this->login}";
    }

    public function initGame($game_id, MUser $player)
    {
        $wallet = PragWallet::getForUser($player);
        $wallet->token = Str::random(32);
        $wallet->save();

        $key = [
            'token' => $wallet->token,
            'symbol' => $game_id,
            'language' => 'ru',
            'technology' => 'H5',
            'platform' => 'web',
            'cashierUrl' => action('IndexController@index'),
            'lobbyUrl' => action('IndexController@index'),
        ];

        $params = [
            'stylename' => $this->login,
            'rcCloseUrl' => action('IndexController@index'),
        ];

        $url = "https://{$this->game_server_domain}/gs2c/playGame.do?key=".urlencode(http_build_query($key))."&".http_build_query($params);

        return $url;
    }

    public function transferMoney(MUser $user)
    {
        DB::beginTransaction();
        try {
            /** @var PragWallet $wallet */
            $wallet = PragWallet::getForUser($user);

            $wallet->amount += floor($user->getBalance());

            $wallet->save();

            $user->remMoney($user->getBalance())->save();
        } catch (\Exception $exception){
            Log::error($exception);
            DB::rollBack();
        }

        DB::commit();
    }

    public function getPlayersFRB($player_id)
    {
        $method = '/FreeRoundsBonusAPI/getPlayersFRB/';
        $params = [
            'secureLogin' => $this->login,
            'playerId' => $player_id,
        ];

        $resp = $this->query($method, $params, true);
        return $resp;
    }

    public function createFRB(string $player_id, $currency_name, $games_list, $rounds, $bonus_id, $expiration_date)
    {
        $method = '/FreeRoundsBonusAPI/createFRB';
        $params = [
            'secureLogin' => $this->login,
            'playerId' => $player_id,
            'currency' => $currency_name,
            'gameIDList' => $games_list,
            'rounds' => $rounds,
            'bonusCode' => $bonus_id,
            'expirationDate' => $expiration_date,
        ];

        $resp = $this->query($method, $params, false);
        return $resp;
    }

    public function cancelFRB($bonus_id)
    {
        $method = '/FreeRoundsBonusAPI/cancelFRB/';
        $params = [
            'secureLogin' => $this->login,
            'bonusCode' => $bonus_id,
        ];

        $resp = $this->query($method, $params, false);
        return $resp;
    }

    public function checkHash($data)
    {
        $hash = $data['hash'];
        if($hash !== $this->calcHash($data)) return false;
        return true;
    }

    /**
     * calc hash
     * should not html encode
     * @param array $data
     * @return string
     */
    public function calcHash(array $data = []):string
    {
        if (isset($data['hash'])) {
            unset($data['hash']);
        }
        ksort($data, SORT_STRING);
//        return md5(http_build_query($data).$this->key);
        $dstr = [];
        foreach ($data as $k => $v){
            $dstr[] = ''.$k.'='.$v;
        }
        return md5(implode('&', $dstr).$this->key);
    }


    public function query($method, $params = [], bool $post =  false)
    {
        $url = $this->api_url.$method;
        $params['hash'] = $this->calcHash($params);

        if(!$post && !empty($params)){
            $url .= '?'.http_build_query($params);
        }
        Log::channel('pragmatik')->info('url: '.var_export($url, true));
        Log::channel('pragmatik')->info('params: '.var_export($params, true));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if(empty($response)){
            Log::channel('pragmatik')->error('empty response');
            return false;
        }

        if($response['error'] != 0){
            if($response['error'] == 10) return false;
            Log::channel('pragmatik')->error($response['description']);
            Log::channel('pragmatik')->error(var_export($response, true));
            return false;
        }

        return $response;
    }

    public function closeSession(MUser $user)
    {
        DB::beginTransaction();
        try {
            /** @var PragWallet $wallet */
            $wallet = PragWallet::getForUser($user);
            $money = (float)$wallet->amount;

            $wallet->amount = 0;
            $wallet->save();
            $user->addMoney($money, $wallet->currency);
            /** @var PragGame $praggame */
            $praggame = PragGame::where('user_id', Auth::id())->whereNull('logout_date')->latest('login_date')->first();

        } catch (\Exception $exception){
            DB::rollBack();
            Log::channel('pragmatik')->error($exception->getMessage());
            return;
        }

        if($praggame){

            try {

                $praggame->logout_date = date('Y-m-d H:i:s');
                $praggame->logout_balance = $money;
                $praggame->last_active_date = date('Y-m-d H:i:s');

//                Journal::logMoneyMovement(Auth::id(), substr($praggame->game_id, 0, 11), $money, 'logout', 0, 0);

                $currency = $user->getCurrencyName($wallet->currency);
                $spentAmount = $praggame->login_balance - $praggame->logout_balance;

                DB::table('payout_transactions')
                    ->insert([
                        'user_id' => $user->id,
                        'hall_id' => $user->hall_id,
                        'date' => date('Y-m-d H:i:s'),
                        'sum' => ($money),
                        'status_id' => 68,
                        'currency' => $currency,
                        'operation' => 'Выход с PragPlay'
                    ]);

                $praggame->save();

                //close all old sessions
                PragGame::where('user_id', Auth::id())->whereNull('logout_date')
                    ->update(['logout_date' => date('Y-m-d H:i:s')]);

                TopWin::userWin($user, $spentAmount * -1, $praggame->game_id, $wallet->currency, 'pragplay', '');

                event(new MoneySpent($user, $spentAmount, $currency));

            } catch (\Exception $exception){
                DB::rollBack();
                Log::channel('pragmatik')->error($exception->getMessage());
                return;
            }
        }

        DB::commit();
    }

}
