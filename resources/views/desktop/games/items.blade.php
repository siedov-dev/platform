<?php
/**
 * @var \App\GamesProviders\SearchResult $games
 */
?>

@foreach ($games->getGames() as $game)
    @include('desktop.games.single-game', ['game' => $game])
@endforeach
