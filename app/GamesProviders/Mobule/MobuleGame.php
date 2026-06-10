<?php

namespace App\GamesProviders\Mobule;

use App\GamesProviders\DeviceType;
use App\GamesProviders\Game;
use Illuminate\Support\Facades\App;

class MobuleGame extends Game
{
    protected $_hasDemoMode = true;
    protected $_canChooseBalance = false;

    public function __construct(string $id, string $name, string $providerId, string $groupId, string $category = 'slots', DeviceType $deviceType = DeviceType::All)
    {
        parent::__construct($id, $name, $providerId, $groupId, $category, $deviceType);
        $this->_imageUrl = $this->_fallbackImageUrl;
    }

    public function getRealPlayUrl(): string
    {
        return '/' . App::getLocale() . "/play/mobule/$this->_groupId/$this->_id/real";
    }

    public function getDemoPlayUrl(): string
    {
        return '/' . App::getLocale() . "/play/mobule/$this->_groupId/$this->_id/demo";
    }

    public function getImageUrl(): string
    {
        return "https://effective-cdn.com/storage/static/CasinoMobule/{$this->getGroupId()}/528-326/{$this->getId()}.webp";
    }
}
