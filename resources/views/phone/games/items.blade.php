<?php
/**
 * @var \App\GamesProviders\SearchResult $games
 */
?>

@foreach ($games->getGames() as $game)
    @include('phone.games.single-game', ['game' => $game])
@endforeach

@if($vpn_remind??false)
    <script>
        setTimeout(() => {
            document.getElementById('vpn-recomendation').style.display = 'flex';
        }, 1000);
    </script>
@endif
