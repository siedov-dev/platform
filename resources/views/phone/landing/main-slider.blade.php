@php
    $banners = \App\Models\SliderBanner::getBanners(\App\MUser::getAuth());
@endphp
<div class="main-slider">
    <div class="main-slider__arrow-left"></div>

    <div class="main-slider__slides">
        @foreach($banners as $idx => $banner)
            <a href="{{ $banner->getUrl() }}"
               class="main-slider__slide
               @if($idx === 0) main-slider__slide_active
               @elseif($idx === 1) main-slider__slide_next
               @elseif($idx === $banners->count() - 1) main-slider__slide_prev
               @endif"
               @if($banner->getType() === $banner::TYPE_EXTERNAL_LINK) target="_blank" @endif
            >
                <img alt="banner" src="{{$banner->getImg()}}" class="main-slider__slide-bg">
            </a>
        @endforeach
{{--        @if($show_offer)--}}
{{--        <a href="{{ route('bonuses.info', ['id' => 'welcome'], false) }}" class="main-slider__slide main-slider__slide_active">--}}
{{--            <img src="/img/banners/welcome_offer_{{ app()->getLocale() }}.webp" class="main-slider__slide-bg">--}}
{{--        </a>--}}
{{--        @endif--}}

{{--        <a href="{{ route('games.tv', [], false) }}" class="main-slider__slide main-slider__slide_next @if($show_offer) main-slider__slide_next @else main-slider__slide_active @endif">--}}
{{--            <img alt="Playson Non-Stop Drops & Races" src="/img/tournaments/playson-non-stop/nonstop_m_{{ app()->getLocale() }}.webp" class="main-slider__slide-bg">--}}
{{--        </a>--}}

{{--        <a href="{{ route('tournaments', [], false) }}" class="main-slider__slide @if(!$show_offer) main-slider__slide_next @endif">--}}
{{--            <img alt="Big Money Bang" src="/img/tournaments/three-oaks-big-money-bang-znjw/BIG_MONEY_m_{{ app()->getLocale() }}.webp" class="main-slider__slide-bg">--}}
{{--        </a>--}}

{{--        <a href="{{ route('tournaments', [], false) }}" class="main-slider__slide">--}}
{{--            <img alt="Double Luck" src="/img/tournaments/three-oaks-double-luck-october/DOUBLE_LUCK_m_{{ app()->getLocale() }}.webp" class="main-slider__slide-bg">--}}
{{--        </a>--}}

{{--        <a href="{{ route('bonuses.info', ['id' => 'wednesday'], false) }}" class="main-slider__slide">--}}
{{--            <img alt="CashBack every Wednesday" src="/img/banners/CASH_BACK/cashback_desctop_{{ app()->getLocale() }}.jpg" class="main-slider__slide-bg">--}}
{{--        </a>--}}

{{--        <a href="http://t.me/+fAc1KastrlpmYjNh" target="_blank" class="main-slider__slide main-slider__slide_prev">--}}
{{--            <img alt="Telegram Bonus" src="/img/banners/telegram_bot_m_{{ app()->getLocale() }}.webp" class="main-slider__slide-bg">--}}
{{--        </a>--}}

    </div>

    <div class="main-slider__arrow-right"></div>

    <div class="main-slider__dots">
        @foreach($banners as $idx => $banner)
            <div class="main-slider__dot @if($idx === 0) active @endif"></div>
        @endforeach
{{--        <div class="main-slider__dot active"></div>--}}
{{--        @if($show_offer) <div class="main-slider__dot"></div> @endif--}}
{{--        <div class="main-slider__dot"></div>--}}
{{--        <div class="main-slider__dot"></div>--}}
{{--        <div class="main-slider__dot"></div>--}}
{{--        <div class="main-slider__dot"></div>--}}
{{--        <div class="main-slider__dot"></div>--}}
    </div>
</div>
