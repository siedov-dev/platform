<?php

namespace App\GamesProviders\SoftSwiss;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class SoftSwissGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = "slots", DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = "https://cdn.softswiss.net/i/s4/$groupId/$id.png";
    }

    public function getImageUrl() : string
    {
        return $this->_imageUrl;
    }

    public function hasDemoMode() : bool
    {
        return $this->_hasDemoMode;
    }

    function getRealPlayUrl(): string
    {
        return "/" . App::getLocale() . "/play/$this->_providerId/$this->_groupId/$this->_id/real";
    }

    function getDemoPlayUrl() : string
    {
        return "/" . App::getLocale() . "/play/$this->_providerId/$this->_groupId/$this->_id/demo";
    }
}
