<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class WinterLuckyDrops extends CustomTournament
{

    public function getId(): string
    {
        return 'winter-lucky-drops';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/winter-lucky-drops/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/winter-lucky-drops/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Winter Lucky Drops';
    }

    public function getDescription(): string
    {
        return __('tournament.springLuckDrops');
    }

    public function getPrizeFund(): string
    {
        return '30 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-12-25 00:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-01-01 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $provider = GamesManager::getProvider(GamesProvider::GAME_SYSTEM_SLOTEGRATOR);
        return $provider->searchGames(['ThreeOaks']);
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }
}
