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
class CreateBonusFromPattern extends UserCommand
{
    protected $name = 'Выдать бонус из шаблонов';
    protected $description = 'Выдать бонус из шаблонов';


    public function execute(?MUser $user = null, ?int $currency = null, $context = null)
    {
        if (!$user) $user = MUser::find(Auth::id());

        if (!$user) {
            Log::error(__CLASS__ . ' no user');
            return false;
        }

        /** @var BonusPatterns $pattern */
        $pattern = BonusPatterns::find($this->_params['pattern_id']);

        return $pattern->createForUser($user, $currency);
    }

}
