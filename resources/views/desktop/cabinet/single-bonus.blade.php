<?php
/** @var $bonus \App\Models\Bonuses\UserBonus */

$active = !in_array($bonus->status, [$bonus::STATUS_EXPIRED, $bonus::STATUS_DONE, $bonus::STATUS_CANCELED]);
$timeProgress = round((time() - strtotime($bonus->created_at))/($bonus->getExpirationDate()->timestamp - strtotime($bonus->created_at))*100);
$bonus->isLocked();
?>
<div class="cabinet__bonuses-wrapper">
<div class="cabinet__bonuses-item cabinet__bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     style='@if(!$active) order: 100; opacity: 0.5; @endif background-image: url("{{$bonus->getImgUrl(\App\Helpers\UI::getTheme())}}");'
     data-id="{{ $bonus->getId() }}"
>

    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-time">
            <img src="/img/bonuses/clock.png">
            <div>{{\Carbon\Carbon::parse($bonus->created_at)->isoFormat('DD MMM')}}-{{\Carbon\Carbon::parse($bonus->active_to)->isoFormat('DD MMM YYYY')}}</div>
        </div>
        <div class="cabinet__bonus-title">{{ $bonus->getTitle() }}</div>
{{--        @if($bonus->isCancelable() || $bonus->getMoneyToPayout() > 0)--}}
{{--            @if($bonus->getMoneyToPayout() > 0)--}}
{{--                <div class="cabinet__bonus-take-btn" data-url="{{ action("GetBonusController@transferBonusMoney", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}">Забрать</div>--}}
{{--            @endif--}}
{{--        @endif--}}
        @if($bonus->isAgreementRequired())
            @if($bonus->isActivationMenu())
                @php $menu = $bonus->getActivationMenu(); @endphp
                @if(!empty($menu))
                    @foreach($menu as $menu_item => $options)
                        <div class="bonus-btn-wrap">
                            <div class="bonus-btn-main" onclick="this.parentElement.parentElement.querySelector('.cabinet__bonus-menu-btn').click()">{{$menu_item}}</div>
                            @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
                        </div>
                        <button type="button" class="cabinet__bonus-menu-btn" style="display: none;">{{$menu_item}}</button>
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
                <div class="bonus-btn-wrap">
                    <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('cabinet.get')}}</div>
{{--                    <div class="bonus-btn-left-border"></div>--}}
{{--                    <div class="bonus-btn-info" onclick="this.closest('.cabinet__bonuses-wrapper').classList.toggle('info-shown')">--}}
{{--                        <div class="bonus-info-arrow"></div>--}}
{{--                    </div>--}}
                    @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
                </div>
            @endif
        @endif
        @if($bonus->isActive() && !$bonus->noPlayButton())
            @if($bonus->isDirectPlayUrl())
                <div>
                    <div class="bonus-btn-wrap">
                        <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
                        <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" style="display: none;"></div>
                        <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-play-btn').click();Overlay.hide();">{{__('games.play')}}</div>
                        @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
                    </div>

                </div>
            @else
                <div class="bonus-btn-wrap">
                    <a class="bonus-btn-main" href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus">{{__('bonus.play')}}</a>
                    @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
                </div>
            @endif
        @endif
        @if($bonus->isPaymentRequired() || $bonus->type == \App\Models\Bonuses\UserBonus::EVENT_CASH_BACK || $bonus->type == \App\Models\Bonuses\UserBonus::TYPE_WEEKLY_CASH_BACK)
            <div class="bonus-btn-wrap">
                <div class="bonus-btn-main" onclick="document.dispatchEvent(new CustomEvent('showDeposit'))">{{__('cabinet.deposit')}}</div>
                @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
            </div>
        @endif
        @if($bonus->status === \App\Models\Bonuses\UserBonus::STATUS_FILTHY)
            <div class="bonus-btn-wrap">
                @if($bonus->getMoneyToPayout() > 0)
                    <div class="bonus-btn-main cabinet__bonus-take-btn" data-url="{{ action("GetBonusController@transferBonusMoney", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}">{{__('bonuses.gift-take')}}</div>
                @else
                    <div class="bonus-btn-main disabled">{{__('bonus.take')}}</div>
                @endif
                @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
            </div>
        @endif
    </div>

</div>
    <div class="bonus-info-wrapper">
        @if($bonus->isCancelable())
            <div class="cabinet__bonus-delete-btn" data-url="{{ action("GetBonusController@cancelBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}">
                <img src="/img/cabinet/bonuses/icons/delete.svg">
            </div>
        @endif
        <div class="bonus-info-content">
            {!! $bonus->getShortDescription(); !!}
        </div>
    </div>
</div>
