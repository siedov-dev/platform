@if($bonus->isAgreementRequired())
    <a class="bonus-btn-main" href="{{route('wb.deposit', ['bonus_id' => $bonus->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
        {{__('cabinet.get')}}
    </a>
@endif
@if($bonus->isLocked())
    <div class="bonus-btn-main">
        <img src="/img/bonuses/lock.svg" alt="locked">
    </div>
@endif
@if($bonus->isActive())
    @if($bonus->isDirectPlayUrl())
        <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
        <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" style="display: none;"></div>
        <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-play-btn').click();Overlay.hide();">{{__('games.play')}}</div>
    @else
        <a class="bonus-btn-main" href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus">{{__('bonus.play')}}</a>
    @endif
@endif
@if($bonus->isPaymentRequired())
    <a class="bonus-btn-main" href="{{route('wb.deposit', ['bonus_id' => $bonus->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
        {{__('cabinet.deposit')}}
    </a>
@endif
@if($bonus->isPaused())
    <div data-url="{{action("GetBonusController@resumeBonus", ['id' => $bonus->getId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('bonus.resume')}}</div>
@endif
