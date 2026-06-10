<div class="width-limiter">

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.birthdayName') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
            <div class="article__title mt-2">{{ __('bonuses.birthdayName') }}</div>
            <div class="article__text">
                <p>
                    {{ __('bonuses.birthdayText1') }}
                </p>
            </div>

            <!--<div class="tournament__info_thin">
                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.birthdayText2') }}:</div>
                    <div class="tournament__info-value">
                        <div class="currency-icon currency-icon_uah tournament__info-icon"></div>{{ __('bonuses.birthdayText3') }}
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.birthdayText4') }}:</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/bet-icon.svg" class="tournament__info-icon"> 0.00
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.birthdayText5') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/min-bet-icon.svg" class="tournament__info-icon"> 5.00
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.birthdayText6') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/max-bet-icon.svg" class="tournament__info-icon">{{ __('bonuses.no') }}
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.birthdayText7') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/reloads-count-icon.svg" class="tournament__info-icon">{{ __('bonuses.notLimited') }}
                    </div>
                </div>
            </div>-->


            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.birthdayText8') }}:</h2>
                <p>
                    {{ __('bonuses.birthdayText9') }}
                </p>

                <ul class="mt-2">
                    <li>{{ __('bonuses.birthdayText10') }}</li>
                    <li>{{ __('bonuses.birthdayText11') }}</li>
                    <li>{{ __('bonuses.birthdayText13') }}</li>
                </ul>

                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif
{{--                    @if(auth()->guest())--}}
{{--                        <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a>--}}
{{--                    @elseif(auth()->user()->getActiveBonus() !== null && auth()->user()->getActiveBonus()->type == 26)--}}
{{--                        <div onclick="document.querySelector('.cabinet__bonuses-link').click()" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</div>--}}
{{--                    @endif--}}


                    <h2 class="mt-2">{{ __('bonuses.birthdayText12') }}:</h2>

                    <div id="games__items" class="games-slider__games">
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Gonzos Quest touch</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/netent/gonzos_quest_touch/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/netent/gonzos_quest_touch/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/netent/gonzos_quest_touch.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Cash-o-matic</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/netent/cash_o_matic/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/netent/cash_o_matic/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/netent/cash_o_matic.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Lost Relics</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/netent/lost_relics/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/netent/lost_relics/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/netent/lost_relics.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Twin Happiness</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/netent/twin_happiness/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/netent/twin_happiness/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/netent/twin_happiness.jpg" class="game__image">
                        </div>
                    </div>
                @endif
            </div>
        </div>


        @if(empty($in_modal) || !$in_modal)
            @include('desktop.bonuses.sidebar')
        @endif

    </div>
</div>
