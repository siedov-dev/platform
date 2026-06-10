<div class="games-slider">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <div>
                <div class="games-slider__title">{{ __('landing.slots') }}</div>
            </div>
        </div>

{{--        <div class="games-slider__controls">--}}
{{--            <div class="games-slider__arrow-left slider__disabled slider__prev" data-target="#landing__slot-games"></div>--}}
{{--            <div class="games-slider__arrow-right slider__next" data-target="#landing__slot-games"></div>--}}
{{--        </div>--}}
    </div>

    <div id="landing__slot-games" class="games-slider__games_scrollable hide-scrollbar">
        @foreach ($games as $game)
            @include('phone.games.single-game', ['game' => $game])
        @endforeach
        <a href="{{ route('games.slots', [], false) }}" class="slider-all-games">
            <div class="arrow-right"></div>
            <div class="mt-2">
                {{ __('landing.all') }}
            </div>
        </a>
    </div>
</div>
