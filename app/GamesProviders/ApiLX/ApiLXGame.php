<?php

namespace App\GamesProviders\ApiLX;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class ApiLXGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = false;

    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
//        $this->_imageUrl = $this->_fallbackImageUrl;
    }

    public function getRealPlayUrl(): string
    {
        return '/' . App::getLocale() . "/play/apilx/$this->_groupId/$this->_id/real";
    }

    public function getDemoPlayUrl(): string
    {
        return '/' . App::getLocale() . "/play/apilx/$this->_groupId/$this->_id/demo";
    }
}
