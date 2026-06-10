<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class GainsAndCocktails extends CustomTournament
{

    public function getId(): string
    {
        return 'gain-and-cocktails';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/gain-and-cocktails/tile_' . app()->getLocale() . '.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/gain-and-cocktails/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Gains and Cocktails';
    }

    public function getDescription(): string
    {
        return __('tournament.gainsAndCocktails');
    }

    public function getPrizeFund(): string
    {
        return '30 000+ €';
    }

    public function getStartDateTime(): string
    {
        return '2022-08-04 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-08-10 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/ThreeOaks/5802603d6a4044109f0966613a1d5a17',
            'sleg/ThreeOaks/09544657a3adf3c57d875e5a102bad621e8828b3',
            'sleg/ThreeOaks/85a135d934dfeeaefc01704368609461443c9286',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/2e489fe424b74d8fb9101f1f998d0496',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/9322d42c543b4ae2bea31c57c22644e3',
            'sleg/ThreeOaks/5fb836fbac494caca33b024d924480e8',
            'sleg/ThreeOaks/aa7f8a2739ef47c09a58180443405c25',
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
