<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class MaiTaiMadness extends CustomTournament
{

    public function getId(): string
    {
        return 'mai-tai-madness';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/mai-tai-madness/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/mai-tai-madness/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg';
    }

    public function getName(): string
    {
        return 'Mai Tai Madness';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '70 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2024-05-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2024-05-28 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/97851c61d9594088a2e8c9538ac6b82a',
            'sleg/Evoplay/cd39d62b6f12418f8bcf00a41b6d6e39',
            'sleg/Evoplay/d6ce67c8b2d81fccd4288bd80c10aae7bcee6e7b',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/c1bc781f8a225e4be9bc583e237582fd3c7b24b3',
            'sleg/Evoplay/ba4e0aba6580189adcef4589a6752761922b8edd',
            'sleg/Evoplay/432824aba253452ab1e580ef384ac614',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
            'sleg/Evoplay/2787cc7a208dc17981769f8dbb6c0b0c6f360100',
            'sleg/Evoplay/abb6a4a2d4ed870b08ffebd71ed52fd1c9ba062e',
            'sleg/Evoplay/8515ccc358434568b04502256e3626dd',
            'sleg/Evoplay/21d7d61a013cb3c6f37e98c3051e03fecfb36c05',
            'sleg/Evoplay/8e8fc2bb56bd137da3613425b6c8a43781c52b6d',
            'sleg/Evoplay/d2f611e2690741dd8b07b0b8d9eb7102',
            'sleg/Evoplay/644a3d09eafd58d2d1b54f0ae76c2b15c1f44532',
            'sleg/Evoplay/101e700ca8f24bd0b8eec72daa50dc58',
            'sleg/Evoplay/354ea5843d41423c94bf7dad5b9541f7',
            'sleg/Evoplay/af1399ec295d46099048402fa96a93fd',
            'sleg/Evoplay/0d68354c44776707e31268b6afbcd55aebfc618a',
            'sleg/Evoplay/8b97149e2b5214d1f62e7f8a7ea1cff0d98bd22d',
            'sleg/Evoplay/0d50b6550a44468286be71dea2480fc2',
            'sleg/Evoplay/642913a4aa614f3a8b974cb6cafbab78',
            'sleg/Evoplay/dc8be7dd5c964542920a759ea383bbae',
            'sleg/Evoplay/611ba8148e6e4eb19cf9791f9c783fe7',
            'sleg/Evoplay/2832849155b8459daff1d4f4f0cfe136',
            'sleg/Evoplay/adefa8081c6e44bba86fa9b358699e6f',
            'sleg/Evoplay/ecc93ee281ac40249a8bd91e24e4286c',
            'sleg/Evoplay/cbfa63a19da17b6192bcc5a8de4f0fd3db7a886f',
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
