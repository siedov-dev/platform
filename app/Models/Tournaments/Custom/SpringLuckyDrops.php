<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class SpringLuckyDrops extends CustomTournament
{

    public function getId(): string
    {
        return 'spring-lucky-drops';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/spring-lucky-drops/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/spring-lucky-drops/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Spring Lucky Drops';
    }

    public function getDescription(): string
    {
        return __('tournament.springLuckDrops');
    }

    public function getPrizeFund(): string
    {
        return '60 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-05-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-05-28 23:59:59';
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
