<?php
/**
 * @var \App\GamesProviders\SearchResult $games
 */

$cat = request()->get('categories', '');
$categories = explode(',', request()->get('category', ''));
$route = request()->route()->getName();
if ($route === 'games.slots') {
    $cat = 'slots';
} else if ($route === 'games.live') {
    $cat = 'live';
} else if ($route === 'games.table') {
    $cat = 'table';
} else if ($route === 'games.sport') {
    $cat = 'sport';
} else if (request()->route()->getName() === 'games.crash') {
    $cat = 'crash';
} else if (request()->route()->getName() === 'games.new') {
    $cat = 'new';
} else if (request()->route()->getName() === 'games.bonus') {
    $cat = 'bonus';
} else if (request()->route()->getName() === 'games.tournament') {
    $cat = 'tournament';
} else if (request()->route()->getName() === 'games.top') {
    $cat = 'top';
} else if (request()->route()->getName() === 'games.hot') {
    $cat = 'hot';
} else if (request()->route()->getName() === 'games.megaways') {
    $cat = 'megaways';
} else if (request()->route()->getName() === 'games.fs') {
    $cat = 'fs';
} else if (request()->route()->getName() === 'games.jackpot') {
    $cat = 'jackpot';
};
?>

{{--@include('phone.blocks.header-switch')--}}

<div id="games" class="width-limiter">

    <div class="games__categories">
        <a href="{{ route('games', [], false) }}" class="games__category content-link @if($cat === '' && empty($categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/all.svg"></div>
            {{ __('games.allGames') }}
        </a>
        <a href="{{ route('games.new', [], false) }}" class="games__category content-link @if($cat === 'new' || in_array('new', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/new.svg"></div>
            {{ __('games.new') }}
        </a>
        <a href="{{ route('games.bonus', [], false) }}" class="games__category content-link @if($cat === 'bonus' || in_array('bonus', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/bonus.svg"></div>
            {{ __('games.bonus') }}
        </a>
        <a href="{{ route('games.tournament', [], false) }}" class="games__category content-link @if($cat === 'tournament' || in_array('tournament', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/tournament.svg"></div>
            {{ __('games.tournamentGames') }}
        </a>
        <a href="{{ route('games.slots', [], false) }}" class="games__category content-link @if($cat === 'slots' || in_array('slots', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/slots.svg"></div>
            {{ __('games.slots') }}
        </a>
        <a href="{{ route('games.crash', [], false) }}" class="games__category content-link @if($cat === 'crash' || in_array('crash', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/crash.svg"></div>
            {{ __('games.crash') }}
        </a>
        <a href="{{ route('games.live', [], false) }}" class="games__category content-link @if($cat === 'live' || in_array('live', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/live.svg"></div>
            {{ __('games.live') }}
        </a>
        <a href="{{ route('games.table', [], false) }}" class="games__category content-link @if($cat === 'table' || in_array('table', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/table.svg"></div>
            {{ __('games.table') }}
        </a>
        <a href="{{ route('games.sport', [], false) }}" class="games__category content-link @if($cat === 'sport' || in_array('sport', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/vr.svg"></div>
            {{ __('games.sport') }}
        </a>

        <a href="{{ route('games.hot', [], false) }}" class="games__category content-link @if($cat === 'hot' || in_array('hot', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/hot.svg"></div>
            {{ __('games.hot') }}
        </a>
        <a href="{{ route('games.top', [], false) }}" class="games__category content-link @if($cat === 'top' || in_array('top', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/top.svg"></div>
            {{ __('games.popular') }}
        </a>
        <a href="{{ route('games.megaways', [], false) }}" class="games__category content-link @if($cat === 'megaways' || in_array('megaways', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/megaways.svg"></div>
            {{ __('games.megaWays') }}
        </a>
        <a href="{{ route('games.fs', [], false) }}" class="games__category content-link @if($cat === 'fs' || in_array('fs', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/fs.svg"></div>
            {{ __('games.freeSpins') }}
        </a>
        <a href="{{ route('games.jackpot', [], false) }}" class="games__category content-link @if($cat === 'jackpot' || in_array('jackpot', $categories))games__category_active @endif">
            <div class="games__category-icon"><img src="/img/ui/icons/games/jackpot.svg"></div>
            {{ __('games.jackpot') }}
        </a>
    </div>

    @include('phone.games.search-form')

    <div class="games-type">
        @if($onlyBonusGames?? false)
            @if($no_active_bonus)
                <div class="game-search-form__games-type-header">{{__('games.noActiveBonusGames')}}</div>
            @else
                <div class="game-search-form__games-type-header">{{__('games.activeBonusGames')}}</div>
            @endif
        @endif
        @if($onlyTournamentGames?? false)
            <div class="game-search-form__games-type-header">{{__('games.activeTournamentsGames')}}</div>
        @endif
    </div>

    <div class="games">

        <div id="games__items" class="games__items">
            @include('phone.games.items', ['games' => $games])
        </div>

        <div id="games__show-more-wrap" class="flex jc-center">
            @include('desktop.games.show-more', ['games' => $games])
        </div>
    </div>
    @include('phone.blocks.bottom-deposit-btn')
</div>
