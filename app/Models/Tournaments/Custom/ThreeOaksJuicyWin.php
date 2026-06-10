<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class ThreeOaksJuicyWin extends CustomTournament
{

    public const prize_fund = '50 000 €';
    public const min_bid = '€0.5';

    public function getId(): string
    {
        return 'three-oaks-juicy-win';
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
        return 'Juicy Win';
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
        return 100;
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
        return '2024-08-15 09:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-08-19 23:59:59';
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
            'sleg/ThreeOaks/7b7e6269961e43cc9fd6856e28d21486',
            'sleg/ThreeOaks/11496fee3b36431ba2f1650cdbb50082',
            'sleg/ThreeOaks/49c987ec891244f0bddb23ff2c7da0e2',
            'sleg/ThreeOaks/13475d8ae79a47b9ab864b8c99d1c41f',
            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/06bb9a583d5f4f0b902bd8ee06f1f1da',
            'sleg/ThreeOaks/7925d4c8b4aa44a1929abd608d1ff874',
            'sleg/ThreeOaks/88c343ef4606e716ed0d50b5bba59aa57bcaf7b6',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/60a7223d84d24c41f7892d218461465f21fbeb16',
            'sleg/ThreeOaks/5deb4306596e49f99778aa0afb362dc9',
            'sleg/ThreeOaks/4eaa610663404a8d9ce4206fa65de0b4',
            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
            'sleg/ThreeOaks/07f30de5fca44b7ea7cee423898cf143',
            'sleg/ThreeOaks/cdf78a60060d49e2bcdf62111a118445',
            'sleg/ThreeOaks/ce3f367fda473f6100fd7cdd10c3d9c507c22367',
            'sleg/ThreeOaks/c1ed24aee9ec4fa8b7da020c14bd968f',
            'sleg/ThreeOaks/5deb4306596e49f99778aa0afb362dc9',
            'sleg/ThreeOaks/99b8589b7a5a46659344cc1e2bb6cadf',
            'sleg/ThreeOaks/cdf78a60060d49e2bcdf62111a118445',
            'sleg/ThreeOaks/5d8d3f9b5b86474471ccbff3e58c818cf88a1749',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/d7c3b3f4827a4a35bbc6d9e9741e4b9a',

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
