<?php
/**
 * @var \App\GamesProviders\GamesCollection $games
 * @var string $customClass
 */
?>

@if ($games->getCurrentPage() < $games->getTotalPages())
    <div class="show-more mt-3" data-total="{{ $games->getTotalGames() }}">
        <div class="show-more__progress-track">
            <div class="show-more__progress-bar" style="width: {{ round(($games->getCurrentPage() * $games->getPageSize())/$games->getTotalGames() * 100) }}%"></div>
        </div>
        <div class="show-more__text">{{ __('games.shown') }} {{ $games->getCurrentPage() * $games->getPageSize() }} {{ __('games.from') }} {{ $games->getTotalGames() }} {{ __('games.games') }}</div>
        <div class="show-more__button click-on-viewport @if(isset($customClass)) {{ $customClass }} @endif" data-page="{{ $games->getCurrentPage() }}" data-tag="{{ $tag }}">{{ __('games.showMore') }}</div>
    </div>
@endif
