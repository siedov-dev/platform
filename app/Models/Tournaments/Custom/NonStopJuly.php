<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class NonStopJuly extends CustomTournament
{

    public function getId(): string
    {
        return 'playson-non-stop';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/playson-non-stop/preview.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/playson-non-stop/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png';
    }

    public function getName(): string
    {
        return 'Playson Non-Stop Drops & Races';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '6 000 000 €';
    }

    public function getStartDateTime(): string
    {
//        if (now()->timestamp >= strtotime('2023-06-19 00:00:00')) return '2023-06-22 00:00:01';
        return '2024-07-01 00:00:01';
    }

    public function getEndDateTime(): string
    {
//        if (now()->timestamp >= strtotime('2023-06-19 00:00:00')) return '2023-06-25 00:00:01';
        return '2025-06-30 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        $ids = [
            'sleg/Playson/53de5008a0004fd9828a2fc46e8b133d',
            'sleg/Playson/b9119020ad078955530637d996afb9a359f7db87',
            'sleg/Playson/d03b40aa384c2792bc0f35b3bb70bc0306bea4e8',
            'sleg/Playson/dc2a86ac6099406291abe438bfb79c23',
            'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
            'sleg/Playson/6918d7a211d040a9be8f09063c9cb805',
            'sleg/Playson/77638fd196194a8781d56e26c5647c71',
            'sleg/Playson/df9ccebe62d348a386d0b5f37315b5fe',
            'sleg/Playson/43479bfd8ed9414b81dd6c6d9e8e9297',
            'sleg/Playson/92822dad28e144a29fbf7b80ca444055',
            'sleg/Playson/6cc0372b7ad44ed29408659ca6610eba',
            'sleg/Playson/1eba29ab5aef4a6db60a08058082c389',
            'sleg/Playson/4fd8bf44c0ee09f825abff65b8c173be5df14d08',
            'sleg/Playson/6e60cf4bf63f4cc9e5420461285b48d5ee4990da',
            'sleg/Playson/4347d4d867864a3db57591fb5c8b7175',
            'sleg/Playson/0d1579c288206cb1aa840ef59f95ee74f5c16798',
            'sleg/Playson/871ee690837b14fc451e42f61c594916fbfdbe16',
            'sleg/Playson/ba1847055b1e496591dbc6f62135b7cb',
            'sleg/Playson/04ff21127ccb4b778d141afe253c3b00',
            'sleg/Playson/d86accf53707bab9986c4fba83bd5aab54cd7cb9',
            'sleg/Playson/2ac8815463ca8d3847aedf106560e9447142bb41',
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
