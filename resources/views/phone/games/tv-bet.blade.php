
<style>
    #tv-bet-frame {
        border: none;
        width: 100vw !important;
        height: calc(100vh - 90px) !important;
        max-width: 100vw !important;
        max-height: calc(100vh - 90px) !important;
        background-color: black;
    }
    #game-wrapper{
        top: 80px !important;
    }
</style>
{{--<div class="play__buttons mr-2">--}}
{{--    <div class="play__button" title="{{ __('games.fullscreen') }}" onclick="toggleFullscreen(document.getElementById('game-wrapper'), false); toggleFullscreen(document.getElementById('game-frame'), true)">--}}
{{--        <img src="/img/ui/icons/game/full-screen.svg">--}}
{{--    </div>--}}
{{--    <div class="play__b utton play__button-close ml-1" title="{{ __('games.close') }}"><img src="/img/ui/icons/game/close.svg"></div>--}}
{{--</div>--}}
<div id="game-wrapper" style="background-color: black;" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
    <iframe webkitallowfullscreen="false" mozallowfullscreen="false" allowfullscreen="false" src="{{ route('games.tv.frame', [], false) }}?gameId={{ request()->get('gameId', 1) }}" id="tv-bet-frame"></iframe>
</div>
<div id="play__wager" style="display: none; pointer-events: none; top: 8rem;"></div>
<script>
    document.querySelector('body').classList.add('tv-bet');
    {{--    setTimeout(function () {--}}
{{--        toggleFullscreen(document.getElementById('game-wrapper'), true)--}}
{{--    }, 1000);--}}
</script>

@include('desktop.games.game_bonus_panel')
