<?php


namespace App\UserCommands;


use App\Models\Bonuses\UserBonus;
use App\Models\UserGift;
use App\MUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class ActivateBonusPrize extends UserCommand
{
    protected $name = 'Активировать бонусный приз';
    protected $description = 'Активирует бонусный приз и переносит его в подарки';

    public function execute(?MUser $user = null, ?int $currency = null, $context = null): bool
    {
//        /** @var UserBonus $context */
//        if(!$context || !($context instanceof UserBonus)){
//            Log::error(__CLASS__.' no UserBonus context');
//            return false;
//        }
//
//        if(!$user)$user = MUser::find(Auth::id());
//
//        if(!$user){
//            Log::error(__CLASS__.' no user');
//            return false;
//        }
//
//        $prizes = json_decode($context->bonus_prizes, true);
//        if(!empty($prizes) && in_array($this->_params['prize_id'], $prizes)){
//
//            //remove el by val
//            $prizes = array_values(array_diff($prizes, [$this->_params['prize_id']]));
//
//            $given_prizes = json_decode($context->given_prizes, true);
//            if(empty($given_prizes) || !is_array($given_prizes)) $given_prizes = [];
//            $given_prizes[] = $this->_params['prize_id'];
//
//            $context->bonus_prizes = json_encode($prizes);
//            $context->given_prizes = json_encode($given_prizes);
//            $context->save();
//
//            UserGift::createForUser($user, $currency?? $user->currency, now()->addDays(14), $this->_params['prize_id'], $context->id);
//        }
        return false;
    }
}
