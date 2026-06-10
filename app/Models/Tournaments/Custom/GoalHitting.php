<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class GoalHitting extends CustomTournament
{

    public function getId(): string
    {
        return 'goal-hitting';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/goal-hitting/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/goal-hitting/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg';
    }

    public function getName(): string
    {
        return 'Evoplay Hot Celebration Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.hotCelebration');
    }

    public function getPrizeFund(): string
    {
        return '10000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-11-17 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-11-23 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/0b943d7851deebc1fe854684eef1d75032234c8a',
            'sleg/Evoplay/fa611fb5296929fb8815a74fc724d0fc9d4a475c',
            'sleg/Evoplay/a0500369ff3ddf6d3f60afb73ae72bfcc4b203a2',
            'sleg/Evoplay/a5adf8a7c62911577dfff086a7b9716be8d95189',
            'sleg/Evoplay/fc5bd16daea3c798917a29f2ccf505adea494e5e',
            'sleg/Evoplay/5198f8ccfdf9f120eba385814fb4b4a04bd28105',
            'sleg/Evoplay/432824aba253452ab1e580ef384ac614',
            'sleg/Evoplay/ba4e0aba6580189adcef4589a6752761922b8edd',
        ];
        foreach ($ids as $id) {
            $game = GamesManager::getGameByCompoundId($id);
            if ($game !== null) {
                $collection->addGame($game);
            }
        }

        return $collection;
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }
}
