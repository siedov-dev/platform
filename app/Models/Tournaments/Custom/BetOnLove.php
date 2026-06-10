<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class BetOnLove extends CustomTournament
{

    public function getId(): string
    {
        return 'bet-on-love';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/bet-on-love/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/bet-on-love/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Bet On Love';
    }

    public function getDescription(): string
    {
        return __('tournament.betonlove');
    }

    public function getPrizeFund(): string
    {
        return '50 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-02-06 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2024-02-15 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/fb5e828668b0f226a55fdfa92b0b34bfbad04720',
            'sleg/Evoplay/97851c61d9594088a2e8c9538ac6b82a',
            'sleg/Evoplay/d6ce67c8b2d81fccd4288bd80c10aae7bcee6e7b',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/c1bc781f8a225e4be9bc583e237582fd3c7b24b3',
            'sleg/Evoplay/423139f7a1224119217a12ebd95edb91439878b7',
            'sleg/Evoplay/86f8a137869a4e22fe93c45c1c92c93505fefab7',
            'sleg/Evoplay/110f544b4e7c7000bbb33590b1b1e2f3e11411df',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
            'sleg/Evoplay/2787cc7a208dc17981769f8dbb6c0b0c6f360100',
            'sleg/Evoplay/351ac3fc59931574c4de5367c0f3a8b5a04dc280',
            'sleg/Evoplay/0062a49ecdc0946ed71270c3fee4f7dd63ace8db',
            'sleg/Evoplay/0d68354c44776707e31268b6afbcd55aebfc618a',
            'sleg/Evoplay/af1399ec295d46099048402fa96a93fd',
            'sleg/Evoplay/dafb7dedbb6551776702120b9adc140e6a881674',
            'sleg/Evoplay/acd4ddeefb8241a2af1c7ff9f0ffb37e',
            'sleg/Evoplay/853621bd28178b7864ec19d003e73026f7f099d1',
            'sleg/Evoplay/ecc93ee281ac40249a8bd91e24e4286c',
            'sleg/Evoplay/18ae758735094924a38486931e92805a',
            'sleg/Evoplay/abb6a4a2d4ed870b08ffebd71ed52fd1c9ba062e',
            'sleg/Evoplay/21d7d61a013cb3c6f37e98c3051e03fecfb36c05',
            'sleg/Evoplay/d44f7cedd3a486bc8a4e209fc460bc5ba49d3c73',
            'sleg/Evoplay/66297147dedad70bf20294dc2b605367a997878b',
            'sleg/Evoplay/563f8f2aafb5461b8258006d512f5736',
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
