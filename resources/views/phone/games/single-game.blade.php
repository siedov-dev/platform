<?php
/**
 * @var \App\GamesProviders\Game $game
 */
?>
<div class="game slider__item @if($game->isTestMode()) test-mode @endif">
{{--    @if($game->isNew())--}}
{{--        <div class="game__badge">New</div>--}}
{{--    @elseif($game->isInTop())--}}
{{--        <div class="game__badge">Top</div>--}}
{{--    @endif--}}
    <div class="game__veil">
        <div class="game__name">{{ $game->getName() }}</div>
        @if(auth()->check())
            <div class="game__button game__play-real" data-url="{{ $game->getRealPlayUrl() }}@if(request()->get('category') === 'bonus' || str_ends_with(request()->url(), "/games/bonus"))?balance=bonus @endif"><div class="triangle"></div></div>
        @else
            <div class="game__button game__play-real" onclick="document.getElementById('login-button').click()"><div class="triangle"></div></div>
        @endif
        @if($game->hasDemoMode())
            <div class="game__button game__play-demo @if($game->getCategory() === 'live' || !$game->hasDemoMode()) demo-forbidden @endif"
                 data-game-id="{{$game->getCompoundId()}}"
                 data-url="{{ $game->getDemoPlayUrl() }}">Demo</div>
        @endif
    </div>
    <img src="{{ $game->getImageUrl() }}" class="game__image">
</div>
