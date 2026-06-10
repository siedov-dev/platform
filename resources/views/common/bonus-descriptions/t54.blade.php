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
    <div class="param">💫 {{__('bonus.wager')}} - x{{$bonus->getJsonParam('wager')}}</div>
    <div class="param">💫 {{__('bonus.stake')}} - x{{$bonus->getJsonParam('money')}}</div>
    <div class="param">💫 {{__('bonus.max_win_k')}} - {{$bonus->getJsonParam('max_win_k')}}</div>
    <div class="param">💫 {{__('bonus.max_win')}} - {{$bonus->getJsonParam('max_win_sum')}}{{$symbol}}</div>
    <div class="param">💫 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
