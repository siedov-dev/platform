<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class NewYear extends CustomTournament
{

    public function getId(): string
    {
        return 'new-year';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/new-year/new_year1.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/new-year/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'new_year') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'New Year';
    }

    public function getDescription(): string
    {
        return __('tournament.MysteryFall');
    }

    public function getPrizeFund(): string
    {
        return '50 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-12-11 11:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2023-12-28 11:00:00';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            'sleg/SmartSoft/bc048935acfd4956950bf0b2a29c6aff',
            'sleg/SmartSoft/9d102c4944c742acbb3ac49dad3bda1e',
            'sleg/SmartSoft/eb2fbec7b7095e687125e86536e22bf661867bec',
            'sleg/SmartSoft/9117a28ecefe54ebb9f812e1cbafdf0de9ace433',
            'sleg/SmartSoft/f6573dfe5225f0d3eb56fc99a4e0eca894268f1e',
            'sleg/SmartSoft/a77abccd4c6f7522fdcc97839ee460b729dc0489',
            'sleg/SmartSoft/eefafc820a06db4a2c80c326ac84dce995b8ffde',
            'sleg/SmartSoft/c00180ea21f844d7be05441fe42a93e7',
            'sleg/SmartSoft/3fd4c913128d45c0bc77cf822072c555',
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
