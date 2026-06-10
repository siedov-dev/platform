<div class="width-limiter" @if(!empty($in_modal) && $in_modal) style="background-color: #272660; padding: 0 1rem 1rem 1rem; border-radius: 15px;" @endif>

    @if(empty($in_modal) || !$in_modal)
        <div class="breadcrumbs">
            <a href="{{ route('bonuses', [], false) }}" class="breadcrumbs__item">{{ __('bonuses.allBonuses') }}</a>
            <div class="breadcrumbs__item">{{ __('bonuses.375_procent') }}</div>
        </div>
    @endif

    <div class="article mt-2">


        <div class="article__content">
            @if(empty($in_modal) || !$in_modal)
                <div class="article__banner">
                    @include('desktop.landing.main-slider')
                </div>
            @endif
           <div class="article__title mt-2">{{ __('bonuses.375_procent1') }} </div>
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
                <!--<h2 class="mt-2">{{ __('bonuses.smart-wager1') }}</h2>-->
                <ul class="mt-2">
                    <li>{{ __('bonuses.375_procent2') }}</li>
                    <li>{{ __('bonuses.375_procent3') }}</li>
                    <li>{{ __('bonuses.375_procent4') }}</li>
                </ul>

                <br>
                    {{ __('bonuses.375_procent5') }}
                </br><b>
                    {{ __('bonuses.375_procent6') }}
                </br></b>
                    {{ __('bonuses.375_procent7') }}
                </br>

             
            <div class="article__text">
                <h2 class="mt-2">{{ __('bonuses.375_procent8') }}</h2>
                 <div class="article__text">
                <!--<h2 class="mt-2">{{ __('bonuses.smart-wager1') }}</h2>-->
                <ul class="mt-2">
                    <li>{{ __('bonuses.375_procent9') }}</li>
                    <li>{{ __('bonuses.375_procent10') }}</li>
                </ul>
            </div>
         </div>



                @if(empty($in_modal) || !$in_modal)

                    @if(\Illuminate\Support\Facades\Auth::guest()) <a href="{{ route('register', [], false) }}" data-ajax="modal" class="button button_outlined mt-2">{{ __('bonuses.takeBonus') }}</a> @endif


                @endif
            </div>
        </div>


    </div>
</div>
