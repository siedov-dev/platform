@php
// deprecated
// see desktop|mobile version
/** @var \App\Models\Slc\WheelSpinLog $todays_spin */
$can_spin = false;
$won = false;
$user_spins = \App\MUser::getAuth()?->getValue('wheel_spins', 0)?? 0;
if ($todays_spin === null || $user_spins > 0) $can_spin = true;
// must take bonus before next spin
if ($todays_spin && $todays_spin->getBonusId() === null) {
    $can_spin = false;
    $won = true;
}
$respin_coast = \App\Models\Slc\SlcConfig::getWheelRespinCost(\App\MUser::getAuth());
$balance = \App\MUser::getAuth()->getSlcBalance();
@endphp
<div id="wheel-of-fortune" class="popup achievements__popup wheel-of-fortune-popup" style="position: relative;">
    <div class="popup__body flex column">
        <div class="popup__content no-click-hide">
            <h2>
                <span class="desktop relative">{{__('wheel.title_desktop')}}
                    <a href="{{route('slc.page-info', ['page' => 'wheel'], false)}}" class="info-icon popup-link" data-ajax="modal" style="top:0; right: -1.5rem;"></a>
                </span>
                <span class="mobile ml-1 relative">{{__('wheel.title_mobile')}}
                    <a href="{{route('slc.page-info', ['page' => 'wheel'], false)}}" class="info-icon popup-link" data-ajax="modal" style="top:0; right: -1.5rem;"></a>
                </span>
            </h2>
{{--            <div class="tabs">--}}
{{--                <a href="#slot-machine" class="tabs__tab popup-link">Slot Machine</a>--}}
{{--                <a href="#wheel-of-fortune" class="tabs__tab active popup-link">Lucky Wheel</a>--}}
{{--            </div>--}}
            <div class="wheel-of-fortune-popup__block-baraban">
                @include('common.blocks.wheel', ['names' => array_map(fn($name) => __($name), $prizes->pluck('bonus_title')->toArray()) ])
                @include('common.blocks.wheel-arrow', ['sections' => $prizes->count()])
                <img class="wheel-of-fortune-popup__central-circle-baraban" src="/images/central-circle-baraban.png" alt="">
            </div>
            <div class="spin-controls">
                <button type="button" class="achievements__popup-button btn-wheel-spin @if($won) hidden @elseif(!$can_spin) disabled @endif">
                    {{__('wheel.spin')}}
                </button>
                <p class="wheel-of-fortune-popup__text-under-button text-fs @if($todays_spin !== null) hidden @endif">{{__('wheel.have_spin')}}</p>
                <p class="wheel-of-fortune-popup__text-under-button text-spins @if($user_spins <= 0 || $won) hidden @endif">{{__('wheel.have_user_spins', ['amount' => $user_spins])}}</p>
            </div>
            @if(\App\Helpers\Device::detect() === \App\Helpers\Device::PHONE)
                <div class="custom-select__dd-close-btn white" onclick="Modal.hide(this.closest('.modal'));">&times;</div>
            @endif
            <div class="popup__close close-popup" onclick="Modal.hide(this.closest('.modal'))">
                <img src="/images/svg/close-pop-up.svg" alt="" class="dekstop">
{{--                <img src="/images/svg/close-pop-up-mobile.svg" alt="" class="phone">--}}
            </div>
            <div class="achievements__popup-block-image">
                <img class="achievements__popup-bg desktop" src="/images/wheel-of-fortune-popup-content-bg.png" alt="">
                <img class="achievements__popup-bg mobile" src="/images/wheel-of-fortune-popup-content-bg-mobile.jpg" alt="">
            </div>
        </div>
        <div class="bonus-win-block">
            <div class="wheel-bonus-got flex column jc-center @if($todays_spin === null) hidden @endif">
                    <div class="today-won @if($todays_spin?->getBonusId() === null) hidden @endif">
                        <div>{{__('wheel.today_received')}}</div>
                        <div class="text-bigger">{{__($todays_spin?->getBonusTitle())}}</div>
                        <p class="text-smaller">{{__('wheel.next_spin_in')}}:
                            <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">14:36:59</span>
                        </p>
                    </div>
                    <div class="now-won @if($todays_spin?->getBonusId() !== null) hidden @endif">
                        <div class="text-small">{{__('wheel.won')}}</div>
                        <div class="text-bigger">{{__($todays_spin?->getBonusTitle())}}</div>
                        <p class="text-smaller">{{__('wheel.next_spin_in')}}:
                            <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">14:36:59</span>
                        </p>
                        <div class="flex jc-sa flex-wrap">
                            <form method="post" action="{{route('wheel.take-bonus', [], false)}}" class="get-bonus-form">
                                @csrf
                                <button type="submit" class="achievements__popup-button get-bonus-btn button_green" onclick="return !this.classList.contains('disabled')">
                                    {{__('wheel.take_bonus')}}
                                </button>
                            </form>
                            <button type="button" class="achievements__popup-button spin_again-bonus-btn ml-1 @if($balance < $respin_coast) disabled @endif">
                                {{__('wheel.spin_again')}} ({{$respin_coast}}&nbsp;SLC)
                            </button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
<script>window.wheel_spin = 1;</script>
