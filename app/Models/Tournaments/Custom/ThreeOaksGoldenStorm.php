<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class ThreeOaksGoldenStorm extends CustomTournament
{

    public function getId(): string
    {
        return 'three-oaks-golden-storm';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/title.jpg';
    }

     public function cabinetBannerUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/' . (Device::detect() === Device::DESKTOP ? 'wide_k' : 'mobile_k') . '.jpg?t='.time();
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Golden Storm';
    }

    public function getProvider(): string
    {
        return '3 Oaks';
    }

    public function getMinBid(): string
    {
        return '€0.2';
    }

    public function getMinSpins(): int
    {
        return 150;
    }

    public function getDescription(): string
    {
        return __('tournament.' . self::getId());
    }

    public function getPrizeFund(): string
    {
        return '40 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-07-18 09:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-07-22 23:59:59';
    }

    public function getStartDate(): string
    {
        return now()->parse($this->getStartDateTime())->format('Y-m-d');
    }

    public function getEndDate(): string
    {
        return now()->parse($this->getEndDateTime())->format('Y-m-d');
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/ThreeOaks/aa530d687875a8400e8b7e1df5ce9c21a49afa6a',
            'sleg/ThreeOaks/85a135d934dfeeaefc01704368609461443c9286',
            'sleg/ThreeOaks/5d8d3f9b5b86474471ccbff3e58c818cf88a1749',
            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/60a7223d84d24c41f7892d218461465f21fbeb16',
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
