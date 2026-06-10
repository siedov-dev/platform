<div class="width-limiter">
    @include('desktop.landing.main-slider')


    @include('desktop.landing.games-categories')

</div>


<div class="games-content">
    <div class="width-limiter" id="landing-games">
        @include('desktop.landing.games')
{{--        @if(auth()->check())--}}
{{--            <?= cache()->remember('landingGamesAuth_'.app()->getLocale(), 1500, function() { return view('desktop.landing.games')->render(); }) ?>--}}
{{--        @else--}}
{{--            <?= cache()->remember('landingGames_'.app()->getLocale(), 1500, function() { return view('desktop.landing.games')->render(); }) ?>--}}
{{--        @endif--}}
    </div>
</div>


{{--<div class="bonuses-content">--}}
{{--    <div class="width-limiter" id="landing-bonuses">--}}
{{--        @include('desktop.landing.bonuses')--}}
{{--    </div>--}}
{{--</div>--}}

{{--<?php \App\Helpers\Measure::logNow('landing-bonuses'); ?>--}}

<div class="recent-wins-content">
    <div class="width-limiter">

{{--        @include('desktop.landing.providers')--}}

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

        <hr class="mt-2 mb-2">

        <div class="landing__footer-logo">
            <div class="img-bg">
                @include('common.blocks.logo')
            </div>
        </div>

    </div>

</div>
