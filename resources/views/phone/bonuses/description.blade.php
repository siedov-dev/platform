<div class="bonuses__title-banner flex column jc-center">
    <img src="/img/landing/bonuses/{{$bonus_id}}-wide.png" class="back-blur">
{{--    <img src="/img/landing/bonuses/{{$bonus_id}}-banner.png" class="right-img">--}}
    <div class="description">
        <div class="flex jc-center title text-white">{{__('bonuses.landind_'.$bonus_id)}}</div>
        <div class="flex jc-center prize text-white">{{__('bonuses.landind_'.$bonus_id.'_prize')}}</div>
    </div>
</div>
<article class="bonuses__article">
    @includeIf('desktop.bonuses.info.'.$bonus_id)
</article>
