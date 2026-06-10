<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class PumpkinFortune extends CustomTournament
{

    public function getId(): string
    {
        return 'pumpkin-fortune';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/pumpkin-fortune/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/pumpkin-fortune/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Pumpkin Fortune';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '30 000+ €';
    }

    public function getStartDateTime(): string
    {
        return '2022-10-31 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-11-01 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            //Green Chilli
            'sleg/ThreeOaks/a01877d807cf1331894f13d3a053bfe9096f5a28',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/faa6bf3527654560bdc105cab19dfe8d',
            'sleg/ThreeOaks/1dacf099c70d4488bd29ac803813ed05',
            'sleg/ThreeOaks/6f12a231efbf436a9e4822027fd7e46c',
            'sleg/ThreeOaks/7fa0f432cc114098b219f1f1c2f60ea3',
            'sleg/ThreeOaks/07fc6fd39d704e658cedf624a04db34a',
            'sleg/ThreeOaks/3b33b1fec97646899c58e90df6b71a84',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/5802603d6a4044109f0966613a1d5a17',
            'sleg/ThreeOaks/d848381c9ffc42bbb00c3668fd0e624d',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/2d94e3a3f9be486b8bd54ed17c79c9ff',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
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
