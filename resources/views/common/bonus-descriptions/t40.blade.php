@php
    /** @var \App\Models\Bonuses\UserBonus $bonus */
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
@endphp
<div class="conditions">{{__('bonus.conditions')}}</div>
<div class="bonus_name">{{$bonus->getName()}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="params">
    <div class="param">💫 {{__('bonus.weekly_cashback')}} - {{\App\Helpers\UI::moneyStr($bonus->money_available) . $symbol}}</div>
    <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    <div class="param mt-1">{{__('bonus.weekly_cashback_get')}}</div>
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
