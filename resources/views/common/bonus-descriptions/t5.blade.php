@php
    /** @var \App\Models\Bonuses\UserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
    $params = json_decode($bonus->json_params??'[]', true);
@endphp
<div class="bonus_name">{{$bonus->getName()}}</div>
<div class="bonus_subtitle">{{__('bonuses.plusMinusDesc')}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="params">
    @if($bonus->id !== null)
        <div class="param">id-{{$bonus->id}}</div>
    @endif
    @if(!empty($game_name))
        <div class="param">🎰 {{__('bonus.game')}} - {{$game_name}}</div>
    @endif
        <div class="param">💸 {{__('bonus.money')}}: {{$params["money"]??''}}{{$symbol}}</div>
        <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
