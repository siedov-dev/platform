<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class ChineCashFall extends CustomTournament
{

    public function getId(): string
    {
        return 'china-cash-fall';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/china-cash-fall/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/china-cash-fall/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'China CashFall';
    }

    public function getDescription(): string
    {
        return __('tournament.chinaCashFall');
    }

    public function getPrizeFund(): string
    {
        return '40 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-07-20 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-07-24 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/ThreeOaks/2aaf0b119814471d9bcbdc6acab04a00',
            'sleg/ThreeOaks/61184a2e7cb34b4dab36215a370c073b',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/bae512fc842b44cba6924e127ef09e2a',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/62f8cca1448246d39dee4eab0bc7a9dc',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/d9fca05cae45d994c64f01c4cfb8e7e46e24e2a8',
            'sleg/ThreeOaks/564bf6b9c3e540feb50b6e2d8dd986205444b4b7',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/5802603d6a4044109f0966613a1d5a17',
            'sleg/ThreeOaks/3b33b1fec97646899c58e90df6b71a84',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/82806fb79449489f946ff6dcd1296f7c',
            'sleg/ThreeOaks/d848381c9ffc42bbb00c3668fd0e624d',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
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
