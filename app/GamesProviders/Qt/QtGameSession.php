<?php

namespace App\GamesProviders\Qt;

use App\Events\MoneySpent;
use App\Models\Journal;
use App\Models\TopWin;
use App\MUser;
use App\UserWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @property $id
 * @property $user_id
 * @property $hall_id
 * @property $status
 * @property $session_id
 * @property $game_id
 * @property $login_balance
 * @property $logout_balance
 * @property string $login_date
 * @property string $logout_date
 * @property string $last_active_date
 */
class QtGameSession extends Model
{
	public $table = "qt_game";

	public $timestamps = false;

	/**
	 * @param MUser $user
	 * @return mixed
	 */
	public static function transferMoneyQt($user, $type, $amount = 0)
	{
	    /** @var MUser $user */
	    $user = MUser::find($user->id);
		if (!$user) {
			return false;
		}

		$token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username=api_drummatic&password=FBqcty5W', false, stream_context_create([ 'ssl' => ['verify_peer'=>false, 'verify_peer_name'=>false]]));
		if(empty($token)) {
		    Log::error('Unable to receive QT API token.');
		    return null;
        }
		$token = json_decode($token);
		$token = $token->access_token;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/fund-transfers");
		curl_setopt($curl, CURLOPT_POST, true);
		$post = [
			'playerId' => urlencode($user->id.$user->getCurrencyName()),
			'currency' => $user->getCurrencyName(),
			'type' => $type,//'CREDIT',//DEBIT
			'referenceId' => microtime(true),
//			'amount' => round($type == 'CREDIT' ? $user->getBalance() : $amount, 2).'',
			'amount' => round($amount, 2).'',
		];
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer ' . $token
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$html = curl_exec($curl);
		if (curl_errno($curl)) {
			$response = [];
			Log::error('Error on transferring money QT:' . $html);
		} else {
			$response = json_decode($html, true);
			Log::info('QT transfer response:'.var_export($response, true));
			if (!empty($response['id'])) {
				$response = self::completeTransfer($response['id'], $token);
			} else {
                Log::error('Error on transferring money QT:' . var_export($html, true));
				$response = [];
			}
		}

		curl_close($curl);

		return $response;
	}

	public static function completeTransfer($id, $token)
	{
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/fund-transfers/$id/status");
		//curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		$post = [
			'status' => "COMPLETED"
		];
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($post));
		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer ' . $token
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$html = curl_exec($curl);
		if (curl_errno($curl)) {
			$response = [];
		} else {
			$response = json_decode($html, true);
            Log::info('QT transfer complete response:'.var_export($response, true));
        }

		curl_close($curl);

		return $response;
	}

	public static function getPlayerBalance($id, string &$currency = null)
	{
	    $user = MUser::find($id);
		$token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username=api_drummatic&password=FBqcty5W', false, stream_context_create([ 'ssl' => ['verify_peer'=>false, 'verify_peer_name'=>false]]));
        $token = json_decode($token);
		$token = $token->access_token;

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/wallet/ext/$id".$user->getCurrencyName());

		curl_setopt($curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Bearer ' . $token
		));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$html = curl_exec($curl);
		if (curl_errno($curl)) {
			$response = [];
		} else {
			$response = json_decode($html, true);
		}

		curl_close($curl);


        Log::info('QT balance response:'.var_export($response, true));

        if($currency !== null && isset($response['currency'])){
            $currency = $response['currency'];
        }

        return $response['amount'];
	}

    public static function qtGamesByProvider($data, $provider = null)
    {
        $games = [];
        $providers = [];
        $path = base_path() . "/game_types.json";
        $game_types = json_decode(file_get_contents($path), true);

        $filter_string = '';
        foreach ($game_types as $k => $v) {
            if ($v)
                $filter_string .= $k;
        }

        if($provider == 'vsport'){
            foreach ($data as $game) {
                if(isset($game['category'])) {
                    $type = explode('/', $game['category']);
                    if (strpos($filter_string, $type[1]) !== false) {
                        if ($game['provider'] == 'BVS' || $game['provider'] == 'KIR') {
                            $games['vsport'][] = $game;
                            $providers[] = 'vsport';
                        }
                    }
                } else {
                        if ($game['provider'] == 'BVS' || $game['provider'] == 'KIR') {
                            $games['vsport'][] = $game;
                            $providers[] = 'vsport';
                        }
                }
            }

        } else {
            foreach ($data as $game) {
                if(isset($game['category'])) {
                    $type = explode('/', $game['category']);
                    if (strpos($filter_string, $type[1]) !== false) {
                        if ($game['provider'] == 'EZU' && $provider != 'EZU') continue;
                        if ($game['provider'] == 'BVS' && $provider != 'BVS') continue;
                        if ($game['provider'] == 'KIR' && $provider != 'KIR') continue;
                        $games[$game['provider']][] = $game;
                        $providers[] = $game['provider'];
                    }
                } else {
                    if ($game['provider'] == 'EZU' && $provider != 'EZU') continue;
                    if ($game['provider'] == 'BVS' && $provider != 'BVS') continue;
                    if ($game['provider'] == 'KIR' && $provider != 'KIR') continue;
                    $games[$game['provider']][] = $game;
                    $providers[] = $game['provider'];
                }
            }
        }

        $provs = array_unique($providers);
        return ['games' => $games, 'providers' => $provs];
    }


    public static function getQtProviders()
    {
        if(!config(app()->getLocale().'.game_systems.Qt')) return [];

        $game_list = Cache::remember('QTGames', now()->addMinutes(3), function() {
            $token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username=api_drummatic&password=FBqcty5W', false, stream_context_create([ 'ssl' => ['verify_peer'=>false, 'verify_peer_name'=>false]]));
            if(!$token) return [];
            $token = json_decode($token);
            $token = $token->access_token;

            // getting QT game list
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games");
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Authorization: Bearer '.$token
            ));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $html = curl_exec($curl);

            if (curl_errno($curl)) {
                $game_list = [];
            } else {
                $game_list = json_decode($html,true);

            }
            curl_close($curl);
            return $game_list;
        });

        if(empty($game_list)){
            return [];
        }
        $info = self::qtGamesByProvider($game_list['items']);

        return $info['providers'];
    }

    /**
     * @deprecated
     * @return void
     * @throws \Exception
     */
    public static function closeSession()
    {
//        /** @var MUser $user */
//        $user = MUser::find(Auth::id());
//        $user->waitOrUnlock();
//
//        //check if user was in qt game
//        /** @var QtGameSession $qt */
////        $qt = QtGameSession::where('user_id', Auth::user()->id)->whereNull('logout_date')->where('login_balance', '>', 0)->latest('last_active_date')->first();
//
////        if(!$qt){
//            $qt = QtGameSession::where('user_id', Auth::user()->id)->whereNull('logout_date')->latest('login_date')->first();
////        }
//
//        if($qt) {
//
//            $user->lock(MUser::LOCK_QT_TRANSFER);
//            DB::beginTransaction();
//            try {
//                $user->refresh();
//                $currency = $user->getCurrencyName();
//                $amount = QtGameSession::getPlayerBalance($user->id, $currency);
//                Log::info('QT amount '.$amount);
//            } catch (\Exception $e){
//                DB::rollBack();
//                throw $e;
//            }
//            if ($amount != null /*&& $amount > 0*/) {
//                try {
//                    if($amount > 0) $response = QtGameSession::transferMoneyQt($user, 'DEBIT', $amount);
//                } catch (\Exception $e){
//                    DB::rollBack();
//                    throw $e;
//                }
//                if ($amount == 0 || ($response && $response['status'] == "COMPLETED")) {
//                    try {
//                        $user->addMoney($amount, UserWallet::getCurrencyCode($currency))->save();
//                        DB::commit();
//                    } catch (\Exception $e){
//                        DB::rollBack();
//                        throw $e;
//                    }
//
//                    $qt->logout_date = date('Y-m-d H:i:s');
//                    $qt->logout_balance = $amount;
//                    $qt->last_active_date = date('Y-m-d H:i:s');
//
//                    Journal::logMoneyMovement($user->id, $qt->game_id, $amount, 'logout', 0, 0);
//
//                    DB::table('payout_transactions')
//                        ->insert([
//                            'user_id' => $user->id,
//                            'hall_id' => $user->hall_id,
//                            'date' => date('Y-m-d H:i:s'),
//                            'sum' => $amount,
//                            'status_id' => '41',
//                            'currency' => $currency,
//                            'operation' => 'Пополнение от QT'
//                        ]);
//                    $qt->save();
//
//                    //close all old sessions
//                    QtGameSession::where('user_id', Auth::id())->whereNull('logout_date')
//                        ->update(['logout_date' => date('Y-m-d H:i:s')]);
//
//                    $spentAmount = $qt->login_balance - $qt->logout_balance;
//                    event(new MoneySpent($user, $spentAmount, $currency));
//                    TopWin::userWin($user, $spentAmount * -1, $qt->game_id , $user->currency , 'qt', '');
//
//
//                    //close all old sessions
//                    QtGameSession::where('user_id', $user->id)->whereNull('logout_date')
//                        ->update(['logout_date' => date('Y-m-d H:i:s')]);
//
//                    DB::table('m_users')
//                        ->where('id', '=', $user->id)
//                        ->update([
////                                'money' => Auth::user()->money + $amount,
//                            'last_game_id' => $qt->game_id,
//                            'last_active' => date('Y-m-d H:i:s'),
//                            'total_win' => $user->total_win + $amount,
//                        ]);
//
//                }
//            } else {
//                DB::commit();
//            }
//
//            $user->unlock();
//        }
    }


    /**
     * @return array
     * ["id" => "BPG-dragonstreasure",
        "name" => "Dragons Treasure",
        "provider" => "BPG",
        "category" => "CASINO/SLOT/5REEL",
        "supportsDemo" => true,
        "clientTypes" => [ …1],
        "languages" => [ …6],
        "currencies" => [ …49],
        "supportedDevices" => [ …3],
        "freeSpinTrigger" => true, :means game supports freespins (not confirmed)
     * ]
     */
//    public static function qtGames()
//    {
//        $token = @file_get_contents('https://api.qtplatform.com/v1/auth/token?grant_type=password&response_type=token&username=api_drummatic&password=FBqcty5W', false, stream_context_create([ 'ssl' => ['verify_peer'=>false, 'verify_peer_name'=>false]]));
//        if($token == null) return [];
//        $token = json_decode($token);
//        $token = $token->access_token;
//
//        // getting QT game list
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_URL, "https://api.qtplatform.com/v1/games");
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
//            'Accept: application/json',
//            'Authorization: Bearer '.$token
//        ));
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//        $html = curl_exec($curl);
//
//        if (curl_errno($curl)) {
//            $game_list = [];
//        } else {
//            $game_list = json_decode($html,true);
//
//        }
//        curl_close($curl);
//        return $game_list['items']?? [];
//    }
}
