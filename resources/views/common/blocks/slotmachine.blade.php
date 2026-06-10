<div id="slot-machine" class="popup achievements__popup slot-machine-popup">
    <div class="popup__body">
        <div class="popup__content">
            <div class="combinations-wrapper">
                <div href="#" class="popup-link" onclick="this.parentElement.classList.add('shown')">
                    <img src="/images/icons/info-icon.png" alt="" class="desktop">
                    <img src="/images/icons/info-mobile-icon.png" alt="" class="mobile">
                    {{__('slotmachine.combinations')}}
                </div>
                <div class="combinations">
                    @include('common.blocks.sm-combinations')
                </div>
            </div>
{{--            <div class="tabs">--}}
{{--                <a href="#slot-machine" class="tabs__tab active popup-link">Slot Machine</a>--}}
{{--                <a href="#wheel-of-fortune" class="tabs__tab popup-link">Lucky Wheel</a>--}}
{{--            </div>--}}
            <h2>{{__('slotmachine.welcome')}}</h2>
            <h3>{{__('slotmachine.jackpot')}}</h3>
            <p class="slc-jackpot">{!!  App\Helpers\UI::moneyStr(\App\Models\Slc\SlcConfig::getParams()->getJackpot())!!} SLC</p>
            <div class="balance-wrap">
                <div class="page__head-block-wrap-price">
                    <p>{{__('slotmachine.bet')}}</p>
                    <div class="page__head-wrap-price">
                        <img src="/images/svg/balans-icon.svg" alt="">
                        {!! \App\Helpers\UI::renderSlcMoney(\App\Models\Slc\SlcConfig::getBetCost()) !!}
                    </div>
                </div>
                <div class="page__head-block-wrap-price slc-balance">
                    <p>{{__('wallet.balance')}}</p>
                    <div class="page__head-wrap-price">
                        <img src="/images/svg/balans-icon.svg" alt="">
                        {!! \App\Helpers\UI::renderSlcMoney(\App\MUser::getAuth()->getSlcBalance()) !!}
                    </div>
                </div>
            </div>
            @if(\App\Helpers\Device::detect() === \App\Helpers\Device::PHONE)
                <div class="custom-select__dd-close-btn white" onclick="Modal.hide(this.closest('.modal'));">&times;</div>
            @endif

            <div class="popup__close close-popup" onclick="Modal.hide(this.closest('.modal'))">
                <img src="/images/icons/close-popup-icon.png" alt="" class="dekstop">
                <img src="/images/svg/close-pop-up-mobile.svg" alt="" class="mobile">
            </div>
            <button type="button" class="slot-machine-popup__play slc-slotmachine-spin">
                <img src="/images/icons/slot-machine-popup-play.png" alt="">
            </button>
            <div class="slot-machine-popup__elements">
                <div class="page__head-block-wrap-price win">
                    <div class="page__head-wrap-price">
                        <p>+</p>
                        <img class="ml-1" src="/images/svg/balans-icon.svg" alt="">
                    </div>
                </div>

                <div class="error invisible">{{__('slotmachine.low_balance')}}</div>

                <div class="jackpot invisible">
                    <img class="" src="/img/drum/jackpot_win.webp" alt="">
                    <p class="amount">0</p>
                </div>

                @foreach(range(0,2) as $drum)
                    <div class="slot-machine-popup__element drum-{{$drum}}">
                        <div class="slot-machine-popup__columns">
                            @foreach(\App\Models\Slc\SlotMachine\SlcSlotMachineSymbol::getForDrum($drum) as $symbol)
                                <div class="slot-machine-popup__column">
                                    <img src="/img/drum/{{$symbol->getType()->value}}.png" alt="">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="achievements__popup-block-image">
                <img class="achievements__popup-bg desktop" src="/images/slot-machine-popup-content-bg.jpg" alt="">
                <img class="achievements__popup-bg mobile" src="/images/slot-machine-popup-content-bg-mobile.png" alt="">
            </div>
        </div>
    </div>
</div>
<script>
    for (let drum = 0; drum < 3; drum++) {
        let slots = document.querySelectorAll(`.slot-machine-popup__elements .drum-${drum} .slot-machine-popup__column`);
        slots.forEach((slot, index) => {
            slot.style.top = `${(index - 1.5) * slot.clientHeight}px`;
        });
    }
</script>
