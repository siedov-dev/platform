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
        <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{$game_name}} </span></div>
    @else
        <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{__('games.allGames')}} {{__('games.category')}} {{__('games.slots')}}</span></div>
    @endif
    @if($bonus->getJsonParam('freespins') > 0)
            <div class="param">💫 {{__('bonus.spins')}} <span class="value"> {{$bonus->getJsonParam('freespins')}} </span></div>
    @endif
        <div class="param">💫 {{__('bonus.wager')}} <span class="value"> x{{$bonus->getJsonParam('wager')}} </span></div>
    <div class="param">💫 {{__('bonus.min_dep')}} <span class="value"> {{$bonus->getJsonParam('min_sum')}}{{$symbol}}</span></div>
    <div class="param">💫 {{__('bonus.max_dep')}} <span class="value"> {{$bonus->getJsonParam('max_sum')}}{{$symbol}}</span></div>
    <div class="param">💫 {{__('bonus.dep_multiplier')}} <span class="value"> {{$bonus->getJsonParam('multiplier')}}%</span></div>
    <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    @if($bonus->getJsonParam('max_x_win', 0) > 0)
        <div class="param">💫 {{__('bonus.max_win')}} <span class="value"> X{{$bonus->getJsonParam('max_x_win')}}{{$symbol}}</span></div>
    @elseif($bonus->getJsonParam('max_win', 0) > 0)
        <div class="param">💫 {{__('bonus.max_win')}} <span class="value"> {{$bonus->getJsonParam('max_win')}}{{$symbol}}</span></div>
    @endif
    @if($bonus->isActive())
            <div class="param">💫 {{__('bonus.progress')}} <span class="value"> {{$bonus->getProgressHint()}} {{$symbol}}</span></div>
    @endif
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
