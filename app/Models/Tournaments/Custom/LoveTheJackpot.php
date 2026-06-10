<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class LoveTheJackpot extends CustomTournament
{

    public function getId(): string
    {
        return 'love-the-jackpot';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/love-the-jackpot/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/love-the-jackpot/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Love The Jackpot';
    }

    public function getDescription(): string
    {
        return __('tournament.lovethejackpot');
    }

    public function getPrizeFund(): string
    {
        return '2 500 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-02-05 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2024-02-25  23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Wazdan/023b9b17551843d0a182fb0afbc3a5cb',
            'sleg/Wazdan/7b0591746228536000b785db119645915613a463',
            'sleg/Wazdan/9f6d3699457584f0c72c0e2af534d86f684ef7fa',
            'sleg/Wazdan/600e0643ec4c4ac880b208636ed61124',
            'sleg/Wazdan/78272c4f9c2f243fb53c34a4a2d92e152e6d3ad5',
            'sleg/Wazdan/40735937e0524a53a4207dae00d4e929',
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
