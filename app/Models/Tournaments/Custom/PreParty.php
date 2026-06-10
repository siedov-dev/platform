<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class PreParty extends CustomTournament
{

    public function getId(): string
    {
        return 'june-pre-party';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/pre-party/tile_' . app()->getLocale() . '.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/pre-party/' . (Device::detect() === Device::DESKTOP ? 'wide_' : 'mobile_') . app()->getLocale() . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Evoplay June pre-party Tournament';
    }

    public function getDescription(): string
    {
        return __('tournament.spinsOfLove');
    }

    public function getPrizeFund(): string
    {
        return '14000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-06-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-06-26 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/853621bd28178b7864ec19d003e73026f7f099d1',
            'sleg/Evoplay/6fe1d6a618ae8507b87840b431938154faa671f5', //CURSE OF THE PHARAOH
            'sleg/Evoplay/94bb51852ab049e99837ad562e438806', //
            'sleg/Evoplay/82516f4de72d7fd2b9e44590b5077264192d2713',
            'sleg/Evoplay/39effd0eb7fa4725079755a858dc0bbccedc33e0',
            'sleg/Evoplay/62191e4e53b9fd14da633870efa3d1a7e1cce9e5',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/cbfa63a19da17b6192bcc5a8de4f0fd3db7a886f',
            'sleg/Evoplay/2787cc7a208dc17981769f8dbb6c0b0c6f360100',
            'sleg/Evoplay/21d7d61a013cb3c6f37e98c3051e03fecfb36c05',
            'sleg/Evoplay/bad98b300e37dc429548aeb7a3179c2efccbb110',
            'sleg/Evoplay/8ea18974dba24c9ba9e465c69a17f76f',
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
