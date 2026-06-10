<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class SpinsOfLove extends CustomTournament
{

    public function getId(): string
    {
        return 'spins-of-love';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/spins-of-love/tile_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/spins-of-love/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay Spins Of Love Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.spinsOfLove');
    }

    public function getPrizeFund(): string
    {
        return '12000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-02-08 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-02-19 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/434357222e8fa313054e57a030eafe1f513ce8d0',
            'sleg/Evoplay/94bb51852ab049e99837ad562e438806',
            'sleg/Evoplay/7e32665e03cd4728bdd5967a9e61c85d',
            'sleg/Evoplay/39effd0eb7fa4725079755a858dc0bbccedc33e0',
            'sleg/Evoplay/6fe1d6a618ae8507b87840b431938154faa671f5',
            'sleg/Evoplay/80ad3e45df4503fc5b22c40e2ce131f5c0c1edde',
            'sleg/Evoplay/1fbe6f3e4eee7120dfaa2edda6ebafd94908456d',
            'sleg/Evoplay/d8a131f322c15b2a6c2243bd21cfb49a4807fef9',
            'sleg/Evoplay/9d085886f7da7a44180aa7cec7e7f57fd206cedf',
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
