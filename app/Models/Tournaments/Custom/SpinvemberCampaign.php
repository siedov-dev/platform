<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class SpinvemberCampaign extends CustomTournament
{

     public const prize_fund = '120 000 €';
     public const min_bid = '€0.3';

    public function getId(): string
    {
        return 'spinvember-campaign';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/title.jpg';
    }

    public function cabinetBannerUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/' . (Device::detect() === Device::DESKTOP ? 'wide_k' : 'mobile_k') . '.jpg?t='.time();
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/' . self::getId() . '/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Spinvember Campaign ';
    }

    public function getProvider(): string
    {
        return 'Evoplay';
    }

    public function getMinBid(): string
    {
        return self::min_bid;
    }

    public function getDescription(): string
    {
        return __('tournament.' . self::getId());
    }

    public function getPrizeFund(): string
    {
        return self::prize_fund;
    }

    public function getStartDateTime(): string
    {
        return '2024-11-08 00:00:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-11-19 23:59:59';
    }

    public function getStartDate(): string
    {
        return now()->parse($this->getStartDateTime())->format('Y-m-d');
    }

    public function getEndDate(): string
    {
        return now()->parse($this->getEndDateTime())->format('Y-m-d');
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Evoplay/351ac3fc59931574c4de5367c0f3a8b5a04dc280',
            'sleg/Evoplay/432824aba253452ab1e580ef384ac614',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/abb6a4a2d4ed870b08ffebd71ed52fd1c9ba062e',
            'sleg/Evoplay/c1bc781f8a225e4be9bc583e237582fd3c7b24b3',
            'sleg/Evoplay/ef026ee6a4bd49aca7274906c147efc3',
            'sleg/Evoplay/d6ce67c8b2d81fccd4288bd80c10aae7bcee6e7b',
            'sleg/Evoplay/af1399ec295d46099048402fa96a93fd',
            'sleg/Evoplay/3a7f10ea836f4a5ab68add59cd86105f',
            'sleg/Evoplay/f4b154791488499b9c72098ff9d90e36',
            'sleg/Evoplay/cee715ae23ff427fac39faf5e63d1b38',
            'sleg/Evoplay/8dd1f96b25dd4a4ba0eae3500c211e4d',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
            'sleg/Evoplay/8c86e30311884a97904c851698b351e4',
            'sleg/Evoplay/b0396a38c9a644e39a3b311f5be8e16c',
            'sleg/Evoplay/003ede987d29433787199da5344a7478',
            'sleg/Evoplay/56cb37c5f18a414787a09f4e961ece4c',
            'sleg/Evoplay/7b9226f4148240aeaf4dc3778e57b4a1',
        ];

//        foreach ($ids as $id) {
//            $game = GamesManager::getGameByCompoundId($id);
//            if ($game !== null) {
//                $collection->addGame($game);
//            }
//        }

        return $collection;
    }

    public function isActive(): bool
    {
        return time() > strtotime($this->getStartDateTime()) && time() < strtotime($this->getEndDateTime());
    }
}
