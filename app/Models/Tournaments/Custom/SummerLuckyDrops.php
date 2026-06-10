<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class SummerLuckyDrops extends CustomTournament
{

    public function getId(): string
    {
        return 'summer-lucky-drops';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/summer-lucky-drops/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/summer-lucky-drops/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Summer Lucky Drops';
    }

    public function getDescription(): string
    {
        return __('tournament.summerLuckDrops');
    }

    public function getPrizeFund(): string
    {
        return '15 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-08-25 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-08-27 23:59:59';
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
