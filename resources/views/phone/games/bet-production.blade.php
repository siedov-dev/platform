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
        width: 100%;
        height: calc(100% - 60px);
        padding: 0;
        margin: 0;
    }
    #bet-game-wrapper{
        /*margin-top: -50px;*/
        width: 100%;
        height: calc(100vh - 105px - 40px);
        top: 105px !important;
        background-color: #1A1944;
    }
    .bottom-menu__green-button{
        top: 1rem !important;
    }
    body{
        background-color: #1A1944 !important;
    }
    #layout-content-wrap,#layout-content{
        background-color: #1A1944 !important;
        background-image: none; !important;
    }
    .sport-header{
        display: flex;
        width: 100%;
        justify-content: space-evenly;
        height: 40px;
    }
</style>
<div id="play__wager" style="display: none; pointer-events: none; top: 3rem;"></div>

<div id="bet-game-wrapper" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
    <div class="sport-header header-switch" onclick="document.getElementById('header').classList.remove('sport');">
        <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('live') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'live') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
            <img src="/img/icons/menu/live.png" class="button__icon">
            <span class="button__text">{{ __('menu.sport-live') }}</span>
        </a>

        <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('line') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'line') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
            <img src="/img/icons/menu/table.png" class="button__icon">
            <span class="button__text">{{ __('menu.sport-line') }}</span>
        </a>

        <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('e-sport') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'e-sport') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
            <img src="/img/icons/menu/vr.png" class="button__icon">
            <span class="button__text">{{ __('menu.sport-e-sports') }}</span>
        </a>
    </div>
    <iframe src="https://iframe6.pixplay.live{{$subsection}}?lng={{$locale}}&authToken={{$token}}&themeMode='dark'" id="bet-production-frame"></iframe>
    <div style="height: 80px;"></div>
</div>
<script>
    document.querySelector('body').classList.add('sport');
    // document.querySelector('.bottom-menu').classList.add('show-sport');
</script>
{{--@include('desktop.games.game_bonus_panel')--}}
{{--<script>--}}
{{--    (() => {--}}
{{--        let chatBtn = document.getElementById('supportTrigger');--}}
{{--        if (chatBtn) chatBtn.style.display = 'none';--}}
{{--    })();--}}
{{--</script>--}}
