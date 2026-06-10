<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class DropsAndWinsLive extends CustomTournament
{

    public function getName(): string
    {
        return 'Drows & Wins: LIVE';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            '401', '404', '405', '411', '413', '402', '403', '412', '414', '415', '301', '302',
            '303', '304', '305', '511', '512', '513', '514', '515', '516', '517', '518', '519',
            '520', '901', '521', '522', '523', '524', '525', '526', '527', '528', '529', '530',
            '539', '538', '537', '536', '535', '801', '225', '201', '206', '221', '222', '224',
            '223', '203', '227', '204'
        ];
        foreach ($ids as $id) {
            $game = GamesManager::getGameByCompoundId('pragplay/pragmatic/'.$id);
            if ($game !== null) {
                $collection->addGame($game);
            }
        }
        return $collection;
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/drops_and_wins.jpg';
    }

    public function getBannerUrl(): string
    {
        switch (Device::detect())
        {
            case Device::DESKTOP:
                return '/img/tournaments/drops-and-wins-banner_desktop.jpg';
            case Device::TABLET:
                return '/img/tournaments/drops-and-wins-banner_desktop.jpg';
            case Device::PHONE:
                return '/img/tournaments/drops-and-wins-banner_phone.jpg';
        }
        return '/img/tournaments/drops-and-wins-banner_desktop.jpg';
    }

    public function getPrizeFund(): string
    {
        return '500000€';
    }

    public function getId(): string
    {
        return 'drops-and-wins-live';
    }

    public function getStartDateTime(): string
    {
        return '2021-08-25 19:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2021-09-22 19:00:00';
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }

    public function getDescription(): string
    {
        return __('tournament.dropsAndWins');
    }
}
