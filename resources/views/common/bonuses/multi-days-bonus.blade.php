@php
/** @var $bonus \App\Models\Bonuses\MultiDaysBonus */
    $params = $bonus->getCurrentDayParams();
    $active = !in_array($bonus->status, [$bonus::STATUS_EXPIRED, $bonus::STATUS_DONE, $bonus::STATUS_CANCELED]);
@endphp
<div class="cabinet__bonuses-wrapper">
<div class="cabinet__bonuses-item cabinet__bonus multidays-bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     style='@if(!$active) order: 100; opacity: 0.5; @endif background-image: url("{{$bonus->getImgUrl(\App\Helpers\UI::getTheme())}}");' data-id="{{ $bonus->getId() }}">

    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-time">
            @if($bonus->status === \App\Models\Bonuses\KUserBonus::STATUS_NOT_ACTIVATED)
                <img src="/img/icons/calendar.svg">
                <div>{{\Carbon\Carbon::parse($bonus->created_at)->isoFormat('DD MMM')}}-{{\Carbon\Carbon::parse($bonus->active_to)->isoFormat('DD MMM YYYY')}}</div>
            @elseif($bonus->isListeningDeposit())
                <img src="/img/icons/time.svg">
                <span class="text-subtitle">{{__('bonus.next_fs_in')}}:&nbsp;</span>
                <div class="flex ai-center update-timer-block" data-target_time="{{$bonus->completed_at}}">
                    <div><span class="timer-hours-left">-</span>h</div>&nbsp;:&nbsp;
                    <div><span class="timer-minutes-left">-</span>m</div>
                </div>
            @else
                <div class="flex ai-center">
                    <span class="text-subtitle">{{__('bonus.day')}}&nbsp;{{$bonus->currentDay()}}:&nbsp;</span>
                    <div class="flex ai-center">
                        <img src="/img/landing/fs.svg">&nbsp;{{$params->getSpins()}}
                    </div>
                </div>
            @endif
        </div>
        @if($bonus->isPaymentRequired())
            <div class="cabinet__bonus-dep">
                {{__('bonus.min_dep')}}&nbsp;
                <div>{{\App\Helpers\UI::moneyStr($bonus->getRequiredSum(), " ")}}
                    <span class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                </div>
            </div>
        @endif
        <div></div>
        <div class="cabinet__bonus-title">{{ $bonus->getTitle() }}</div>
        <div class="bonus-btn-wrap">
            {!! $bonus->getRenderer()->renderPlayButton() !!}
            @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
        </div>
    </div>
</div>
</div>
