<?php

namespace App\Models\Tournaments\Custom;

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;
use App\Helpers\Device;

class TakeThePrize extends CustomTournament
{

    public function getId(): string
    {
        return 'take-the-prize';
    }

    public function getImgUrl(): string
    {
        return '/img/tournaments/take-the-prize/tile.jpg';
    }

    public function getBannerUrl(): string
    {
        return '/img/tournaments/take-the-prize/' . (Device::detect() === Device::DESKTOP ? 'wide' : 'mobile') . '.jpg?t='.time();
    }

    public function getName(): string
    {
        return 'BetSoft Take The Prize Promotion';
    }

    public function getDescription(): string
    {
        return __('tournament.takeThePrize');
    }

    public function getPrizeFund(): string
    {
        return '70 000 €';
    }

    public function getStartDateTime(): string
    {
        return '2022-09-29 00:00:01';
    }

    public function getEndDateTime(): string
    {
        return '2022-10-09 23:59:59';
    }

    public function getAllowedGames(): GamesCollection
    {
        $collection = new GamesCollection();

        $ids = [
            'sleg/Betsoft/c4c673c5c5a9b4598921363a2432463bb372c5d2',
            'sleg/Betsoft/f7452fcf49e8ec80edddd160a14a4a42784485f8',
            'sleg/Betsoft/8437b1c2fc4b0d7c30deefffc097b1f079826702',
            'sleg/Betsoft/d9a0ecb3d745a18b7132a68a30785daf6014acf6',
            'sleg/Betsoft/b34b5acf1e5bd6987d330e0a2a8e1fc9c0761ec7',
            'sleg/Betsoft/d9ce44299fdc408899de6b28af74332310b72013',
            'sleg/Betsoft/5f42c94351c23421eefd5143a51c5705563f0ab5',
            'sleg/Betsoft/e6ef71639157661237e1d5cb3929c439c486312d',
            'sleg/Betsoft/c3b9b42e9e54584ac165b443a6b7b01aa7fa4793',
            'sleg/Betsoft/b99bc2e9dedbcdad377abce252f85e6bc473c372',
            'sleg/Betsoft/3ec582d29e81f2817fc3e8831e748260fbfd864e',
            'sleg/Betsoft/1d3b399de09537d244139c8ea4b939c8806c2c36',
            'sleg/Betsoft/a06b87d3cf919f3909e92365657ae0584b070625',
            'sleg/Betsoft/0e2709e16a5a114506d2841a3729b34c1f06e4ea',
            'sleg/Betsoft/18ab3eb98132e1205f3cc2d522e1e678f25d2a5f',
            'sleg/Betsoft/8fda2c82491ab6706ae4365124a460cdded91212',
            'sleg/Betsoft/bdff81ec0fb7d3cbd8f1c6690f8003c9837e0f95',
            'sleg/Betsoft/4a012f6bfc9c04cfb792ef06e8eec1f4d2772819',
            'sleg/Betsoft/89eb1d66254869d119bdff7f7bfb04d4de038441',
//            '',// kensei blades
//            '',// gemini joker
            'sleg/Betsoft/34463110ffc4b683072ca2a824af6ace233f2e85',
            'sleg/Betsoft/417c1727d8fc08a29ea6dda24459b20d05fd26b1',
            'sleg/Betsoft/f873e8c215c848f5430212906b32bb4ba00d89bb',
            'sleg/Betsoft/5152c15092584ca9de24edc53a0054f7938234a0',
            'sleg/Betsoft/d0b144bb40d4d3d89fbef162141a951599327648',
            'sleg/Betsoft/b65529957eb826f62d6889fdb84c7b1e0402f890',
            'sleg/Betsoft/c6b4cfa8de4f15e781ce1560e874adf4e5e36892',
            'sleg/Betsoft/f4ebfab49afe9f11b3835844726dc8aa28bba5d4',
            'sleg/Betsoft/5dbdc7c5bc01ab52be11a09b7b4c46f53eb85410',// miles
            'sleg/Betsoft/b4ce78f45cf5ec36749fd72b5a5c1f6fcdb3006b',
            'sleg/Betsoft/be08fc4a70677482513fa2fb8b80a7425a9baaa9',
            'sleg/Betsoft/1e99f486a542f214631615cd3a8ac51faaf348c0',
            'sleg/Betsoft/9905a36d9cf26a37dbb72aa5f01bdb2525a36401',
            'sleg/Betsoft/a936cf7a76bf008cc3283ecbc53d68aaa5fba1bd',
            'sleg/Betsoft/acdb6ef0f6d6ca5b99087b0c20907fba8765ab63',
            'sleg/Betsoft/49aa976fbd98310901a3e5443942fdc5e881ab4c',
            'sleg/Betsoft/e067ad85bc39cd730d4a0580c7866b83395378d8',
//            '',//wilds of fortune
            'sleg/Betsoft/9ea5a7bf77d604d160a7e001e604549b8c121565',
//            '',// captain quest
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
