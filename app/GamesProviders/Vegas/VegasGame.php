<?php


namespace App\GamesProviders\Vegas;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class VegasGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = '/img/games-previews/vegas/'. $groupId.'/'.$id.'.webp';
    }


    function getRealPlayUrl(): string
    {
        return "/" . App::getLocale() . "/play/vegas/$this->_groupId/$this->_id/real";
    }

    function getDemoPlayUrl() : string
    {
        return "/" . App::getLocale() . "/play/vegas/$this->_groupId/$this->_id/demo";
    }
}
