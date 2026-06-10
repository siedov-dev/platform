<?php

namespace App\GamesProviders\BetProduction;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class BetProductionGame extends Game
{
    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = '/img/games-previews/bet-production/game_'.$id.'.webp';
    }

    function getRealPlayUrl(): string
    {
        return route('games.BetProduction', [
            'gameId' => $this->_id,
            'mode' => 'real',
            'compoundId' => $this->getCompoundId(),
        ]);
//        return "/" . App::getLocale() . '/games/bet-production?gameId='.$this->_id.'&mode=real&compoundId='.$this->getCompoundId();
    }

    function getDemoPlayUrl(): string
    {
        return route('games.BetProduction', [
            'gameId' => $this->_id,
            'mode' => 'demo',
            'compoundId' => $this->getCompoundId(),
        ]);
//        return "/" . App::getLocale() . '/games/bet-production?gameId='.$this->_id.'&mode=demo&compoundId='.$this->getCompoundId();
    }
}
