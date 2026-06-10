<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class WeeklyRaces extends CustomTournament
{

    public function getId(): string
    {
        return 'weekly-races';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/weekly-races/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/weekly-races/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png';
    }

    public function getName(): string
    {
        return 'Playson Weekly Races';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '40000 €';
    }

    public function getStartDateTime(): string
    {
        if (now()->timestamp >= strtotime('2023-06-19 00:00:00')) return '2023-06-22 00:00:01';
        return '2023-06-15 00:00:01';
    }

    public function getEndDateTime(): string
    {
        if (now()->timestamp >= strtotime('2023-06-19 00:00:00')) return '2023-06-25 00:00:01';
        return '2023-06-18 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();
        if (now()->timestamp >= strtotime('2023-06-19 00:00:00')) {
            $ids = [
                'sleg/Playson/b9119020ad078955530637d996afb9a359f7db87',
                'sleg/Playson/871ee690837b14fc451e42f61c594916fbfdbe16',
                'sleg/Playson/6d392fb2009dbdce0133ec99547fcbd31b831aeb',
                'sleg/Playson/a1bb5ff572ed258612fd5575ad47a2af55ecb380',
                'sleg/Playson/3f98ff6c577be4c77659c05135000baee7c69b2b',
                'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
                'sleg/Playson/4fd8bf44c0ee09f825abff65b8c173be5df14d08',
                'sleg/Playson/d86accf53707bab9986c4fba83bd5aab54cd7cb9',
                'sleg/Playson/6e60cf4bf63f4cc9e5420461285b48d5ee4990da',
                'sleg/Playson/2ac8815463ca8d3847aedf106560e9447142bb41',
            ];

        } else {
            $ids = [
                'sleg/Playson/d03b40aa384c2792bc0f35b3bb70bc0306bea4e8',
                'sleg/Playson/6d392fb2009dbdce0133ec99547fcbd31b831aeb',
                'sleg/Playson/fd4975dda93ab5138ddae77217bb55a9ed3091b1',
                'sleg/Playson/986b329bd86d83eafef215924c3354e0fa064cfa',
                'sleg/Playson/1b6e686f83ce044245af1bb8b0199f0099787612',
                'sleg/Playson/a1bb5ff572ed258612fd5575ad47a2af55ecb380',
                'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
                'sleg/Playson/4fd8bf44c0ee09f825abff65b8c173be5df14d08',
                'sleg/Playson/d86accf53707bab9986c4fba83bd5aab54cd7cb9',
                'sleg/Playson/6e60cf4bf63f4cc9e5420461285b48d5ee4990da',
            ];
        }


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
