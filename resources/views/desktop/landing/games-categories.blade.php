<?php
$categories =[
    \App\GamesProviders\GameTag::Top,
    \App\GamesProviders\GameTag::New,
    \App\GamesProviders\GameTag::Crash,
    \App\GamesProviders\GameTag::Hot,
    \App\GamesProviders\GameTag::Jackpot,
    \App\GamesProviders\GameTag::MegaWays,
    \App\GamesProviders\GameTag::FreeSpins,
];
?>

<div class="categories-wrapper">

    <div class="lnd-categories">

        @foreach($categories as $c)
            @switch($c->value)
                @case('new')
                <!-- New games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=new" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/new.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.new') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalNewGames() }}</div>
                    </div>
                @break
                @case('crash')
                <!-- crash games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=crash" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/crash.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.crash') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalCrashGames() }}</div>
                    </div>
                @break
                @case('hot')
                <!-- Hot games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=hot" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/hot.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.hot') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalHotGames() }}</div>
                    </div>
                @break
                @case('top')
                <!-- Popular games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=top" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/top.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.popular') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalPopularGames() }}</div>
                    </div>
                @break
                @case('jackpot')
                <!-- Jackpot games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=jackpot" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/jackpot.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.jackpot') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalJackpotGames() }}</div>
                    </div>
                @break
                @case('megaways')
                <!-- Mega-ways games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=megaways" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/megaways.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.megaWays') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalMegaWaysGames() }}</div>
                    </div>
                @break
                @case('fs')
                <!-- Free-spins games -->
                    <div data-url="{{ route('gamesByTag', [], false) }}?tag=fs" class="lnd-categories__item">
                        <img src="/img/icons/game_categories/fs.png" class="lnd-categories__item-img">
                        <div class="lnd-categories__item-title">{{ __('landing.freeSpins') }}</div>
                        <div class="lnd-categories__item-hint">{{ \App\GamesProviders\GamesManager::getTotalFreeSpinsGames() }}</div>
                    </div>
                    @break
            @endswitch
        @endforeach
    </div>

    <div class="landing-search">
        @include('desktop.landing.search-form', ['pageSize' => 60, 'categories' => null,])
    </div>
</div>

<div class="games">
    <div id="games__items" class="games__items games-slider__games">
    </div>
    <div id="games__show-more-wrap" class="flex jc-center">
    </div>
</div>
