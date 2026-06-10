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
        <div class="promo__item @if(!$item->isActive()) test-item @endif">
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
        </div>
        <img class="promo-img" src="{{$item->getImg()}}">
    </div>
    <div class="promo-info p-1">
        <div class="flex column jc-center ai-fs">
            <div class="promos__promo-title">
                {{$item->getTitle()}}
            </div>
            <div class="promos__promo-slogan">
                {{$item->getSlogan()}}
            </div>
        </div>
        <div class="flex column jc-center mt-1">
            <a class="button button_green" href="{{$item->getActionUrl()}}">{{$item->getBtnText()}}</a>
        </div>
    </div>
    <div class="description-body mt-1 p-1">
        {!! $item->renderDescription() !!}
    </div>
    @include('phone.blocks.bottom-deposit-btn')
</div>
