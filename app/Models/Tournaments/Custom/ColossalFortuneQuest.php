<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class ColossalFortuneQuest extends CustomTournament
{

    public function getId(): string
    {
        return 'colossal-fortune-quest';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/colossal-fortune-quest/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/colossal-fortune-quest/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png?t='.time();
    }

    public function getName(): string
    {
        return 'Colossal Fortune Quest';
    }

    public function getDescription(): string
    {
        return __('tournament.fortuneQuest');
    }

    public function getPrizeFund(): string
    {
        return '40000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-10-10 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-10-16 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'qt/YGG/YGG-vikingsgoberzerkreloaded',
            'qt/YGG/YGG-mexomax',
            'qt/YGG/YGG-calaveracrush',
//            '',//Valley of the Gods 2
            'qt/YGG/YGG-goldenfishtank2gigablox',
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
