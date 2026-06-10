<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class MysteryFall extends CustomTournament
{

    public function getId(): string
    {
        return 'mystery-fall';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/mystery-fall/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/mystery-fall/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Mystery Fall';
    }

    public function getDescription(): string
    {
        return __('tournament.MysteryFall');
    }

    public function getPrizeFund(): string
    {
        return '500 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-12-28 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-12-29 23:59:59';
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
