<?php
/** @var $bonus \App\Models\Bonuses\Foreign\ForeignBonusInterface */

$active = $bonus->isActive() | $bonus->canBeActivated();
$timeProgress = round((time() - strtotime($bonus->created_at))/($bonus->getExpirationDate()->timestamp - strtotime($bonus->created_at))*100);
?>

<div class="cabinet__bonuses-item cabinet__bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled" style="order: 100; opacity: 0.25;' }}" data-id="{{ $bonus->getId() }}">
    <div class="cabinet__bonus-status">
        @if($bonus->isActive() || $bonus->canBeActivated())
            <div class="status-circle status-circle_green cabinet__bonus-status-icon"></div>
            <span class="text-green">{{ $bonus->getStatusName() }}</span>
        @elseif($bonus->isDone())
            <div class="status-circle status-circle_gray cabinet__bonus-status-icon"></div>
            <span class="text-gray">{{ $bonus->getStatusName() }}</span>
        @elseif($bonus->isExpired())
            <div class="status-circle status-circle_gray cabinet__bonus-status-icon"></div>
            <span class="text-gray">{{ $bonus->getStatusName() }}</span>
        @else
            <div class="status-circle status-circle_orange cabinet__bonus-status-icon"></div>
            <span class="text-orange">{{ $bonus->getStatusName() }}</span>
        @endif
    </div>

    <div class="cabinet__bonus-corner">
        <div class="cabinet__bonus-info-btn" data-id="{{ $bonus->id??0 }}" @if($bonus->getDescriptionName() !== null) data-bonus-id="{{$bonus->getDescriptionName()}}" @endif>
            <img src="/img/cabinet/bonuses/icons/info.svg">
            <div class="cabinet__bonus-info-dd">{{ $bonus->getDescription() }}</div>
        </div>

        @if($bonus->isCancelable())
            <div class="cabinet__bonus-delete-btn" data-url="{{$bonus->cancelUrl()}}"><img src="/img/cabinet/bonuses/icons/delete.svg"></div>
        @endif
    </div>


    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-left">
            <div class="cabinet__bonus-title">{{ $bonus->getName() }}</div>

            <div class="cabinet__bonus-balance">
                <div class="cabinet__bonus-balance-name">{{$bonus->getBalanceName()}}</div>
                <div class="cabinet__bonus-balance-value">{{$bonus->getBalanceString()}}</div>
            </div>

            <div class="cabinet__bonus-plus">{{ $bonus->getProgressHint() }}</div>


            @if(!$bonus->isDone() && !$bonus->isExpired() && !$bonus->isCanceled())
                <div class="cabinet__bonus-countdown">{{$bonus->getExpirationDate()->format('Y-m-d H:i:s')}}</div>
                <div class="cabinet__bonus-progress progress-bar">
                    <div class="progress-bar__track progress-bar__track_green" style="width: {{ $timeProgress }}%"></div>
                </div>
            @endif

        </div>
        <div class="cabinet__bonus-right">
            <div class="cabinet__bonus-image-wrap">
                <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" class="cabinet__bonus-image">
            </div>

            @if ($bonus->isProgressAvailable())
                <div class="cabinet__bonus-wager">
                    <div class="cabinet__bonus-wager-value"><span class="cabinet__bonus-wager-green">{{ $bonus->getProgressPercents() }}</span>/%</div>
                    <div class="cabinet__bonus-wager-label">Прогресс:</div>
                </div>
            @endif

            @if($bonus->isAgreementRequired())
                <div data-url="{{$bonus->activateUrl()}}" class="cabinet__bonus-activate-btn">{{$bonus->getActivationString()}}</div>
            @endif
            @if($bonus->isActive())
                @if($bonus->isDirectPlayUrl())
                    <div>
                        <div class="game__name" style="display: none">{{$bonus->getAllowedGames()->current()?->getName()??''}}</div>
                        <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" onclick="Overlay.hide()">{{ __('games.play') }}</div>
                    </div>
                @else
                    <a href="{{route('games', ['category' => 'bonus', 'id' => $bonus->getId(), 'provider' => $bonus->getProviderId()], false)}}" data-url="{{route('games', ['category' => 'bonus', 'id' => $bonus->getId(), 'provider' => $bonus->getProviderId()], false)}}" class="cabinet__bonus-play-btn">{{__('bonus.play')}}</a>
                @endif
            @endif
        </div>
    </div>
</div>
