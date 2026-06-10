<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class BetTrills extends CustomTournament
{

    public function getId(): string
    {
        return 'bet-trills';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/bet-trills/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/bet-trills/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay Bet Trills';
    }

    public function getDescription(): string
    {
        return __('tournament.harvestOfBets');
    }

    public function getPrizeFund(): string
    {
        return '10000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-05-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-05-24 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/8e8fc2bb56bd137da3613425b6c8a43781c52b6d',
            'sleg/Evoplay/c1bc781f8a225e4be9bc583e237582fd3c7b24b3',
            'sleg/Evoplay/97851c61d9594088a2e8c9538ac6b82a',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/d6ce67c8b2d81fccd4288bd80c10aae7bcee6e7b',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
            'sleg/Evoplay/d3a2d27bfc818a3ac8829b31994ab023cfaa24e5',
            'sleg/Evoplay/8515ccc358434568b04502256e3626dd',
            'sleg/Evoplay/94bb51852ab049e99837ad562e438806',
            'sleg/Evoplay/23a128b3e7de30cdb57d7e33450946723ebae79b',
            'sleg/Evoplay/d44f7cedd3a486bc8a4e209fc460bc5ba49d3c73',
            'sleg/Evoplay/ecc93ee281ac40249a8bd91e24e4286c',
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
