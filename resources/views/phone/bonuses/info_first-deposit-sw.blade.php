<div class="width-limiter" @if(!empty($in_modal) && $in_modal) style="background-color: #272660; padding: 0 1rem 1rem 1rem; border-radius: 15px;" @endif>

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.firstDepName-sw') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
           <div class="article__title mt-2">{{ __('bonuses.firstDepName-sw') }} </div>
            <div class="article__text">
                <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText0-sw') }}</h2>
                   </div>
                <br>
                    {{ __('bonuses.firstDepText1-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.firstDepText2-sw') }}
                </p>
            </div>

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText3-sw') }}</h2>
                <ul class="mt-2">
                    <li>{{ __('bonuses.firstDepText4-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText5-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText6-sw') }}</li>
                </ul>

                <br>
                    {{ __('bonuses.firstDepText7-sw') }}
                </br>

             <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText8-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.firstDepText9-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText10-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText11-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText12-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText13-sw') }}</li>
                </ul>
                    </div>



            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText14-sw') }}</h2>
                <ul class="mt-2">
               <ul class="mt-2">
                   <br>
                    {{ __('bonuses.firstDepText15-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.firstDepText16-sw') }}
                </p>
                    <br>
                    {{ __('bonuses.firstDepText17-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.firstDepText18-sw') }}
                </br>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.firstDepText38-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText19-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_wager') }}</li>
                </ul>
                </div>
                <div>
                <br>
                    {{ __('bonuses.firstDepText20-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.firstDepText21-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.firstDepText22-sw') }}
                 </br>
                 <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.firstDepText23-sw') }}</li>
                </ul>
                    </div>

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText29-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.firstDepText30-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText31-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText32-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText33-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText34-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText35-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText36-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText37-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_procent') }}</li>
                    <li>{{ __('bonuses.firstDepText40-sw') }}</li>
                </ul>
            </div>


            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.firstDepText24-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    {{ __('bonuses.firstDepText25-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.firstDepText26-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.firstDepText27-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.firstDepText28-sw') }}
              </ul>
            </div>




                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif

                    <!-- <h2 class="mt-2">{{ __('bonuses.firstDepText15') }}:</h2>
                    <div id="games__items" class="games-slider__games">
                        <div class="game slider__item">
                            <div class="game__veil">
                                <div class="game__name">Gonzos Quest touch</div>
                                <div class="game__button game__play-real" data-url="/play/vegas/pragmatic/vs20fruitparty_prg/real">{{ __('bonuses.play') }}</div>
                                <div class="game__button game__play-demo" data-url="/play/vegas/pragmatic/vs20fruitparty_prg/demo">{{ __('bonuses.try') }}</div>
                            </div>
                            <img src="/img/games-previews/vegas/pragmatic/vs20fruitparty_prg.webp" class="game__image">
                        </div>

                    </div>-->
                @endif
            </div>
        </div>



    </div>
</div>
