<?php
/** @var $event \App\Models\BonusEvents\BonusEvent */

$active = $event->isActive();
$timeProgress = round((time() - strtotime($event->start_time))/($event->end_time->timestamp - strtotime($event->start_time))*100);
?>

<div class="cabinet__bonuses-item cabinet__bonus
{{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     style="@if(!$active && !($event instanceof \App\Models\BonusEvents\VegasFsEvent)) order: 100; opacity: 0.25; @endif"
     data-id="{{ $event->id }}">

    <div class="cabinet__bonus-status">
        @if($active)
            <div class="status-circle status-circle_green cabinet__bonus-status-icon"></div>
            <span class="text-green">{{$event->getStatusName()}}</span>
        @else
            <div class="status-circle status-circle_orange cabinet__bonus-status-icon"></div>
            <span class="text-orange">{{$event->getStatusName()}}</span>
        @endif
    </div>

    <div class="cabinet__bonus-corner">
        <div class="cabinet__bonus-info-btn" data-id="{{ $event->id }}" @if($event->getDescriptionName() !== null) data-bonus-id="{{$event->getDescriptionName()}}" @endif>
            <img src="/img/cabinet/bonuses/icons/info.svg">
            <div class="cabinet__bonus-info-dd">{!! $event->getDescription() !!}</div>
        </div>
    </div>


    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-left">
            <div class="cabinet__bonus-title">{{ $event->getName() }}</div>

            <div class="cabinet__bonus-balance">
                <div class="cabinet__bonus-balance-name">{{$event->getShortDescription()}}</div>
                @if($event->getActivationsLeft() >= 0)
                    <div class="cabinet__bonus-balance-value">{{__('bonus.activations_available')}}: {{$event->getActivationsLeft()}}</div>
                @endif
            </div>

            @if($event->userDataRequired())
                <div class="cabinet__event-user-data"><input type="text" name="user_data" value="" placeholder="{{__('bonus.activation_sum')}}"></div>
            @endif

            @if($event->isActive())
                <div class="cabinet__bonus-countdown">{{$event->end_time->format('Y-m-d H:i:s')}}</div>
                <div class="cabinet__bonus-progress progress-bar">
                    <div class="progress-bar__track progress-bar__track_green" style="width: {{ $timeProgress }}%"></div>
                </div>
            @endif
        </div>
        <div class="cabinet__bonus-right">
            <div class="cabinet__bonus-image-wrap">
                <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" class="cabinet__bonus-image">
            </div>

            @if($event->isActivatedByUser())
                <div data-url="{{action("GetBonusController@activateEvent", ['event_id' => $event->id], false)}}" class="cabinet__event-activate-btn">{{__('bonus.activate')}}</div>
            @endif

            @if($event->isCancelable())
                <div data-url="{{action("GetBonusController@cancelEvent", ['event_id' => $event->id], false)}}" class="cabinet__event-activate-btn">{{__('bonus.cancel')}}</div>
            @endif
        </div>
    </div>
</div>
