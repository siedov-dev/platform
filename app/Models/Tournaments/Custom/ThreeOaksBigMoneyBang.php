<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class ThreeOaksBigMoneyBang extends CustomTournament
{

     public const prize_fund = '120 000 €';
     public const min_bid = '€0.3';

    public function getId(): string
    {
        return 'three-oaks-big-money-bang';
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
        return 'Big Money Bang';
    }

    public function getProvider(): string
    {
        return '3 Oaks';
    }

    public function getMinBid(): string
    {
        return self::min_bid;
    }

    public function getMinSpins(): int
    {
        return 200;
    }

    public function getDescription(): string
    {
        return __('tournament.' . self::getId());
    }

    public function getPrizeFund(): string
    {
        return self::prize_fund;
    }

    public function getStartDateTime(): string
    {
        return '2024-10-31 00:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-11-13 23:59:59';
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
            'sleg/ThreeOaks/41191faad640aaee9b9b336e9b181054459a3684',
            'sleg/ThreeOaks/eceb3b84155f472a929129611cc2f8b9',
            'sleg/ThreeOaks/685669127af045e5b74447dd3a1eb7a3',
            'sleg/ThreeOaks/06bb9a583d5f4f0b902bd8ee06f1f1da',
            'sleg/ThreeOaks/d7c3b3f4827a4a35bbc6d9e9741e4b9a',
            'sleg/ThreeOaks/27b887e9f4c9475ca80d2c1c680b1d08',
            'sleg/ThreeOaks/13475d8ae79a47b9ab864b8c99d1c41f',
            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/7925d4c8b4aa44a1929abd608d1ff874',
            'sleg/ThreeOaks/11496fee3b36431ba2f1650cdbb50082',
            'sleg/ThreeOaks/60a7223d84d24c41f7892d218461465f21fbeb16',
            'sleg/ThreeOaks/88c343ef4606e716ed0d50b5bba59aa57bcaf7b6',
            'sleg/ThreeOaks/621560b88d9b45f68a17a77ddb80b4c4',
            'sleg/ThreeOaks/07f30de5fca44b7ea7cee423898cf143',
            'sleg/ThreeOaks/5deb4306596e49f99778aa0afb362dc9',
            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
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
