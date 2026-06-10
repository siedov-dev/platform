<style>
    #tv-bet-frame {
        border: none;
        width: 100vw !important;
        height: calc(100vh - 80px) !important;
        max-width: 100vw !important;
        max-height: calc(100vh - 80px) !important;
        background-color: black;
    }
    #game-wrapper{
        top: 80px !important;
        z-index: 550 !important;
    }
    #play__wager{
        margin-top: -5rem !important;
    }
</style>
<div id="game-wrapper" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
    <iframe src="{{ route('games.tv.frame', [], false) }}?gameId={{ request()->get('gameId', 1) }}" id="tv-bet-frame"></iframe>
</div>
<div id="play__wager" style="display: none;pointer-events: none;"></div>
@include('desktop.games.game_bonus_panel')
<script>
    document.getElementById('header').classList.add('tv-bet');
    document.querySelector('body').classList.add('tv-bet');
</script>
