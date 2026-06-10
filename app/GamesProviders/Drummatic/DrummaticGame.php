<?php


namespace App\GamesProviders\Drummatic;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;

class DrummaticGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = '/img/games-previews/drummatic/' . $id . '_235.jpg';
    }


    function getRealPlayUrl(): string
    {
        return "/play/drummatic/drummatic/$this->_id/real";
    }

    function getDemoPlayUrl(): string
    {
        return "/play/drummatic/drummatic/$this->_id/demo";
    }
}
