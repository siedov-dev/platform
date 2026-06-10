@php
    /** @var \App\Models\Bonuses\UserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
@endphp
<div class="conditions">{{__('bonus.conditions')}}</div>
<div class="bonus_name">{{$bonus->getName()}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="params">
    @if(!empty($game_name))
        <div class="param">💫 {{__('bonus.game')}} - {{$game_name}}</div>
    @endif
    @if($bonus->getJsonParam('freespins') > 0)
        <div class="param">💫 {{__('bonus.spins')}} - {{$bonus->getJsonParam('freespins')}}</div>
    @endif
    @if($bonus->isActive() || $bonus->isPaymentRequired())
        <div class="param">💫 {{__('bonus.wager')}} - x{{$bonus->multiplier}}</div>
    @endif
    @if($bonus->isPaymentRequired())
        <div class="param">💫 {{__('bonus.min_dep')}} {{$bonus->money_to_activate}} {{$symbol}}</div>
    @endif
    <div class="param">💫 {{__('bonus.max_win')}} - x3 {{__('bonus.from_dep_sum')}}</div>
    <div class="param">💫 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
