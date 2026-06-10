@if(($bonus->isAgreementRequired() || $bonus->isPending()) && $bonus->tries > 3)
    <a class="bonus-btn-main" href="{{route('wb.nondep', ['bonus_id' => $bonus->getId()], false)}}" onclick="Modal.hide(this)" data-ajax="modal" data-popup-type="bottom-popup">
        {{__('cabinet.get')}}
    </a>
@endif
@if($bonus->isAgreementRequired() && $bonus->tries < 4)
    <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('cabinet.get')}}</div>
@endif
@if($bonus->isLocked())
    <div class="bonus-btn-main">
        <img src="/img/bonuses/lock.svg" alt="locked">
    </div>
@endif
@if($bonus->isPaymentRequired())
    <a class="bonus-btn-main" href="{{route('wb.deposit', ['bonus_id' => $bonus->getId()], false)}}" onclick="Modal.hide(this)" data-ajax="modal" data-popup-type="bottom-popup">
        {{__('cabinet.get')}}
    </a>
@endif
@if($bonus->isPaused())
    <div data-url="{{action("GetBonusController@resumeBonus", ['id' => $bonus->getId()], false)}}" class="cabinet__bonus-activate-btn bonus-btn-main">{{__('bonus.resume')}}</div>
@endif
