<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class SpinMania extends CustomTournament
{

    public function getId(): string
    {
        return 'spin-mania';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/spin-mania/tile_' . app()->getLocale() . '.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/spin-mania/' . (Device::detect() === Device::DESKTOP ? 'wide-' : 'mobile-') . app()->getLocale() . '.png?t='.time();
    }

    public function getName(): string
    {
        return 'Playson SpinMania';
    }

    public function getDescription(): string
    {
        return __('tournament.SpinMania');
    }

    public function getPrizeFund(): string
    {
        return '30000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-05-26 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-05-29 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Playson/8d5f59fbdf8a026460aecad099a495289e8ebb57',
            'sleg/Playson/b9119020ad078955530637d996afb9a359f7db87',
            'sleg/Playson/871ee690837b14fc451e42f61c594916fbfdbe16',
            'sleg/Playson/d03b40aa384c2792bc0f35b3bb70bc0306bea4e8',
            'sleg/Playson/6d392fb2009dbdce0133ec99547fcbd31b831aeb',
            'sleg/Playson/9a908dd5014463bc494bb4052e32fe4eae1987e5',
            'sleg/Playson/fd4975dda93ab5138ddae77217bb55a9ed3091b1',
            'sleg/Playson/c275af2cd83feaee0252c4eae7f5d2f51a3d4d0a',
            'sleg/Playson/deba1669e73d429402aa031918f9500e3aa92d7c',
            'sleg/Playson/986b329bd86d83eafef215924c3354e0fa064cfa',
            'sleg/Playson/1b6e686f83ce044245af1bb8b0199f0099787612',
            'sleg/Playson/a1bb5ff572ed258612fd5575ad47a2af55ecb380',
//            'sleg/Playson/3f98ff6c577be4c77659c05135000baee7c69b2b', //Legend of Cleopatra Megaways
            'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
            'sleg/Playson/d86accf53707bab9986c4fba83bd5aab54cd7cb9',
            'sleg/Playson/cc759e367826a39e1bdba714767bfd0d7b9e52e3',
            'sleg/Playson/8c35806a327d76b99c31afeb9a7f71870e5e2624', //Sevens&Fruits: 20 Lines
            'sleg/Playson/2ac8815463ca8d3847aedf106560e9447142bb41',
            'sleg/Playson/54a8a0b23c8965f174af9ce65a3875b1d475a95e',
//            'sleg/Playson/54a8a0b23c8965f174af9ce65a3875b1d475a95e', //Wolf Power Megaways
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
