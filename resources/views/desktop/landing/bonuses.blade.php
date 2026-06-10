@php
    $land_bonuses = [
            'welcome',
            'wheel',
            'slots',
            'shop',
            'daily',
            'achivements',
        ];
@endphp
<div class="games-slider mt-2">
    <div class="games-slider__header">
        <div class="games-slider__info">
            <div>
                <div class="games-slider__title">{{ __('landing.bonuses') }}</div>
            </div>
        </div>

        <div class="games-slider__controls">
            <div class="games-slider__arrow-left slider__disabled" data-target="#landing__bonuses"></div>
            <div class="games-slider__arrow-right" data-target="#landing__bonuses"></div>
        </div>
    </div>

    <div id="landing__live-games" class="games-slider__games_scrollable games-slider__bonuses_scrollable hide-scrollbar">
        @foreach($land_bonuses as $l_bonus)
            <div class="landing__bonus slider__item" style='background-image: url("/img/landing/bonuses/{{$l_bonus}}.png")'>
                <div class="description">
                    <div class="flex jc-center title mb-05">{{__('bonuses.landind_'.$l_bonus)}}</div>
                    <div class="flex jc-center prize mb-1">{{__('bonuses.landind_'.$l_bonus.'_prize')}}</div>
                    <a class="button_green mb-1 content-link" href="{{ route('bonuses.info', ['id' => $l_bonus], false) }}">{{__('bonuses.moreDetails')}}</a>
                </div>
            </div>
        @endforeach
    </div>
</div>

