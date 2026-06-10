<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class EasterDrop extends CustomTournament
{

    public function getId(): string
    {
        return 'easter-drop';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/easter-drop/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/easter-drop/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Easter Drop';
    }

    public function getDescription(): string
    {
        return __('tournament.takeThePrize');
    }

    public function getPrizeFund(): string
    {
        return '250 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-04-03 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-04-16 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_SLOTEGRATOR);
        return $provider->searchGames(['Wazdan']);
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }
}
