<div class="width-limiter" @if(!empty($in_modal) && $in_modal) style="background-color: #272660; padding: 0 1rem 1rem 1rem; border-radius: 15px;" @endif>

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.thirdDepName-sw') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
           <div class="article__title mt-2">{{ __('bonuses.thirdDepName-sw') }} </div>
            <!--<div class="article__text">
                <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText0-sw') }}</h2>
                   </div>
                <br>
                    {{ __('bonuses.thirdDepText1-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.thirdDepText2-sw') }}
                </p>
            </div>-->

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText3-sw') }}</h2>
                <ul class="mt-2">
                    <li>{{ __('bonuses.thirdDepText4-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText5-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText6-sw') }}</li>
                </ul>

                <br>
                    {{ __('bonuses.thirdDepText7-sw') }}
                </br>

             <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText8-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.thirdDepText9-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText10-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText11-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText12-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText13-sw') }}</li>
                </ul>
                    </div>



            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText14-sw') }}</h2>
                <ul class="mt-2">
               <ul class="mt-2">
                   <br>
                    {{ __('bonuses.thirdDepText15-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.thirdDepText16-sw') }}
                </p>
                    <br>
                    {{ __('bonuses.thirdDepText17-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.thirdDepText18-sw') }}
                </br>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.thirdDepText38-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText19-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_wager') }}</li>
                </ul>
                </div>
                <div>
                <br>
                    {{ __('bonuses.thirdDepText20-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.thirdDepText21-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.thirdDepText22-sw') }}
                 </br>
                 <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.thirdDepText23-sw') }}</li>
                </ul>
                    </div>

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText29-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.thirdDepText30-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText31-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText32-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText33-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText34-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText35-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText36-sw') }}</li>
                    <li>{{ __('bonuses.thirdDepText37-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_procent') }}</li>
                    <li>{{ __('bonuses.firstDepText40-sw') }}</li>
                </ul>
            </div>


            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.thirdDepText24-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    {{ __('bonuses.thirdDepText25-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.thirdDepText26-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.thirdDepText27-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.thirdDepText28-sw') }}
              </ul>
            </div>




                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif

                @endif
            </div>
        </div>


    </div>
</div>
