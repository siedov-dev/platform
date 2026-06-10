<div class="width-limiter">

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonus.bonus_27_name') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
            <div class="article__title mt-2">{{ __('bonus.bonus_27_name') }}</div>
            <div class="article__text">
                <p>
                    {{ __('bonuses.wagerFSText1') }}
                </p>
            </div>


            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.wagerFSText2') }}:</h2>
                <p>
                    1. {{ __('bonuses.wagerFSText3') }}<br/>
                    2. {{ __('bonuses.wagerFSText4') }}
                </p>

                <ul class="mt-2">
                    <li>{{ __('bonuses.wagerFSText5') }}</li>
                    <li>{{ __('bonuses.wagerFSText6') }}</li>
                </ul>

                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif
{{--                    @if(auth()->guest())--}}
{{--                        <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a>--}}
{{--                    @elseif(auth()->user()->getActiveBonus() !== null && auth()->user()->getActiveBonus()->type === 1)--}}
{{--                        <div onclick="document.querySelector('.cabinet__bonuses-link').click()" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</div>--}}
{{--                    @endif--}}

                    <h2 class="mt-2">{{ __('bonuses.wagerFSText7') }}:</h2>

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
