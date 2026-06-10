<div class="width-limiter">

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.secondDepName') }}</div>
        </div>
    @endif

    <div class="article mt-2">


         <div class="article__content">
             @if(empty($in_modal) || !$in_modal)
                 <div class="article__banner">
                     @include('desktop.landing.main-slider')
                 </div>
             @endif
            <div class="article__title mt-2">{{ __('bonuses.secondDepName') }}</div>
            <div class="article__text">
                <p>
                    {{ __('bonuses.secondDepText1') }}
                </p>
            </div>

            <!--<div class="tournament__info_thin">
                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.secondDepText2') }}:</div>
                    <div class="tournament__info-value">
                        <div class="currency-icon currency-icon_uah tournament__info-icon"></div>{{ __('bonuses.secondDepText3') }}
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.secondDepText4') }}:</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/bet-icon.svg" class="tournament__info-icon"> 500/250
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.secondDepText5') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/min-bet-icon.svg" class="tournament__info-icon"> {{ __('bonuses.no') }}
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.secondDepText6') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/max-bet-icon.svg" class="tournament__info-icon"> {{ __('bonuses.no') }}
                    </div>
                </div>

                <div class="tournament__info-item">
                    <div class="tournament__info-label">{{ __('bonuses.secondDepText7') }}</div>
                    <div class="tournament__info-value">
                        <img src="/img/tournaments/reloads-count-icon.svg" class="tournament__info-icon"> {{ __('bonuses.no') }}
                    </div>
                </div>
            </div>-->


            <div class="article__text">

                <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText8') }}</li>
                    <li>{{ __('bonuses.secondDepText9') }}</li>
                    <li>{{ __('bonuses.secondDepText10') }}</li>
                    <li>{{ __('bonuses.secondDepText11') }}</li>
                </ul>

                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif

                    <h2 class="mt-2">{{ __('bonuses.secondDepText12') }}:</h2>

                    <div id="games__items" class="games-slider__games">
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Gonzos Quest touch</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/pragmatic/vs50juicyfr_prg/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/pragmatic/vs50juicyfr_prg/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/pragmatic/vs50juicyfr_prg.webp" class="game__image">
                        </div>
                        <!--<div class="game slider__item">
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
                        </div>-->
                    </div>
                @endif
            </div>
        </div>


    </div>
</div>

