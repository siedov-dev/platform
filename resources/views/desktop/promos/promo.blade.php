@php
    $target_time = $item->getTargetTime();
@endphp
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
    <div class="promo-controls">
        <div class="flex column jc-center ai-fs">
            <div class="promo-slogan">
                {{$item->getSlogan()}}
            </div>
            <div class="promo-title">
                {{$item->getTitle()}}
            </div>
        </div>
        <div class="flex column jc-center">
            @if($item->hasDescription())
                <a class="button button_green content-link" href="{{route('promos.description', ['promo_id' => $item->getId()])}}">{{$item->getDescriptionBtnText()}}</a>
            @else
                <a class="button button_green" href="{{$item->getActionUrl()}}">{{$item->getBtnText()}}</a>
            @endif
        </div>
    </div>
    <img src="{{$item->getImg()}}">
</div>
