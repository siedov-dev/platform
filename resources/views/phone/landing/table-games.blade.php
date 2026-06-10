<div class="games-slider">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <div>
                <div class="games-slider__title">{{ __('landing.tableGames') }}</div>
            </div>
        </div>

{{--        <div class="games-slider__controls">--}}
{{--            <div class="games-slider__arrow-left slider__disabled slider__prev" data-target="#landing__table-games"></div>--}}
{{--            <div class="games-slider__arrow-right slider__next" data-target="#landing__table-games"></div>--}}
{{--        </div>--}}
    </div>

    <div id="landing__table-games" class="games-slider__games_scrollable hide-scrollbar">
        @foreach ($games as $game)
            @include('phone.games.single-game', ['game' => $game])
        @endforeach
        <a href="{{ route('games.table', [], false) }}" class="slider-all-games">
            <div class="arrow-right"></div>
            <div class="mt-2">
                {{ __('landing.all') }}
            </div>
        </a>
    </div>
</div>
