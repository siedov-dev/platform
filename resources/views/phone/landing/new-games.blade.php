<div class="games-slider">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <img src="/img/landing/party-poppertop.svg" class="games-slider__icon">
            <div>
                <div class="games-slider__title">{{ __('landing.new') }}</div>
                <div class="flex">
                    <div class="games-slider__total-number">{{ $totalGames }}+</div>&nbsp;
                    <div class="games-slider__total-text">{{ __('landing.nGames') }}!</div>
                </div>
            </div>
        </div>

        <div class="games-slider__controls">
            <a href="{{ route('games.new', [], false) }}" class="games-slider__all-button">{{ __('landing.all') }} ({{ $totalGames }})</a>
            <div class="games-slider__left slider__disabled" data-target="#landing__slot-games"></div>
            <div class="games-slider__right" data-target="#landing__slot-games"></div>
        </div>
    </div>

    <div id="landing__top-games" class="games-slider__triple_games_list scroll-hidden">
        @php
            $c = 0;
        @endphp
        @foreach ($games as $game)

            @if($c === 0) <div class="triple-games-wrapper"> @endif
                <div class="games-slider__triple_list_item item-{{$c}}">
                    @include('desktop.games.single-game', ['game' => $game])
                </div>
                @if($c === 2) </div> @endif
            @php
                $c = ++$c % 3;
            @endphp
        @endforeach
        @if($c !== 0) </div> @endif
</div>
</div>
