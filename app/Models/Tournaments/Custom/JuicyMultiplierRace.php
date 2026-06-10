<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class JuicyMultiplierRace extends CustomTournament
{

    public function getId(): string
    {
        return 'juicy-multiplier-race';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/juicy-multiplier-race/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/juicy-multiplier-race/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Juicy Multiplier Race';
    }

    public function getDescription(): string
    {
        return __('tournament.juicy-multiplier-race');
    }

    public function getPrizeFund(): string
    {
        return '50 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-07-27 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-07-31 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/ThreeOaks/2aaf0b119814471d9bcbdc6acab04a00',
            'sleg/ThreeOaks/61184a2e7cb34b4dab36215a370c073b',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/2e489fe424b74d8fb9101f1f998d0496',
            'sleg/ThreeOaks/564bf6b9c3e540feb50b6e2d8dd986205444b4b7',
            'sleg/ThreeOaks/abf4cf0a9cba7e0115967735a98da1a11a313277',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/62f8cca1448246d39dee4eab0bc7a9dc',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
            'sleg/ThreeOaks/d9fca05cae45d994c64f01c4cfb8e7e46e24e2a8',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
            'sleg/ThreeOaks/82806fb79449489f946ff6dcd1296f7c',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/9322d42c543b4ae2bea31c57c22644e3',
            '11111111111111111',
            '11111111111111111',
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
