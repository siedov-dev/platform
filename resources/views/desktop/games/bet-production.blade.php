@php
$locale = \Illuminate\Support\Facades\App::getLocale();
if ($locale === 'sk') $locale = 'en';
//$locale = app()->getLocale();
$subsection = '/sports/live/overview';
if (request()->get('gameId') === 'line') $subsection = '/sports/line';
if (request()->get('gameId') === 'live') $subsection = '/sports/live/overview';
if (request()->get('gameId') === 'e-sport') $subsection = '/e-sports/overview';
@endphp
<style>
    #bet-production-frame {
        border: none;
        width: 100vw !important;
        height: calc(100vh - 80px) !important;
        max-width: 100vw !important;
        max-height: calc(100vh - 80px) !important;
        background-color: #1A1944;
    }
    #game-wrapper{
        top: 80px !important;
        z-index: 550 !important;
    }
    #play__wager{
        margin-top: -3rem !important;
    }
</style>
<div class="play__buttons mr-2">
    <div class="play__button" title="{{ __('games.fullscreen') }}" onclick="toggleFullscreen(document.getElementById('game-wrapper'), true);">
        <img src="/img/ui/icons/game/full-screen.svg">
    </div>
{{--    <div class="play__b button play__button-close ml-1" title="{{ __('games.close') }}"><img src="/img/ui/icons/game/close.svg"></div>--}}
</div>

<div id="game-wrapper" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
    <iframe src="https://iframe6.pixplay.live{{$subsection}}?lng={{$locale}}&authToken={{$token}}&themeMode='dark'" id="bet-production-frame"></iframe>
</div>
{{--<div id="play__wager" style="display: none;pointer-events: none;"></div>--}}
{{--<div id="play__wager" style="display: none; pointer-events: none;"></div>--}}
@include('desktop.games.game_bonus_panel')
<script>
    (() => {
        document.getElementById('header').classList.add('sport');
        document.querySelector('body').classList.add('sport');
        let chatBtn = document.getElementById('supportTrigger');
        if (chatBtn) chatBtn.style.display = 'none';
    })();
</script>
