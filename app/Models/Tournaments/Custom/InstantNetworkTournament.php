<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class InstantNetworkTournament extends CustomTournament
{

    public function getId(): string
    {
        return 'instant-network-tournament';
    }

    public function getImgUrl(): string
    {
        return '/img/banners/instant_network_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/banners/instant_network_' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay Instant Network Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.instantNetwork');
    }

    public function getPrizeFund(): string
    {
        return '7000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-01-25 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-01-31 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/0b943d7851deebc1fe854684eef1d75032234c8a',
            'sleg/Evoplay/c90eaac005c3d83ead96f8adcf037cff3f3f7c51',
            'sleg/Evoplay/65a1c5d2af7411d6be1f5205cb323df6d7932780',
            'sleg/Evoplay/e3fb99c163a0e90a08ab692ad67919a2e4e0c11c',
            'sleg/Evoplay/6e7a9fcfaafc086af8f16ca37fcc1c1395d54ef7'
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
