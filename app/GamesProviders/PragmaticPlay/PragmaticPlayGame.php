<?php


namespace App\GamesProviders\PragmaticPlay;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class PragmaticPlayGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = "slots", DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = '/img/games-previews/pragmatic/'.$id.'.webp';
    }

    public function hasDemoMode() : bool
    {
        return $this->_originalData['demoGameAvailable']?? $this->_hasDemoMode;
    }

    public function isIframeForbidden() : bool
    {
        if(!isset($this->_originalData['gameTypeID'])) return false;
        return $this->_originalData['gameTypeID'] === "lg";
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
