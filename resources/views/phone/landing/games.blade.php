<?php

use App\GamesProviders\GamesCollection;
use App\GamesProviders\GamesManager;

//if (!isset($slotGames)) {
//    $slotGames = new GamesCollection();
////    foreach (config(app()->getLocale().'.game_systems.topSlotGames') as $gameCompoundId) {
//    foreach (\App\Models\LandingGame::getGamesByCategory('topSlotGames') as $gameCompoundId) {
//        $game = GamesManager::getGameByCompoundId($gameCompoundId);
//        if ($game !== null) $slotGames->addGame($game);
//    }
//    $slotGames->setPageSize(25);
//}


if (!isset($tableGames)) {
    $tableGames = GamesManager::gamesByLandingGroup('topTableGames');
//    foreach (\App\Models\LandingGame::getGamesByCategory('topTableGames') as $gameCompoundId) {
//        $game = GamesManager::getGameByCompoundId($gameCompoundId);
//        if ($game !== null) $tableGames->addGame($game);
//    }
//    $tableGames->setPageSize(25);
}

if (!isset($liveGames)) {
    $liveGames = GamesManager::gamesByLandingGroup('topLiveGames');
//    foreach (\App\Models\LandingGame::getGamesByCategory('topLiveGames') as $gameCompoundId) {
//        $game = GamesManager::getGameByCompoundId($gameCompoundId);
//        if ($game !== null) $liveGames->addGame($game);
//    }
//    $liveGames->setPageSize(25);
}


if (!isset($topGames)) {
    $topGames = GamesManager::getGamesByTag(\App\GamesProviders\GameTag::Top->value);
//    $topGames->setPageSize(30);
}


if (!isset($newGames)) {
    $newGames = GamesManager::getGamesByTag(\App\GamesProviders\GameTag::New->value);
//    $newGames->setPageSize(30);
}


$data = [
    'top' => [
        'games' => $topGames,
        'totalGames' => $topGames->count(),
    ],
//    'slots' => [
//        'games' => $slotGames,
//        'totalGames' => GamesManager::getTotalSlotGames(),
//    ],
    'new' => [
        'games' => $newGames,
        'totalGames' => $newGames->count(),
    ],
    'table' => [
        'games' => $tableGames,
        'totalGames' => $tableGames->count(),
    ],
    'live' => [
        'games' => $liveGames,
        'totalGames' => $liveGames->count(),
    ],
];

$categories = ['top', 'new', 'live', 'table'];

?>

@foreach ($categories as $category)
    @include('phone.landing.'.$category.'-games', $data[$category])
@endforeach
