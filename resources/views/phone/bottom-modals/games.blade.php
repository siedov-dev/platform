@php
    $popular_groups = array_slice(\App\GamesProviders\GamesManager::getAllGroups(true), 0, 6);
@endphp
<div class="games-menu bottom-modal">
    <div class="content">
        <div class="pad">
            <h4 class="bottom-modal__title-small mb-1">{{__('footer.popular_providers')}}</h4>
            <nav class="popular-providers">
                @foreach($popular_groups as $group)
                    <a href="/games?providers={{$group->getProviderId()}}%7C{{$group->getId()}}&category={{implode(',',$group->getAllCategories())}}" class="menu__item">
                        <img src="{{$group->getIconUrl()}}">
                        <span class="menu__item-text">{{ $group->getName() }}</span>
                    </a>
                @endforeach
            </nav>
            <hr class="mt-1 menu__line">
            <nav class="bottom-modals__games-nav mt-1">
                <a href="/games" class="menu__item">
                    <img src="/img/ui/icons/games/all.svg">
                    <span class="menu__item-text">{{ __('menu.allGames') }}</span>
                </a>
                <a href="{{ route('games.new', [], false) }}" class="menu__item new-games" onclick="this.classList.remove('notify');">
                    <img src="/img/ui/icons/games/new.svg">
                    <span class="menu__item-text">{{ __('games.new') }}</span>
                    <div class="has-new">!</div>
                </a>
                <a href="{{ route('games.crash', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/crash.svg">
                    <span class="menu__item-text">{{ __('games.crash') }}</span>
                </a>
                <a href="{{ route('games.hot', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/hot.svg">
                    <span class="menu__item-text">{{ __('games.hot') }}</span>
                </a>
                <a href="{{ route('games.jackpot', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/jackpot.svg">
                    <span class="menu__item-text">{{ __('games.jackpot') }}</span>
                </a>
                <a href="{{ route('games.megaways', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/megaways.svg">
                    <span class="menu__item-text">{{ __('games.megaWays') }}</span>
                </a>
                <a href="{{ route('games.slots', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/slots.svg">
                    <span class="menu__item-text">{{ __('games.slots') }}</span>
                </a>
                <a href="{{ route('games.live', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/live.svg">
                    <span class="menu__item-text">{{ __('games.live') }}</span>
                </a>
                <a href="{{ route('games.table', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/table.svg">
                    <span class="menu__item-text">{{ __('games.table') }}</span>
                </a>
                <a href="/{{ app()->getLocale() }}/games?providers=tv-bet%7Ctv-bet" class="menu__item">
                    <img src="/img/ui/icons/games/tv-bet.svg">
                    <span class="menu__item-text">{{ __('games.tvbet') }}</span>
                </a>
                <a href="{{ route('games.sport', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/vr.svg">
                    <span class="menu__item-text">{{ __('games.sport') }}</span>
                </a>
                <a href="{{ route('games.fs', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/fs.svg">
                    <span class="menu__item-text">{{ __('games.freeSpins') }}</span>
                </a>
                <a href="{{ route('games.bonus', [], false) }}" class="menu__item bonus-games" onclick="this.classList.remove('notify');">
                    <img src="/img/ui/icons/games/bonus.svg">
                    <span class="menu__item-text">{{ __('games.bonus') }}</span>
                    <div class="has-new">!</div>
                </a>
                <a href="{{ route('games.tournament', [], false) }}" class="menu__item tournament-games" onclick="this.classList.remove('notify');">
                    <img src="/img/ui/icons/games/tournament.svg">
                    <span class="menu__item-text">{{ __('games.tournamentGames') }}</span>
                    <div class="has-new">!</div>
                </a>
                <a href="{{ route('games.top', [], false) }}" class="menu__item">
                    <img src="/img/ui/icons/games/top.svg">
                    <span class="menu__item-text">{{ __('games.popular') }}</span>
                </a>
            </nav>
        </div>
    </div>
    <div class="bottom-menu__close-button">&times;</div>
</div>
