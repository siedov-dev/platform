@if(!in_array(request()->route()->getName(), [
    'slc.treasures',
    'slc.tasks',
    'slc.shop',
    'slc.user-items',
    'slc.achievements',
    'landing',
    'referralSystem',
    'hallOfFame',
    'tournaments',
    'tournaments.info',
]))
    <div class="switch-panel">
        <div class="header-switch">
            <a href="{{ route('games.live', [], false) }}" class="content-link @if(request()->route()->getName() === 'games.live') active @endif">
                <img src="/img/icons/menu2/live.png">
                <span class="menu__item-text">{{ __('menu.live') }}</span>
            </a>
            {{--            <a href="{{\App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl()}}" class="sport">--}}
            {{--                <img src="/img/icons/menu2/sport.png">--}}
            {{--                <span class="menu__item-text">{{ __('menu.bet-production') }}</span>--}}
            {{--            </a>--}}
            <a href="{{ route('landing', [], false) }}" class="content-link @if(request()->route()->getName() !== 'games.live') active @endif">
                <img src="/img/icons/menu2/slots.png">
                <span class="menu__item-text">{{ __('mainMenu.casino') }}</span>
            </a>
        </div>
    </div>
@endif
