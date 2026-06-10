<div class="bonuses__title-banner flex column jc-center">
    <img src="/img/landing/bonuses/{{$bonus_id}}-mobile.png" class="back-blur">
{{--    <img src="/img/landing/bonuses/{{$bonus_id}}-banner.png" class="right-img">--}}
    <div class="description">
        <div class="flex jc-center title text-white">{{__('bonuses.landind_'.$bonus_id)}} <br> {{__('bonuses.landind_'.$bonus_id.'_prize')}}</div>
        <div class="flex jc-center prize text-white"></div>
    </div>
</div>
<article class="bonuses__article mt-1">
    @includeIf('desktop.bonuses.info.'.$bonus_id)
</article>
<link rel="stylesheet" href="{{ asset('css/bonus.css') }}">
