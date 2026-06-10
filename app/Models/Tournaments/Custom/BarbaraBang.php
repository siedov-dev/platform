<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class BarbaraBang extends CustomTournament
{

    public function getId(): string
    {
        return 'barbara-bang';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/tile.png';
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
        return 'Hot Summer Drop';
    }

    public function getDescription(): string
    {
        return __('tournament.barbara-bang');
    }

    public function getPrizeFund(): string
    {
        return '50 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-06-06 00:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-06-15 23:45:00';
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
