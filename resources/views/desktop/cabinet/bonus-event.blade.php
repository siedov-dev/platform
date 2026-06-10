<?php
/** @var $event \App\Models\BonusEvents\BonusEvent */

$active = $event->isActive();
$timeProgress = round((time() - strtotime($event->start_time))/($event->end_time->timestamp - strtotime($event->start_time))*100);
?>
<div class="cabinet__bonuses-wrapper">
    <div class="cabinet__bonuses-item cabinet__bonus
    {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
         style='@if(!$active) order: 100; opacity: 0.5; @endif background-image: url("{{$event->getImgUrl($_COOKIE['theme']??null)}}");'
         data-id="{{ $event->id }}">

        <div class="cabinet__bonus-time">
            <img src="/img/bonuses/clock.png">
            <div>{{\Carbon\Carbon::parse($event->start_time)->isoFormat('DD MMM')}}-{{\Carbon\Carbon::parse($event->end_time)->isoFormat('DD MMM YYYY')}}</div>
        </div>

    {{--    <div class="cabinet__bonus-status">--}}
    {{--        @if($active)--}}
    {{--            <div class="status-circle status-circle_green cabinet__bonus-status-icon"></div>--}}
    {{--            <span class="text-green">{{$event->getStatusName()}}</span>--}}
    {{--        @else--}}
    {{--            <div class="status-circle status-circle_orange cabinet__bonus-status-icon"></div>--}}
    {{--            <span class="text-orange">{{$event->getStatusName()}}</span>--}}
    {{--        @endif--}}
    {{--    </div>--}}


        <div class="cabinet__bonus-content">
                <div class="cabinet__bonus-title">{{ $event->getName() }}</div>

{{--                <div class="cabinet__bonus-balance">--}}
{{--                    <div class="cabinet__bonus-balance-name">{{$event->getShortDescription()}}</div>--}}
{{--                    @if($event->getActivationsLeft() >= 0)--}}
{{--                        <div class="cabinet__bonus-balance-value">{{__('bonus.activations_available')}}: {{$event->getActivationsLeft()}}</div>--}}
{{--                    @endif--}}
{{--                </div>--}}


{{--                @if($event->isActive())--}}
{{--                    <div class="cabinet__bonus-countdown">{{$event->end_time->format('Y-m-d H:i:s')}}</div>--}}
{{--                    <div class="cabinet__bonus-progress progress-bar">--}}
{{--                        <div class="progress-bar__track progress-bar__track_green" style="width: {{ $timeProgress }}%"></div>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--            <div class="cabinet__bonus-right">--}}
{{--                <div class="cabinet__bonus-image-wrap">--}}
{{--                    <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" class="cabinet__bonus-image">--}}
{{--                </div>--}}

                @if($event->isActivatedByUser())
{{--                    <div data-url="{{action("GetBonusController@activateEvent", ['event_id' => $event->id], false)}}" class="cabinet__event-activate-btn">{{__('bonus.activate')}}</div>--}}
                <div class="bonus-btn-wrap">
                    <div data-url="{{action("GetBonusController@activateEvent", ['event_id' => $event->id], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('cabinet.get')}}</div>
                    <div class="bonus-btn-left-border"></div>
                    <div class="bonus-btn-info" onclick="this.closest('.cabinet__bonuses-wrapper').classList.toggle('info-shown')">
                        <div class="bonus-info-arrow"></div>
                    </div>
                </div>
                @endif

{{--                @if($event->isCancelable())--}}
{{--                    <div data-url="{{action("GetBonusController@cancelEvent", ['event_id' => $event->id], false)}}" class="cabinet__event-activate-btn">{{__('bonus.cancel')}}</div>--}}
{{--                @endif--}}
{{--            </div>--}}
        </div>
    </div>
    <div class="bonus-info-wrapper">
        <div class="bonus-info-content">
            {!! $event->getShortDescription(); !!}
{{--            @if($event->userDataRequired())--}}
{{--                <div class="cabinet__event-user-data"><input type="text" name="user_data" value="" placeholder="{{__('bonus.activation_sum')}}"></div>--}}
{{--            @endif--}}

        </div>
    </div>
</div>
