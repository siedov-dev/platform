<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @deprecated
 */
class Journal
{
    /**
     * Adds records to the journal about money movement.
     * @deprecated
     * @param $userId
     * @param $action
     * @param $credits
     */
    public static function logCreditsMovement($userId, $action, $credits)
    {
        DB::table('new_h_log')
            ->insert([
                'user_id' => $userId,
                'game_id' => 0,
                'game_status' => 0,
                'credits' => $credits,
                'nominal' => 0,
                'money' => 0,
                'last_win' => 0,
                'line' => 0,
                'bet' => 0,
                'action' => $action,
                'action_table_id' => 0,
                'request' => '',
                'result' => '',
                'time' => date('Y-m-d H:i:s'),
                'user_ip' => '',
                'drum_rand' => '',
            ]);
    }

    /**
     * @deprecated
     * @param $user_id
     * @param $game_id
     * @param $money
     * @param $action
     * @param $request
     * @param $result
     * @return void
     */
	public static function logMoneyMovement($user_id, $game_id, $money, $action, $request, $result)
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		DB::table('new_h_log')
			->insert([
				'user_id' => $user_id,
				'game_id' => $game_id,
				'money' => $money,
				'action' => $action,
				'request' => $request,
				'result' => $result,
				'time' => date('Y-m-d H:i:s'),
				'user_ip' => $ip,

				//default value fields
				'game_status' => 0,
				'credits' => 0,
				'nominal' => 0,
				'last_win' => 0,
				'line' => 0,
				'bet' => 0,
				'drum_rand' => '',
				'action_table_id' => 0,
			]);
	}
}
