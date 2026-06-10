<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class FireworksOfFortune extends CustomTournament
{

    public function getId(): string
    {
        return 'fireworks-of-fortune';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/fireworks-of-fortune/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/fireworks-of-fortune/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Fireworks of Fortune';
    }

    public function getDescription(): string
    {
        return __('tournament.MysteryFall');
    }

    public function getPrizeFund(): string
    {
        return '100 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-12-28 09:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-01-02 23:00:00';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            'sleg/ThreeOaks/6c36348efaf650cb73e0ef7c29712d0dfe3d1801',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/d9fca05cae45d994c64f01c4cfb8e7e46e24e2a8',
            'sleg/ThreeOaks/13475d8ae79a47b9ab864b8c99d1c41f',
            'sleg/ThreeOaks/61184a2e7cb34b4dab36215a370c073b',
            'sleg/ThreeOaks/2aaf0b119814471d9bcbdc6acab04a00',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/3b33b1fec97646899c58e90df6b71a84',
            'sleg/ThreeOaks/82806fb79449489f946ff6dcd1296f7c',
            'sleg/ThreeOaks/aa530d687875a8400e8b7e1df5ce9c21a49afa6a',
            'sleg/ThreeOaks/ce3f367fda473f6100fd7cdd10c3d9c507c22367',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/2d94e3a3f9be486b8bd54ed17c79c9ff',
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
