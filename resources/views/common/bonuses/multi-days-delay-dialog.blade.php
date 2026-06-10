<div class="cabinet__bonuses-item bonus-multi-days-form no-click-hide">
    <div class="title mt-2">{{$bonus->getTitle()}}</div>
    <div class="sub-title">{{__('bonus.for_deposit', ['amount' => $bonus->getJsonParam("activation_sum"), 'curr' => $bonus->getCurrencyName()])}}</div>
    <div class="days-wrapper mt-1">
        @foreach(range($bonus->getJsonParam('day_s'), $bonus->getJsonParam('day_e')) as $day_n)
            @php
                $params = \App\Models\Bonuses\Params\BonusMultiDaysParam::getForDay($day_n);
            @endphp
            <div class="day @if($bonus->currentDay() === $day_n - 1) active @endif relative">
                @if($bonus->currentDay() >= $day_n)<img class="status-icon" src="/img/icons/checked.svg">@endif
                @if($bonus->currentDay() + 1 < $day_n) <img class="status-icon" src="/img/icons/lock.svg"> @endif
                {{__('bonus.day')}}&nbsp;{{$day_n - $bonus->getJsonParam('day_s') + 1}}
                <div class="spins flex ai-center">
                    <img src="/img/landing/fs.svg">&nbsp;{{$params->getSpins()}}
                </div>
            </div>
        @endforeach
    </div>
    <div class="timeout mt-2">
        <div>
            <img src="/img/icons/time.svg"> {{__('bonus.multi_day-next_day_in')}}
        </div>
        <div class="time update-timer-block" data-target_time="{{$bonus->completed_at}}">
            <div><span class="timer-hours-left">-</span>h</div>&nbsp;:&nbsp;
            <div><span class="timer-minutes-left">-</span>m</div>
        </div>
    </div>
    <div class="mt-2 delay-info">{{__('bonus.delay_info', ['amount' => $bonus->getJsonParam("buy_sum"),
                                                            'curr' => $bonus->getCurrencyName(),
                                                            'day' => $bonus->currentDay() + 1,
                                                            ])}}</div>
    <div class="flex jc-center">
        <button type="submit" class="bonus_yes_btn" onclick="Modal.hide(this); wallet.showDeposit();">
            {{ __('bonus.remove_delay') }}
        </button>
    </div>
</div>
