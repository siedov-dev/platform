<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\GamesProviders\GamesProvider;
use App\Helpers\Device;

class MoneyWonderwood extends CustomTournament
{

    public function getId(): string
    {
        return 'money-wonderwood';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/money-wonderwood/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/money-wonderwood/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'Money Wonderwood';
    }

    public function getDescription(): string
    {
        return __('tournament.money-wonderwood');
    }

    public function getPrizeFund(): string
    {
        return '40 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-08-24 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-08-30 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/ThreeOaks/4b201b08cf054ac69b84c0bdafb8c4b0',
            'sleg/ThreeOaks/f3840f26f909450e83f1f5da1427a4c7',
            'sleg/ThreeOaks/ce3f367fda473f6100fd7cdd10c3d9c507c22367',
            'sleg/ThreeOaks/cfa4f2692fbe48fd8adf21f309dfe8a4',
            'sleg/ThreeOaks/dc5b2b9a69cd4aa68f47b28c5273c006',
            'sleg/ThreeOaks/d7a5102557ab458fa196100db0a21f92',
            'sleg/ThreeOaks/15b8df3fb3534bcf98c545e9a4b90b40',
            'sleg/ThreeOaks/d13ce905f7dc4843836c9e4c65f90667',
            'sleg/ThreeOaks/e24e1dec72f04aa6b00feddd6108de41',
            'sleg/ThreeOaks/9f9703002bda47cc47e0f349e0269d7e0286ec02',
            'sleg/ThreeOaks/810fdf0ac3c246b1a172aff0e664d80d',
            'sleg/ThreeOaks/2aaf0b119814471d9bcbdc6acab04a00',
            'sleg/ThreeOaks/dc0da54714b8490a99aebe603a629e97',
            'sleg/ThreeOaks/85a135d934dfeeaefc01704368609461443c9286',
            'sleg/ThreeOaks/b2645118aa064e22a6a54482cd384c92',
            'sleg/ThreeOaks/09544657a3adf3c57d875e5a102bad621e8828b3',
            'sleg/ThreeOaks/7fa0f432cc114098b219f1f1c2f60ea3',
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
