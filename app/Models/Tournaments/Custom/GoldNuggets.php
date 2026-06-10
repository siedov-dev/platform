<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class GoldNuggets extends CustomTournament
{

    public function getId(): string
    {
        return 'gold-nuggets';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/gold-nuggets/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/gold-nuggets/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Bucket of Gold Nuggets';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '50 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-03-23 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-03-27 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            //Boom Boom Gold
            'sleg/ThreeOaks/9cf6042fa3e341c09c4618d6a1b30c15',
            'sleg/ThreeOaks/564bf6b9c3e540feb50b6e2d8dd986205444b4b7',
            'sleg/ThreeOaks/817094fc97d1477cb0a061a9647102d7',
            'sleg/ThreeOaks/85a135d934dfeeaefc01704368609461443c9286',
            'sleg/ThreeOaks/9322d42c543b4ae2bea31c57c22644e3',
            'sleg/ThreeOaks/62f8cca1448246d39dee4eab0bc7a9dc',
            'sleg/ThreeOaks/4e26e0d34af64a59a4b07e734e970830',
            'sleg/ThreeOaks/08448cac46004cab8628c24b3c5bda8d',
            'sleg/ThreeOaks/31ec41be2c7e4651a470088c45a4b2b8',
            'sleg/ThreeOaks/67c021e40bcf4808b9f5572147dfc53c',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/82806fb79449489f946ff6dcd1296f7c',
            'sleg/ThreeOaks/11c40eb85287578758ca29ff932408d69e5995b3',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/d9fca05cae45d994c64f01c4cfb8e7e46e24e2a8',
            'sleg/ThreeOaks/5802603d6a4044109f0966613a1d5a17',
            'sleg/ThreeOaks/3b33b1fec97646899c58e90df6b71a84',
            'sleg/ThreeOaks/2f07cc0de97b496dab214b8f70dbe261',
            'sleg/ThreeOaks/d848381c9ffc42bbb00c3668fd0e624d',
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
