<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class CashRainTournament extends CustomTournament
{
    public function getName(): string
    {
        return 'Gameart\'s cash rain';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            '7813e797ebbd99422b2707ceee31549a56b48d0f',
            '5e3df895767153467e9f0514a2f371e8b99187c4',
            'fa71505d2ec7d2b1dbebd39c8188f2aa35f56bc5',
            '168447d1a0db6d951275b5d91a32bff389fa8191',
            'f98c17b429cf69dd5113e9c33adc9943',
        ];
        foreach ($ids as $id) {
            $game = GamesManager::getGameByCompoundId('sleg/GameArt/'.$id);
            if ($game !== null) {
                $collection->addGame($game);
            }
        }
        return $collection;
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/cash_rain.jpg';
    }

    public function getBannerUrl(): string
    {
        switch (Device::detect())
        {
            case Device::DESKTOP:
                return '/img/tournaments/cash_rain-banner_desktop.jpg';
            case Device::TABLET:
                return '/img/tournaments/cash_rain-banner_desktop.jpg';
            case Device::PHONE:
                return '/img/tournaments/cash_rain-banner_phone.jpg';
        }
        return '/img/tournaments/cash_rain-banner_desktop.jpg';
    }

    public function getPrizeFund(): string
    {
        return '10000 €';
    }

    public function getId(): string
    {
        return 'cash-rain';
    }

    public function getStartDateTime(): string
    {
        return '2021-11-11 08:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2021-11-18 23:59:59';
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }

    public function getDescription(): string
    {
        return __('tournament.cashRain');
    }
}
