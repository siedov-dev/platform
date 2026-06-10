<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class EvoplaySuperCup extends CustomTournament
{

    public const prize_fund = '110 000 €';
    public const min_bid = '€0.2';
    public const min_spins = 150;

    public function getId(): string
    {
        return 'evoplay-super-cup';
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
        return 'Super Cup';
    }

    public function getProvider(): string
    {
        return 'Evoplay';
    }

    public function getMinBid(): string
    {
        return self::min_bid;
    }

    public function getMinSpins(): int
    {
        return self::min_spins;
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
        return '2024-08-06 00:01:00';
    }

    public function getEndDateTime(): string
    {
        return '2024-08-15 23:59:59';
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
            'sleg/Evoplay/97851c61d9594088a2e8c9538ac6b82a',
            'sleg/Evolpay/0b943d7851deebc1fe854684eef1d75032234c8a',
            'sleg/Evoplay/d6ce67c8b2d81fccd4288bd80c10aae7bcee6e7b',
            'sleg/Evoplay/9f75bcff6c7e447d8ece26330d637d4a',
            'sleg/Evoplay/c1bc781f8a225e4be9bc583e237582fd3c7b24b3',
            'sleg/Evoplay/adefa8081c6e44bba86fa9b358699e6f',
            'sleg/Evoplay/b0a19fbf7e29eca7c0f4e4cf7f478113e3c9af8f',
            'sleg/Evoplay/21d7d61a013cb3c6f37e98c3051e03fecfb36c05',
            'sleg/Evoplay/cee715ae23ff427fac39faf5e63d1b38',
            'sleg/Evoplay/028dc9a2f01e48fa814a7a7698cac5de',
            'sleg/Evoplay/d2508be85f774afea95b606a4706db4a',
            'sleg/Evoplay/351ac3fc59931574c4de5367c0f3a8b5a04dc280',
            'sleg/Evoplay/0062a49ecdc0946ed71270c3fee4f7dd63ace8db',
            'sleg/Evoplay/af1399ec295d46099048402fa96a93fd',
            'sleg/Evoplay/0d68354c44776707e31268b6afbcd55aebfc618a',
            'sleg/Evoplay/ef026ee6a4bd49aca7274906c147efc3',
            'sleg/Evoplay/2b73bc89adea4a658c9c2fb3cb1b7e1e',
            'sleg/Evoplay/8dd1f96b25dd4a4ba0eae3500c211e4d',
            'sleg/Evoplay/f4b154791488499b9c72098ff9d90e36',
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
