<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class JuneCashDays extends CustomTournament
{

    public function getId(): string
    {
        return 'june-cash-days';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/june-cash-days/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/june-cash-days/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png';
    }

    public function getName(): string
    {
        return 'Playson June CashDays';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getPrizeFund(): string
    {
        return '80000 €';
    }

    public function getStartDateTime(): string
    {
        return '2023-06-01 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-06-08 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Playson/b9119020ad078955530637d996afb9a359f7db87',
            'sleg/Playson/d03b40aa384c2792bc0f35b3bb70bc0306bea4e8',
            'sleg/Playson/6d392fb2009dbdce0133ec99547fcbd31b831aeb',
            'sleg/Playson/dc2a86ac6099406291abe438bfb79c23',
            'sleg/Playson/8fb83d59c8abc7e7029b510f068f36597841d6bb',
            'sleg/Playson/424fcd6b1cf543889baa647bc24b7b71',
            'sleg/Playson/986b329bd86d83eafef215924c3354e0fa064cfa',
            'sleg/Playson/1b6e686f83ce044245af1bb8b0199f0099787612',
            'sleg/Playson/3f98ff6c577be4c77659c05135000baee7c69b2b',
            'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
            'sleg/Playson/4fd8bf44c0ee09f825abff65b8c173be5df14d08',
            'sleg/Playson/2cd70f841b844ac98a46d8aae9a0f949',
            'sleg/Playson/04ff21127ccb4b778d141afe253c3b00',
            'sleg/Playson/6e60cf4bf63f4cc9e5420461285b48d5ee4990da',
            'sleg/Playson/4347d4d867864a3db57591fb5c8b7175',
            'sleg/Playson/cc759e367826a39e1bdba714767bfd0d7b9e52e3',
            'sleg/Playson/2ac8815463ca8d3847aedf106560e9447142bb41',
            'sleg/Playson/ffbc23d788d98eb7212a7769129bb7362af4b4ce',

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
