<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class SparklingSpins extends CustomTournament
{

    public function getId(): string
    {
        return 'sparkling-spins';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/sparkling-spins/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/sparkling-spins/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Sparkling Spins';
    }

    public function getDescription(): string
    {
        return __('tournament.MysteryFall');
    }

    public function getPrizeFund(): string
    {
        return '75 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-12-20 09:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2023-12-25 23:00:00';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            'sleg/ThreeOaks/6c36348efaf650cb73e0ef7c29712d0dfe3d1801',
            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/5d8d3f9b5b86474471ccbff3e58c818cf88a1749',
            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
            'sleg/ThreeOaks/07f30de5fca44b7ea7cee423898cf143',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/60a7223d84d24c41f7892d218461465f21fbeb16',
            'sleg/ThreeOaks/62f8cca1448246d39dee4eab0bc7a9dc',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/d9fca05cae45d994c64f01c4cfb8e7e46e24e2a8',
            'sleg/ThreeOaks/13475d8ae79a47b9ab864b8c99d1c41f',
            'sleg/ThreeOaks/61184a2e7cb34b4dab36215a370c073b',
            'sleg/ThreeOaks/2aaf0b119814471d9bcbdc6acab04a00',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/3b33b1fec97646899c58e90df6b71a84',
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
