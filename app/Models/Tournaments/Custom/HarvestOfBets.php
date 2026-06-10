<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class HarvestOfBets extends CustomTournament
{

    public function getId(): string
    {
        return 'harvest-of-bets';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/harvest-of-bets/tile.jpg';
        return '/img/tournaments/harvest-of-bets/tile_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/harvest-of-bets/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
        return '/img/tournaments/harvest-of-bets/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay Harvest OF Bets Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.harvestOfBets');
    }

    public function getPrizeFund(): string
    {
        return '12000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-09-19 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-09-30 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/110f544b4e7c7000bbb33590b1b1e2f3e11411df',
            'sleg/Evoplay/d78a3256fb5df8e4c16ef4e1136fe0400378182d',
            'sleg/Evoplay/4d24f9e6ec85b1a21ada149e4b76147dbb10aeb0',
            'sleg/Evoplay/97851c61d9594088a2e8c9538ac6b82a',
            'sleg/Evoplay/4d718570cb07c3ebc68f3766ff0c8b6cc0df4d71',
            'sleg/Evoplay/9d085886f7da7a44180aa7cec7e7f57fd206cedf',
            'sleg/Evoplay/7ad01bc8a4bcb615e4f45f70957c4c0d25c7706e',
            'sleg/Evoplay/94bb51852ab049e99837ad562e438806',
            'sleg/Evoplay/351ac3fc59931574c4de5367c0f3a8b5a04dc280',
            'sleg/Evoplay/e663d840b66fb3d9bad732d5bb39de409b7d5589',
            'sleg/Evoplay/bad98b300e37dc429548aeb7a3179c2efccbb110',
            'sleg/Evoplay/c0f6d8c1da144d20b65d05a89681aba6',
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
