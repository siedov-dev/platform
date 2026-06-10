@if($bonus->isAgreementRequired())
    <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('cabinet.get')}}</div>
@endif
@if($bonus->isActive())
    <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
    <div class="game game__button game__play-real cabinet__bonus-play-btn hidden" data-url="{{ $bonus->getPlayUrl() }}"></div>
    <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-play-btn').click();Overlay.hide();">{{__('games.play')}}</div>
@endif
@if($bonus->isPaymentRequired())
    <div class="bonus-btn-main" onclick="wallet.showDeposit({{$bonus->getRequiredSum()}}); Modal.hide(this);">{{__('cabinet.deposit')}}</div>
@endif
@if($bonus->isListeningDeposit())
    <a class="bonus-btn-main" href="{{route('bonus.multiday-delay', ['id' => $bonus->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup" onclick="Modal.hide(this)">
        {{__('games.play')}}
    </a>
@endif
