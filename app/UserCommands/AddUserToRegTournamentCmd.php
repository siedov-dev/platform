<?php


namespace App\UserCommands;


use App\Models\Tournaments\RegTournament;
use App\MUser;
use App\UserWallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated
 */
class AddUserToRegTournamentCmd extends UserCommand
{
    protected $name = 'Добавить в бонусный турнир';
    protected $description = 'Разрешить игроку участвовать в бонусном турнире';

    public function execute(?MUser $user = null, ?int $currency = null, $context = null):bool
    {

        if(!$user)$user = MUser::find(Auth::id());

        if(!$user){
            Log::error(__CLASS__.' no user');
            return false;
        }

        /** @var RegTournament $r_tnt */
        $reg_tnt = RegTournament::getCurrent($currency?? $user->currency);
        if($reg_tnt){
            $reg_tnt->addUser($user->id);
            $user->notify(__('bonus.bonus_tournament'), __('bonus.bonus_tournament_allowed'));
        }
        return true;
    }
}
