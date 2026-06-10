<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class FruitParty extends CustomTournament
{

    public function getId(): string
    {
        return 'fruit-party';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/fruit-party/tile_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/fruit-party/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'GameArt Fruit Party';
    }

    public function getDescription(): string
    {
        return __('tournament.fruitParty');
    }

    public function getPrizeFund(): string
    {
        return '10000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-02-10 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-02-17 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/GameArt/5165099741db8d6fc798ffd0fa0c026b887dfd47',
            'sleg/GameArt/c4fe13af2cbd59202b1935d27cad18ff93d5f8d6',
            'sleg/GameArt/41ceec72dea05702aecc8b947b2453ae8cd35451',
            'sleg/GameArt/5e3df895767153467e9f0514a2f371e8b99187c4',
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
