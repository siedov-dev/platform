<?php


namespace App\UserCommands;


use App\MUser;
use Illuminate\Support\Facades\Log;
use JsonSerializable;

/**
 * @deprecated
 */
class UserCommand implements UserCommandInterface, JsonSerializable
{
    const UCMD_ADD_TO_REG_TNT = 1;
    const UCMD_BONUS_FROM_PATTERN = 2;
    const UCMD_GIVE_BONUS_PRIZE = 3;

    protected $name = 'base class';
    protected $description = 'base class';

    protected $_params = [];

    public function __construct(array $data = [])
    {
        $this->_params = $data;
    }

    /**
     * create command
     * @param array $data command params in array
     *                  ['type' => UserCommand::UCMD_BONUS_FROM_PATTERN, 'pattern_id' => $ptn_id]
     * @return UserCommandInterface|null
     * @throws \Exception
     */
    public static function create(array $data):?UserCommandInterface
    {
        if(!isset($data['type'])){
            throw new \Exception('Can`t create command, no type '.var_export($data));
            Log::error('Can`t create command, no type '.var_export($data));
            return null;
        }

        switch ($data['type']){
            case self::UCMD_ADD_TO_REG_TNT:
                return new AddUserToRegTournamentCmd($data);
                break;
            case self::UCMD_BONUS_FROM_PATTERN:
                return new CreateBonusFromPattern($data);
                break;
            case self::UCMD_GIVE_BONUS_PRIZE:
                return new ActivateBonusPrize($data);
                break;
        }

        throw new \Exception('Can`t create command, not found '.var_export($data));
        Log::error('Can`t create command, not found '.var_export($data));
        return null;
    }

    public function getName():string
    {
        return $this->name;
    }

    public function getDescription():string
    {
        return $this->description;
    }

    public function execute(?MUser $user = null, ?int $currency = null, $context = null)
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->_params;
    }
}
