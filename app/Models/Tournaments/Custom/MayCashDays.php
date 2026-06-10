<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class MayCashDays extends CustomTournament
{

    public function getId(): string
    {
        return 'may-cash-days';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/may-cash-days/tile.png';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/may-cash-days/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.png';
    }

    public function getName(): string
    {
        return 'Playson May CashDays';
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
        return '2023-05-01 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2023-05-08 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Playson/b9119020ad078955530637d996afb9a359f7db87',
            'sleg/Playson/e5ef653e347f411180202b71978de0b6',
            'sleg/Playson/871ee690837b14fc451e42f61c594916fbfdbe16',
            'sleg/Playson/6d392fb2009dbdce0133ec99547fcbd31b831aeb',
            'sleg/Playson/fd4975dda93ab5138ddae77217bb55a9ed3091b1',
            'sleg/Playson/424fcd6b1cf543889baa647bc24b7b71',
            'sleg/Playson/7e14168883e94c47aca3142a7566ae8b',
            'sleg/Playson/986b329bd86d83eafef215924c3354e0fa064cfa',
            'sleg/Playson/477833b5c067ec871d8acc9ff20d81bafcfe47b0',
            'sleg/Playson/a1bb5ff572ed258612fd5575ad47a2af55ecb380',
            'sleg/Playson/3f98ff6c577be4c77659c05135000baee7c69b2b',
            'sleg/Playson/07454036211f25c6619061f78db019b095382f9c',
            'sleg/Playson/4fd8bf44c0ee09f825abff65b8c173be5df14d08',
            'sleg/Playson/2cd70f841b844ac98a46d8aae9a0f949',
            'sleg/Playson/04ff21127ccb4b778d141afe253c3b00',
            'sleg/Playson/6e60cf4bf63f4cc9e5420461285b48d5ee4990da',
            'sleg/Playson/d86accf53707bab9986c4fba83bd5aab54cd7cb9',
            'sleg/Playson/4347d4d867864a3db57591fb5c8b7175',
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
