@if($bonus->isAgreementRequired())
    @if($bonus->isActivationMenu())
        @php $menu = $bonus->getActivationMenu(); @endphp
        @if(!empty($menu))
            @foreach($menu as $menu_item => $options)
                <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-menu-btn').click()">{{$menu_item}}</div>
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
        <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('cabinet.get')}}</div>
    @endif
@endif
@if($bonus->isLocked())
    <div class="bonus-btn-main" onclick="shortMessage('{{__("bonus.bonus_locked")}}');">
        <img src="/img/bonuses/lock.svg" alt="locked">
    </div>
@endif
@if($bonus->isActive() && !$bonus->noPlayButton())
    @if($bonus->isDirectPlayUrl())
        <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
        <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" style="display: none;"></div>
        <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-play-btn').click();Overlay.hide();">{{__('games.play')}}</div>
{{--    @elseif($bonus->allGamesAllowed())--}}
{{--        <a class="bonus-btn-main" href="{{route('games', [], false)}}" data-url="{{route('games', [], false)}}">{{__('bonus.play')}}</a>--}}
    @else
        <a class="bonus-btn-main" href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus">{{__('bonus.play')}}</a>
    @endif
@endif
@if($bonus->isPaused())
    <div data-url="{{action("GetBonusController@resumeBonus", ['id' => $bonus->getId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('bonus.resume')}}</div>
@endif
@if($bonus->isPaymentRequired() || $bonus->type == \App\Models\Bonuses\UserBonus::TYPE_WEEKLY_CASH_BACK)
    <div class="bonus-btn-main" onclick="wallet.showDeposit({{$bonus->getRequiredSum()}}); Modal.hide(this)">{{__('cabinet.deposit')}}</div>
@endif
@if($bonus->status === \App\Models\Bonuses\UserBonus::STATUS_FILTHY)
    @if($bonus->getMoneyToPayout() > 0)
        <div class="bonus-btn-main cabinet__bonus-take-btn" data-url="{{ action("GetBonusController@transferBonusMoney", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}">{{__('bonuses.gift-take')}}</div>
    @else
        <div class="bonus-btn-main disabled">{{__('bonus.take')}}</div>
    @endif
@endif
