<?php


namespace App\GamesProviders\B2BSlots;


use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class B2BSlotsGame extends Game
{
    protected $_hasDemoMode = false;
    protected $_canChooseBalance = true;


    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = "slots", DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
//        $this->_imageUrl = '/img/games-previews/b2bslots/'.$id.'.webp';
    }

    public function hasDemoMode() : bool
    {
        return false;
    }

    public function isIframeForbidden() : bool
    {
        return false;
    }


    function getRealPlayUrl(): string
    {
        return "/" . App::getLocale() . "/play/$this->_providerId/$this->_groupId/$this->_id/real";
    }

    function getDemoPlayUrl() : string
    {
        return "/" . App::getLocale() . "/play/$this->_providerId/$this->_groupId/$this->_id/demo";
    }

    public function getImageUrl(): string
    {
        return 'https://int.apichannel.cloud'.($this->_fallbackImageUrl?? '');
    }
}
