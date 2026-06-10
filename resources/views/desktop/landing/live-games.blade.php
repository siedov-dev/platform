<div class="games-slider">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <img src="/img/landing/live-games.svg" class="games-slider__icon">
            <div>
                <div class="games-slider__title">{{ __('landing.liveGames') }}</div>
                <div class="flex">
                    <div class="games-slider__total-number">{{ \App\GamesProviders\GamesManager::getTotalLiveGames() }}+</div>&nbsp;
                    <div class="games-slider__total-text">{{ __('landing.nGames') }}!</div>
                </div>
            </div>
        </div>

        <div class="games-slider__controls">
            <a href="{{ route('games.live', [], false) }}" class="games-slider__all-button">{{ __('landing.all') }} ({{ \App\GamesProviders\GamesManager::getTotalLiveGames() }})</a>
            <div class="games-slider__arrow-left slider__disabled" data-target="#landing__live-games"></div>
            <div class="games-slider__arrow-right" data-target="#landing__live-games"></div>
        </div>
    </div>

    <div id="landing__live-games" class="games-slider__games_scrollable">
        @foreach ($liveGames as $game)
            @include('desktop.games.single-game', ['game' => $game])
        @endforeach
        <a href="{{ route('games.live', [], false) }}" class="slider-all-games">
            <div class="arrow-right"></div>
            <div class="mt-2">
                {{ __('landing.all') }}
            </div>
        </a>
    </div>
</div>
