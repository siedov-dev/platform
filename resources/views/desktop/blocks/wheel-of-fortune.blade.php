@php
    /** @var \App\Models\Slc\WheelSpinLog $todays_spin */
    $can_spin = false;
    $won = false;
    $user_free_spins = \App\MUser::getAuth()?->getValue('wheel_spins', 0)?? 0;
    $weekly_made_free_spins = \App\Models\UserWeeklyData::getIntValue(\App\MUser::getAuth()?->getId(), 'wheel_spin');
    $has_free_spins = $user_free_spins > 0 || $weekly_made_free_spins === 0;
    /** @var \App\Models\Slc\SlcConfig $slc_config */
    $slc_config = \App\Models\Slc\SlcConfig::first();
    if ($todays_spin === null || $user_free_spins > 0) $can_spin = true;
    // must take bonus before next spin
    if ($todays_spin !== null) $won = true;
    if ($todays_spin !== null && $todays_spin->getBonusId() === null) {
        $can_spin = false;
        $won = true;
    }
    $respin_coast = \App\Models\Slc\SlcConfig::getWheelRespinCost(\App\MUser::getAuth());
    $respin = \App\Models\UserDailyStat::getIntValue(\Illuminate\Support\Facades\Auth::id(), 'wheel_respin');
    $balance = \App\MUser::getAuth()->getSlcBalance();
    if ($balance < $slc_config->getSpinPrice() && !$has_free_spins) $can_spin = false;
@endphp
<script>window.wheel_spin = 1;</script>
<div id="wheel-of-fortune" class="wheel-of-fortune no-click-hide">
    <div class="wheel-bg">
        <img src="/img/wheel/bg_w.png">
    </div>
    <div class="drum-block">
        <div class="flex">
            <h1 class="mt-1 ml-1 relative">{{__('wheel.title_mobile')}}
                <a href="{{route('slc.page-info', ['page' => 'wheel'], false)}}" class="info-icon popup-link" data-ajax="modal" style="top:0; right: -2rem;"></a>
            </h1>
        </div>
        <img class="base" src="/img/wheel/base.png">
        <div class="wheel-drum">
            <img class="drum-bg" src="/img/wheel/drum.png">
            @include('common.blocks.wheel_text-only', ['names' => array_map(fn($name) => __($name), $prizes->pluck('bonus_title')->toArray()) ])
        </div>
        <img class="top" src="/img/wheel/top.png">
    </div>
    <div class="bottom-block">
        <div class="spin-controls">
            <button type="button" class="achievements__popup-button btn-wheel-spin @if($won && !$can_spin) hidden @elseif(!$can_spin) disabled @endif">
                {{__('wheel.spin')}} @if(!$has_free_spins)({{$slc_config->getSpinPrice()}} SLC)@endif
            </button>
            <div class="flex jc-center">
                <p class="wheel-of-fortune-popup__text-under-button text-fs @if($weekly_made_free_spins > 0) hidden @endif">{{__('wheel.have_spin')}}</p>
            </div>
            <div class="flex jc-center">
                <p class="wheel-of-fortune-popup__text-under-button text-spins @if($user_free_spins <= 0 || $won) hidden @endif">{{__('wheel.have_user_spins', ['amount' => $user_free_spins])}}</p>
            </div>
        </div>
        <div class="bonus-win-block">
            <div class="wheel-bonus-got flex column jc-center @if($todays_spin === null) hidden @endif">
                <div class="today-won @if($todays_spin?->getBonusId() === null) hidden @endif">
                    <div>{{__('wheel.today_received')}}</div>
                    <div class="text-bigger">{{__($todays_spin?->getBonusTitle())}}</div>
                    <p class="text-smaller">{{__('wheel.next_spin_in')}}:
                        <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">00:00:00</span>
                    </p>
                </div>
                <div class="now-won @if($todays_spin?->getBonusId() !== null) hidden @endif">
                    <div class="text-small">{{__('wheel.won')}}</div>
                    <div class="text-bigger">{{__($todays_spin?->getBonusTitle())}}</div>
                    <p class="text-smaller">{{__('wheel.next_spin_in')}}:
                        <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">00:00:00</span>
                    </p>
                    <div class="flex jc-sa flex-wrap respin-controls mb-2">
                        <form method="post" action="{{route('wheel.take-bonus', [], false)}}" class="get-bonus-form">
                            @csrf
                            <button type="submit" class="achievements__popup-button get-bonus-btn button_green" onclick="if(this.classList.contains('disabled')) return false; Modal.hide(this);">
                                {{__('wheel.take_bonus')}}
                            </button>
                        </form>
                        <button type="button" class="achievements__popup-button spin_again-bonus-btn ml-1
                            @if($balance < $respin_coast) disabled @endif
                            @if($respin > 1) hidden @endif
                        ">
                            {{__('wheel.spin_again')}} ({{$respin_coast}}&nbsp;SLC)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
