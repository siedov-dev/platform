<div class="width-limiter">

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.fsWithoutWagerName') }}</div>
        </div>
    @endif
    @include('phone.bonuses.info-hint')
    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
            <div class="article__title mt-2">{{ __('bonuses.fsWithoutWagerName') }}</div>
            <div class="article__text">
                <p>
                    {{ __('bonuses.fsWithoutWagerText1') }}
                </p>
            </div>


            <div class="article__text">

                @if(empty($in_modal) || !$in_modal)
                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif
                    <h2 class="mt-2">{{ __('bonuses.fsWithoutWagerText2') }}:</h2>

                    <div id="games__items" class="games-slider__games">
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Age of Ice Dragons</div>
                                <div class="game__button game__play-real" data-url="/play/qt/KGL/KGL-ageoficedragons/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/qt/KGL/KGL-ageoficedragons/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/qt/KGL/KGL-ageoficedragons.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">2 Gods – Zeus vs Thor</div>
                                <div class="game__button game__play-real" data-url="/play/qt/YGG/YGG-2godszeusvsthor/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/qt/YGG/YGG-2godszeusvsthor/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/qt/YGG/YGG-2godszeusvsthor.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Alice</div>
                                <div class="game__button game__play-real" data-url="/play/qt/DS/DS-alice/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/qt/DS/DS-alice/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/qt/DS/DS-alice.jpg" class="game__image">
                        </div>
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">The Cheshire Tree</div>
                                <div class="game__button game__play-real" data-url="/play/qt/OMI/OMI-thecheshiretree/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/qt/OMI/OMI-thecheshiretree/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/qt/OMI/OMI-thecheshiretree.jpg" class="game__image">
                        </div>
                    </div>
                @endif
            </div>
        </div>




    </div>
</div>
