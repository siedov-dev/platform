<div class="width-limiter" @if(!empty($in_modal) && $in_modal) style="background-color: #272660; padding: 0 1rem 1rem 1rem; border-radius: 15px;" @endif>

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.secondDepName-sw') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
           <div class="article__title mt-2">{{ __('bonuses.secondDepName-sw') }} </div>
            <!--<div class="article__text">
                <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText0-sw') }}</h2>
                   </div>
                <br>
                    {{ __('bonuses.secondDepText1-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.secondDepText2-sw') }}
                </p>
            </div>-->

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText3-sw') }}</h2>
                <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText4-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText5-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText6-sw') }}</li>
                </ul>

                <br>
                    {{ __('bonuses.secondDepText7-sw') }}
                </br>

             <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText8-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText9-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText10-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText11-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText12-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText13-sw') }}</li>
                </ul>
                    </div>



            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText14-sw') }}</h2>
                <ul class="mt-2">
               <ul class="mt-2">
                   <br>
                    {{ __('bonuses.secondDepText15-sw') }}
                 </br>
                 <p>
                    {{ __('bonuses.secondDepText16-sw') }}
                </p>
                    <br>
                    {{ __('bonuses.secondDepText17-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.secondDepText18-sw') }}
                </br>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText38-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText19-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_wager') }}</li>
                </ul>
                </div>
                <div>
                <br>
                    {{ __('bonuses.secondDepText20-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.secondDepText21-sw') }}
                 </br>
                 <br>
                    {{ __('bonuses.secondDepText22-sw') }}
                 </br>
                 <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText23-sw') }}</li>
                </ul>
                    </div>

            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText29-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    <li>{{ __('bonuses.secondDepText30-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText31-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText32-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText33-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText34-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText35-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText36-sw') }}</li>
                    <li>{{ __('bonuses.secondDepText37-sw') }}</li>
                    <li>{{ __('bonuses.firstDepText_procent') }}</li>
                    <li>{{ __('bonuses.firstDepText40-sw') }}</li>
                </ul>
            </div>


            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.secondDepText24-sw') }}</h2>
                <ul class="mt-2">
                    <ul class="mt-2">
                    {{ __('bonuses.secondDepText25-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.secondDepText26-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.secondDepText27-sw') }}
                <br/>
                <br/>
                    {{ __('bonuses.secondDepText28-sw') }}
              </ul>
            </div>




                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif


                @endif
            </div>
        </div>


    </div>
</div>
