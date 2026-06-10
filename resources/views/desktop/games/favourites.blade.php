<div class="width-limiter flex column" style="padding-top: 1rem">
    @if(auth()->user()?->getFavouriteGames() !== null)

        @if (count(auth()->user()?->getFavouriteGames()) === 0)

            <div class="no-records">
                <div class="no-records__title">{{ __('games.noFavouritesTitle') }}</div>
                <div class="no-records__hint">{{ __('games.noFavouritesHint') }}</div>
            </div>

        @else
            <div class="bonuses__list-title">{{ __('games.favouriteGames') }}</div>
            <div class="games-slider">
                <div id="games__items" class="games-slider__games">
                    @include('desktop.games.items', ['games' => auth()->user()->getFavouriteGames()])
                </div>
            </div>
        @endif

    @endif
</div>
