@php
/** @var \App\Models\Slc\SlotMachine\SlcSlotMachineCombination $combination */
@endphp
<div id="slot-machine-combinations" class="popup">
    <div class="popup__body">
        <div class="popup__content">
            <div class="for-scroll flex column jc-center ai-center mt-2">
                <h2>{{__('slotmachine.combinations')}}</h2>
                @foreach($combinations as $combination)
                    <div class="flex-rs">
                        <div class="shadow-block table-controls">
                            <div class="ai-center combinations-grid">
                                <div>
                                    <img src="/img/drum/{{$combination->getSymbol1()->value}}.png" width="60px" height="60px" class="mr-5">
                                </div>
                                <div>
                                    <img src="/img/drum/{{$combination->getSymbol2()->value}}.png" width="60px" height="60px" class="mr-5">
                                </div>
                                <div>
                                    <img src="/img/drum/{{$combination->getSymbol3()->value}}.png" width="60px" height="60px" class="mr-5">
                                </div>
                                <div>
                                    <h2>=</h2>
                                </div>
                                <div class="flex ai-center jc-fe">
                                    @if($combination->getSlcReward() === -1)
                                        <img src="/img/drum/jackpot.png" width="60px" height="60px" class="mr-5">
                                    @else
                                        <div class="flex ai-center">
                                            <h2>{{$combination->getSlcReward()}}</h2>
                                            <div class="mr-1"></div>
                                            <img src="/images/svg/reward-icon.svg" width="30px" height="30px" class="mr-5">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(\App\Helpers\Device::detect() === \App\Helpers\Device::PHONE)
                <div class="custom-select__dd-close-btn white" onclick="this.closest('.combinations-wrapper').classList.remove('shown');">&times;</div>
            @endif

            <div class="popup__close close-popup" onclick="this.closest('.combinations-wrapper').classList.remove('shown');">
                <img src="/images/svg/close-pop-up.svg" alt="" class="dekstop">
                {{--                <img src="/images/svg/close-pop-up-mobile.svg" alt="" class="phone">--}}
            </div>
        </div>
    </div>
</div>
