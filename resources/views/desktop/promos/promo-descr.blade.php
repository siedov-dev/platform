@php
    /** @var \App\Models\Promos\PromoItem $item */
    $target_time = $item->getTargetTime();
@endphp
<div class="page__head slc-shop">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('promos')}}" class="content-link">{{__('promos.promos')}}</a>
        </div>
    </div>
</div>
<div class="promo-page">
    <div class="header-banner">
        <div class="promo__item">
            <div class="promo-info">
                <div class="promo-time">
                    <img src="/img/icons/time-orange.svg">
                    <div>
                        @if($item->getType() === \App\Models\Promos\PromoItemType::SpecificDate)
                            <div class="timer flex update-timer-block" data-target_time="{{$target_time}}">
                                <span class="timer-days-left" data-hide-on-zero data-symbol=" {{__('promos.days')}} ">--</span>&nbsp;
                                <span class="timer-hours-left" data-symbol=":">--</span>
                                <span class="timer-minutes-left" data-symbol=":">--</span>
                                <span class="timer-seconds-left" data-symbol="">--</span>
                            </div>
                        @else
                            {{$target_time}}
                        @endif
                    </div>
                </div>
                <div class="flex column jc-center ai-fs">
                    <div class="promo-title">
                        {{$item->getTitle()}}
                    </div>
                    <div class="promo-slogan">
                        {{$item->getSlogan()}}
                    </div>
                </div>
                <div class="flex column jc-center">
                     <a class="button button_green" href="{{$item->getActionUrl()}}">{{$item->getBtnText()}}</a>
                </div>
            </div>
        </div>
        <img class="promo-img" src="{{$item->getImg()}}">
        <img class="back-img" src="{{$item->getImg()}}">
    </div>
    <div class="description-body mt-2">
        {!! $item->renderDescription() !!}
    </div>

    <div class="games-slider mt-2">
        <div class="games-slider__header">
            <div class="games-slider__info">
                <div>
                    <div class="games-slider__title">{{ __('promos.other-promos') }}</div>
                </div>
            </div>

            <div class="games-slider__controls">
                <a href="{{ route('promos', [], false) }}" class="games-slider__all-button">{{ __('landing.all') }} ({{ $items->count() + 1 }})</a>
                <div class="games-slider__left slider__disabled"></div>
                <div class="games-slider__right"></div>
            </div>
        </div>

        <div id="other-promos" class="slider other-promos_scrollable hide-scrollbar">
            @foreach($other_promos as $promo)
                <div class="slider__item">
                    @include('desktop.promos.promo', ['item' => $promo])
                </div>
            @endforeach
        </div>
    </div>
</div>
