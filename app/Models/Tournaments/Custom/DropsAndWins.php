<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\Game;
use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class DropsAndWins extends CustomTournament
{
    public function getName(): string
    {
        return 'Drows & Wins';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = ['vs25wolfgold', 'vs25mustang', 'vs20fruitsw', 'vs25scarabqueen', 'vswaysrhino',
            'vs10bookoftut', 'vs20doghouse', 'vswaysdogs', 'vs25chilli', 'vs20fruitparty',
            'vs10bbbonanza', 'vswaysmadame', 'vs20goldfever', 'vs576treasures', 'vs40wildwest',
            'vs20olympgate', 'vswayshammthor', 'vs20midas', 'vswayslions', 'vswaysbufking',
            'vs10bookviking', 'vs20chickdrop', 'vs20fparty2', 'vs10nudgeit', 'vs50juicyfr'];
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
        return 'drops-and-wins';
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
