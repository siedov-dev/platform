<?php


namespace App\GamesProviders\Slotegrator;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class SlegGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl =  '/img/games-previews/sleg/'. $groupId.'/'.$id.'.webp';
    }

    function getRealPlayUrl(): string
    {
        return "/" . App::getLocale() . "/play/sleg/$this->_groupId/$this->_id/real";
    }

    function getDemoPlayUrl() : string
    {
        return "/" . App::getLocale() . "/play/sleg/$this->_groupId/$this->_id/demo";
    }


    public function setFallbackImageUrl(string $url): void
    {
        $url = str_ireplace('gis.slotegrator.com', 'vcasinos.xyz', $url);
        parent::setFallbackImageUrl($url);
    }
}
