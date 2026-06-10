<?php


namespace App\UserCommands;


use App\Models\Bonuses\UserBonus;
use App\Models\BonusPatterns;
use App\MUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class CreateBonusFromParamsCmd extends UserCommand
{
    protected $name = 'Создать бонус';
    protected $description = 'Создать бонус из параметров';


    public function execute(?MUser $user = null, ?int $currency = null, $context = null)
    {
        if(!$user)$user = MUser::find(Auth::id());

        if(!$user){
            Log::error(__CLASS__.' no user');
            return false;
        }

        $bonus = new UserBonus();
        $bonus->type = $this->_params['bonus_type'];
        $bonus = UserBonus::convertByType($bonus);
        $bonus = $bonus->createFromParams($this->_params['bonus_type'], $user->id);
//        if ($user) {
//            $user_rank = $user->getRank($bonus->currency);
//            if($user_rank) $bonus->wagerDiminution($user_rank->getWagerDiminution());
//        }
        $bonus->save();

        return $bonus;
    }
}
