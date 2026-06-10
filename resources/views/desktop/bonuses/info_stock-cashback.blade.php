<div class="width-limiter">

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.stockCashBackName') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
            <div class="article__title mt-2">{{ __('bonuses.stockCashBackName') }}</div>
            <div class="article__text">
                <p>
                    {{ __('bonuses.stockCashBackText1') }}
                </p>
            </div>

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.stockCashBackText2') }}:</h2>
                <p>
                    {{ __('bonuses.stockCashBackText3') }}
                </p>

                 <ul class="mt-2">
                    <li>{{ __('bonuses.stockCashBackText4') }}</li>
                    <li>{{ __('bonuses.stockCashBackText6') }}</li>
                    <li>{{ __('bonuses.stockCashBackText7') }}</li>
                    <li>{{ __('bonuses.stockCashBackText8') }}</li>
                    <li>{{ __('bonuses.stockCashBackText9') }}</li>
                </ul>

                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif

                    <h2 class="mt-2">{{ __('bonuses.stockCashBackText5') }}:</h2>

                    <div id="games__items" class="games-slider__games">
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Big Bass Bonanza</div>
                                <div class="game__button game__play-real" data-url="/play/pragplay/pragmatic/vs10bbbonanza/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/pragplay/pragmatic/vs10bbbonanza/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/pragmatic/vs10bbbonanza.png" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Buffalo Hunter</div>
                                <div class="game__button game__play-real" data-url="/play/qt/NLC/NLC-buffalohunter/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/qt/NLC/NLC-buffalohunter/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/qt/NLC/NLC-buffalohunter.jpg" class="game__image">
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
