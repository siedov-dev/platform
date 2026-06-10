<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class SpookyFortuneQuest extends CustomTournament
{

    public function getId(): string
    {
        return 'spooky-fortune-quest';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/spooky-fortune-quest/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/spooky-fortune-quest/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png?t='.time();
    }

    public function getName(): string
    {
        return 'Spooky Fortune Quest';
    }

    public function getDescription(): string
    {
        return __('tournament.fortuneQuest');
    }

    public function getPrizeFund(): string
    {
        return '50000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-10-28 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-11-06 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'qt/YGG/YGG-vikingsgoberzerkreloaded',
            'qt/YGG/YGG-mexomax',
            'qt/YGG/YGG-goldenfishtank2gigablox',
            'qt/YGG/YGG-championoftheunderworld',
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
