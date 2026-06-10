<?php

namespace App\GamesProviders\TVBet;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class TVBetGame extends Game
{
    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = '/img/games-previews/tv-bet/game_'.$id.'.webp';
    }

    function getRealPlayUrl(): string
    {
        return "/" . App::getLocale() . '/games/tv-bet?gameId='.$this->_id.'&mode=real&compoundId='.$this->getCompoundId();
    }

    function getDemoPlayUrl(): string
    {
        return "/" . App::getLocale() . '/games/tv-bet?gameId='.$this->_id.'&mode=demo&compoundId='.$this->getCompoundId();
    }
}
