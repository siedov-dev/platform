<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class HotCelebration extends CustomTournament
{

    public function getId(): string
    {
        return 'hot-celebration';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/hot-celebration/tile_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/hot-celebration/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay Hot Celebration Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.hotCelebration');
    }

    public function getPrizeFund(): string
    {
        return '15000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-08-11 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-08-22 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/434357222e8fa313054e57a030eafe1f513ce8d0',
            'sleg/Evoplay/6fe1d6a618ae8507b87840b431938154faa671f5',
            'sleg/Evoplay/4d24f9e6ec85b1a21ada149e4b76147dbb10aeb0',
            'sleg/Evoplay/abb6a4a2d4ed870b08ffebd71ed52fd1c9ba062e',
            'sleg/Evoplay/00a2df60b0c44756a42e9cc995ba43bb',
            'sleg/Evoplay/ce6a0d91febd3fd5cba7992dd4a7c550b838276d',
            'sleg/Evoplay/62191e4e53b9fd14da633870efa3d1a7e1cce9e5',
            'sleg/Evoplay/9d085886f7da7a44180aa7cec7e7f57fd206cedf',
            'sleg/Evoplay/2787cc7a208dc17981769f8dbb6c0b0c6f360100',
            'sleg/Evoplay/21d7d61a013cb3c6f37e98c3051e03fecfb36c05',
            'sleg/Evoplay/7e32665e03cd4728bdd5967a9e61c85d',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
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
