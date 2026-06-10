<?php
/** @var $bonus \App\Models\Bonuses\UserBonus */

$active = !$bonus->isCompleted();
$bonus->isLocked();
?>
<div class="cabinet__bonuses-wrapper">
    <div
        class="cabinet__bonuses-item cabinet__bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
        style='@if(!$active) order: 100; opacity: 0.5; @endif background-image: url("{{$bonus->getImgUrl(\App\Helpers\UI::getTheme())}}");'
        data-id="{{ $bonus->getId() }}"
    >

        <div class="cabinet__bonus-content">
            <div class="cabinet__bonus-time">
                <img src="/img/bonuses/clock.png">
                <div>{{\Carbon\Carbon::parse($bonus->created_at)->isoFormat('DD MMM')}}
                    -{{\Carbon\Carbon::parse($bonus->active_to)->isoFormat('DD MMM YYYY')}}</div>
            </div>
            <div>
                @if($bonus->isCancelable())
                    @if($bonus->isAgreementRequired() || $bonus->isLocked() || $bonus->isPaymentRequired())
                        @if($bonus->getSpinsAvailable() > 0)
                            <div class="cabinet__bonus-balance">
                                <img src="/img/bonuses/fs.svg" alt="fs">
                                <div class="ml-05">{{$bonus->getSpinsAvailable()}}</div>
                            </div>
                            @if($bonus->isPaymentRequired())
                                <div class="cabinet__bonus-dep">
                                    {{__('bonus.min_dep')}}&nbsp;
                                    <div>{{\App\Helpers\UI::moneyStr($bonus->getRequiredSum(), " ")}}
                                        <span class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                                    </div>
                                </div>
                            @endif
                        @endif
                    @else
                        @if($bonus->getSpinsAvailable() > 0)
                            <div>
                                <div class="bonuses__item-progress"
                                     style="--progress: {{$bonus->getProgressPercents()}}%">
                                    <div class="progress-label">{{$bonus->getProgressHint()}}</div>
                                </div>
                            </div>
                        @elseif($bonus->money_available > 0)
                            <div class="cabinet__bonus-balance">
                                <div>{{\App\Helpers\UI::moneyStr($bonus->getBalanceCents() / 100, " ")}}
                                    <span
                                        class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                                </div>
                            </div>
                            @if($bonus->isProgressAvailable())
                                <div class="bonuses__item-progress"
                                     style="--progress: {{$bonus->getProgressPercents()}}%">
                                    <div class="progress-label">{{$bonus->getTargetHint()}}</div>
                                </div>
                            @endif
                        @endif
                    @endif
                @endif
            </div>
            <div class="cabinet__bonus-title">{{ $bonus->getTitle() }}</div>
            <div class="bonus-btn-wrap">
                {!! $bonus->getRenderer()->renderPlayButton() !!}
                @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
            </div>
        </div>

    </div>
</div>
