<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class DoubleLuck extends CustomTournament
{

    public function getId(): string
    {
        return 'double-luck';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/double-luck/title.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/double-luck/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Double Luck';
    }

    public function getDescription(): string
    {
        return __('tournament.double-luck');
    }

    public function getPrizeFund(): string
    {
        return '15 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-11-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2024-11-17 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

//        $ids = [
//            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
//            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
//            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
//            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
//            'sleg/ThreeOaks/60a7223d84d24c41f7892d218461465f21fbeb16',
//            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
//            'sleg/ThreeOaks/07f30de5fca44b7ea7cee423898cf143',
//            'sleg/ThreeOaks/5d8d3f9b5b86474471ccbff3e58c818cf88a1749',
//            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
//            'sleg/ThreeOaks/ce3f367fda473f6100fd7cdd10c3d9c507c22367',
//        ];
//        foreach ($ids as $id) {
//            $game = GamesManager::getGameByCompoundId($id);
//            if ($game !== null) {
//                $collection->addGame($game);
//            }
//        }

        return $collection;
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }
}
