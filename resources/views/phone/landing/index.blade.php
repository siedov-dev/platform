<div class="width-limiter full-width">

    <div class="bonus-slider-wrapper">
{{--        @if(\Illuminate\Support\Facades\Auth::guest())--}}
{{--            @include('phone.landing.welcome-bonus')--}}
{{--        @endif--}}
        @include('phone.landing.main-slider')
    </div>


    @include('phone.landing.games-categories')

</div>

<div class="mt-1"></div>

<div class="landing-search">
    @include('phone.landing.search-form', ['pageSize' => 30])
    <div class="games">
        <div id="games__items" class="games__items">
        </div>
        <div id="games__show-more-wrap" class="flex jc-center">
        </div>
    </div>
</div>

<div class="games-content">
    <div class="width-limiter" id="landing-games">
        @include('phone.landing.games')
{{--    @if(auth()->check())--}}
{{--            <?= cache()->remember('landingGamesPhoneAuth_'.app()->getLocale(), 1500, function() { return view('phone.landing.games')->render(); }) ?>--}}
{{--        @else--}}
{{--            <?= cache()->remember('landingGamesPhone_'.app()->getLocale(), 1500, function() { return view('phone.landing.games')->render(); }) ?>--}}
{{--        @endif--}}
    </div>
</div>



{{--<div class="bonuses-content">--}}
{{--    <div class="width-limiter" id="landing-bonuses">--}}
{{--        @include('desktop.landing.bonuses')--}}
{{--    </div>--}}
{{--</div>--}}


<div class="recent-wins-content">
    <div class="width-limiter">

{{--        <div class="providers-list mb-4">--}}
{{--            <div class="games-slider__header mb-1">--}}
{{--                <div class="games-slider__info">--}}
{{--                    <div>--}}
{{--                        <div class="games-slider__title">{{ __('landing.providers') }}</div>--}}
{{--                    </div>--}}
{{--                </div>--}}

{{--                <div class="games-slider__controls">--}}
{{--                    <div class="all-button" onclick="this.closest('.providers-list').classList.toggle('show-all')">{{ __('landing.all') }}</div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            @include('desktop.landing.providers-icons')--}}
{{--        </div>--}}


{{--        <article class="landing__footer-text">--}}
{{--            @include('common.kraken-ad')--}}
{{--        </article>--}}

        <hr class="mt-2">

        <section class="landing__footer-images">
            <div class="landing__footer-image">
                <img src="/img/landing/headphones.png">
                <div>{{__('landing.support24')}}</div>
            </div>
            <div class="landing__footer-image">
                <img src="/img/landing/lock.png">
                <div>{{__('landing.onlyLicence')}}</div>
            </div>
            <div class="landing__footer-image">
                <img src="/img/landing/crown.png">
                <div>{{__('landing.vipOffer')}}</div>
            </div>
            <div class="landing__footer-image">
                <img src="/img/landing/referral.png">
                <div>{{__('landing.referralProgram')}}</div>
            </div>
        </section>
        {{--        <?= cache()->remember('recentWinsPhone_'.app()->getLocale(), 60, function() { return view('phone.landing.recent-wins')->render(); }) ?>--}}

{{--        <?php \App\Helpers\Measure::logNow('Landing recent wins'); ?>--}}
    </div>
    @include('phone.blocks.bottom-deposit-btn')
</div>
