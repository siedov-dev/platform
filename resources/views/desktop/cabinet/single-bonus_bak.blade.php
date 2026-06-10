<?php
/** @var $bonus \App\Models\Bonuses\UserBonus */

$active = !in_array($bonus->status, [$bonus::STATUS_EXPIRED, $bonus::STATUS_DONE, $bonus::STATUS_CANCELED]);
$timeProgress = round((time() - strtotime($bonus->created_at))/($bonus->getExpirationDate()->timestamp - strtotime($bonus->created_at))*100);
?>

<div class="cabinet__bonuses-item cabinet__bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     style="@if(!$active) order: 100; opacity: 0.25; @endif"
     data-id="{{ $bonus->getId() }}">
    <div class="cabinet__bonus-status">
        @if($active)
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
        <div class="cabinet__bonus-info-btn" data-id="{{ $bonus->id }}" @if($bonus->getDescriptionName() !== null) data-bonus-id="{{$bonus->getDescriptionName()}}" @endif>
            <img src="/img/cabinet/bonuses/icons/info.svg">
            <div class="cabinet__bonus-info-dd">{{ $bonus->getDescription() }}</div>
        </div>

        @if($bonus->isCancelable())
            <div class="cabinet__bonus-delete-btn" data-url="{{ action("GetBonusController@cancelBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}"><img src="/img/cabinet/bonuses/icons/delete.svg"></div>
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
            <div class="cabinet__bonus-image-wrap cabinet__bonus-info-btn" data-id="{{ $bonus->id }}" @if($bonus->getDescriptionName() !== null) data-bonus-id="{{$bonus->getDescriptionName()}}" @endif>
                <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" class="cabinet__bonus-image">
            </div>

            @if ($bonus->isProgressAvailable())
                <div class="cabinet__bonus-wager">
                    <div class="cabinet__bonus-wager-value"><span class="cabinet__bonus-wager-green">{{ $bonus->getProgressPercents() }}</span>/%</div>
                    <div class="cabinet__bonus-wager-label">{{__('bonus.progress')}}</div>
                </div>
            @endif

            @if($bonus->isCancelable() || $bonus->getMoneyToPayout() > 0)
                @if($bonus->getMoneyToPayout() > 0)
                    <div class="cabinet__bonus-take-btn" data-url="{{ action("GetBonusController@transferBonusMoney", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}">Забрать</div>
                @endif
            @endif
            @if($bonus->isAgreementRequired())
                @if($bonus->isActivationMenu())
                    @php $menu = $bonus->getActivationMenu(); @endphp
                    @if(!empty($menu))
                        @foreach($menu as $menu_item => $options)
                            <button type="button" class="cabinet__bonus-menu-btn">{{$menu_item}}</button>
                            <div class="cabinet__bonus-modal-wrap">
                                <div class="cabinet__bonus-menu">
                                    @foreach($options as $k => $v)
                                        <div class="cabinet__bonus-menu-item" data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId(), 'option' => $v], false)}}">{{$k}}</div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endif
                @else
                    <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="cabinet__bonus-activate-btn">{{__('bonus.activate')}}</div>
                @endif
            @endif
            @if($bonus->isActive() && !$bonus->noPlayButton())
                @if($bonus->isDirectPlayUrl())
                    <div>
                        <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
                        <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" onclick="Overlay.hide()">{{ __('games.play') }}</div>
                    </div>
                @else
                    <a href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus" class="cabinet__bonus-play-btn">{{__('bonus.play')}}</a>
                @endif
            @endif
            @if($bonus->isPaymentRequired() || $bonus->type == \App\Models\Bonuses\UserBonus::EVENT_CASH_BACK || $bonus->type == \App\Models\Bonuses\UserBonus::TYPE_WEEKLY_CASH_BACK)
                <div class="wallet-selector__plus-button cabinet__bonus-btn">{{__('cabinet.deposit')}}</div>
            @endif
        </div>
    </div>
</div>
