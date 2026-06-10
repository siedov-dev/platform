<?php

namespace App\Http\Controllers;

use App\Helpers\Hlog;
use App\Models\Rank\UserRank;
use App\Models\Rank\UserRankRewarded;
use App\Models\Slc\SlcUserInventory;
use App\MUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RanksController extends Controller
{
    public function getReward(Request $request)
    {
        $user = MUser::getAuth();
        $inventory = SlcUserInventory::forUser($user);
        DB::beginTransaction();
        try {
            /** @var UserRank $rank */
            $rank = UserRank::where('currency_id', $request->input('currency'))
                ->where('rank_level', $request->input('level'))
                ->first();
            if ($user->getRank()->getLevel() >= $rank->getLevel() && !$rank->isRewarded($user, $user->getCurrency())) {
                UserRankRewarded::setRewarded($user, $rank);
                $rewards = $rank->getRewards();
                foreach ($rewards as $reward) {
                    $inventory->addItem($reward, 'rank-'.$rank->getLevel());
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Hlog::logException($e);
        }
        return redirect()->route('slc.achievements');
    }
}
