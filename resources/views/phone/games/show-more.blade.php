<?php
/**
 * @var \App\GamesProviders\SearchResult $games
 * @var string $customClass
 */
?>

@if ($games->getCurrentPage() < $games->getTotalPages() && $games->getTotalPages() > 1)
    <div id="games__show-more" class="show-more mt-3" data-total="{{ $games->getTotalGames() }}" >
        <div class="show-more__progress-track">
            <div class="show-more__progress-bar" style="width: {{ round(($games->getCurrentPage() * $games->getPageSize())/($games->getTotalGames()?:1) * 100) }}%"></div>
        </div>
        <div class="show-more__text">{{ __('games.shown') }} {{ $games->getCurrentPage() * $games->getPageSize() }} {{ __('games.from') }} {{ $games->getTotalGames() }} {{ __('games.games') }}</div>
        <div class="show-more__button @if(isset($customClass)) {{ $customClass }} @endif" data-page="{{ $games->getCurrentPage() }}" onclick="document.dispatchEvent(new CustomEvent('showMoreGames'))">{{ __('games.showMore') }}</div>
        @include('desktop.games.auto-show-more')
    </div>
@endif
