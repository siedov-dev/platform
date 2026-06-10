@php
    /** @var \App\Models\Bonuses\KUserBonus $bonus */
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
@endphp
<div class="bonus_name">{{$bonus->getTitle()}}</div>
<div class="bonus_subtitle">{{__('bonus.for_deposit', ['amount' => $bonus->getJsonParam("activation_sum"), 'curr' => $bonus->getCurrencyName()])}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="params">
    <div class="param">{{__('bonus.common_conditions')}}:</div>
    <div class="param">🔻 {{__('bonus.min_dep')}}: <span class="value">{{$bonus->getJsonParam('activation_sum')}} {{$symbol}}</span></div>
    <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    <div class="param">🔄 {{__('bonus.spins_total')}}: <span class="value">{{$bonus->getAllSpinsCount()}}</span></div>

    @foreach(range($bonus->getJsonParam('day_s'), $bonus->getJsonParam('day_e')) as $day_n)
        @php
            $params = \App\Models\Bonuses\Params\BonusMultiDaysParam::getForDay($day_n);
            $curr_params = json_decode($params->getParams(), true);
            $curr_day = $bonus->currentDay();
            if ($bonus->status === \App\Models\Bonuses\KUserBonus::STATUS_DEPOSIT_LISTEN) {
                $curr_day++;
            }
        @endphp
        <div class="mt-1 @if($day_n < $curr_day) grayscale @endif">
            <div class="param">{{__('bonus.day_conditions', ['day' => $day_n - $bonus->getJsonParam('day_s') + 1])}}:</div>
            <div class="param">🎰 {{__('bonus.game')}}:  <span class="value">{{$params?->getGameName()}}</span></div>
            <div class="param">🔄 {{__('bonus.spins')}}:  <span class="value">{{$params->getSpins()}}</span></div>
            <div class="param">❎ {{__('bonus.wager')}}:  <span class="value">{{$params->getWager()}}</span></div>
            <div class="param">💸 {{__('bonus.max_win')}}:  <span class="value">{{$curr_params['max_win_'.$currency]??''}} {{$symbol}}</span></div>
            <div class="param">💰 {{__('bonus.stake')}}:  <span class="value">{{$curr_params['max_bet_'.$currency]??''}} {{$symbol}}</span></div>
        </div>
        @if($day_n === $bonus->currentDay() && $bonus->isActive() && $bonus->getSpinsAvailable() < 0)
            <div class="bonus-btn-main bonus-drop-btn mt-05" data-url="{{ action("GetBonusController@dropBonus", ['bonus_id' => $bonus->getId()], false) }}">
                <span>&times;<span>&nbsp;{{__('bonus.drop')}}
            </div>
        @endif
        @if($day_n === $bonus->currentDay() && $bonus->isActive())
            <div class="param">💫 {{$bonus->getProgressHint()}}</div>
        @endif
    @endforeach
</div>
