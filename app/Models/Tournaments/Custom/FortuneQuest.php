<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class FortuneQuest extends CustomTournament
{

    public function getId(): string
    {
        return 'fortune-quest';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/fortune-quest/tile_' . app()->getLocale() . '.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/fortune-quest/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.png?t='.time();
    }

    public function getName(): string
    {
        return 'Grand Fortune Quest';
    }

    public function getDescription(): string
    {
        return __('tournament.fortuneQuest');
    }

    public function getPrizeFund(): string
    {
        return '30000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-09-19 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-09-25 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            //Sabers and Monsters
            'qt/YGG/YGG-raptordoublemax',
            'qt/YGG/YGG-vikingsgotovalhalla',
            'qt/YGG/YGG-cazinozeppelinreloaded',
            'qt/YGG/YGG-multifly',
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
