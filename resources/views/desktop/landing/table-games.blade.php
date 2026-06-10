<div class="games-slider">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <img src="/img/landing/cards.svg" class="games-slider__icon">
            <div>
                <div class="games-slider__title">{{ __('landing.tableGames') }}</div>
                <div class="flex">
                    <div class="games-slider__total-number">{{ \App\GamesProviders\GamesManager::getTotalTableGames() }}+</div>&nbsp;
                    <div class="games-slider__total-text">{{ __('landing.nGames') }}!</div>
                </div>
            </div>
        </div>

        <div class="games-slider__controls">
            <a href="{{ route('games.table', [], false) }}" class="games-slider__all-button">{{ __('landing.all') }} ({{ \App\GamesProviders\GamesManager::getTotalTableGames() }})</a>
            <div class="games-slider__arrow-left slider__disabled" data-target="#landing__table-games"></div>
            <div class="games-slider__arrow-right" data-target="#landing__table-games"></div>
        </div>
    </div>

    <div id="landing__table-games" class="games-slider__games_scrollable">
        @foreach ($tableGames as $game)
            @include('desktop.games.single-game', ['game' => $game])
        @endforeach
        <a href="{{ route('games.table', [], false) }}" class="slider-all-games">
            <div class="arrow-right"></div>
            <div class="mt-2">
                {{ __('landing.all') }}
            </div>
        </a>
    </div>
</div>
