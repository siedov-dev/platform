@php
    /** @var \App\Models\Bonuses\UserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
    $params = json_decode($bonus->json_params??'[]', true);
@endphp
<div class="conditions">{{__('bonus.conditions')}}</div>
<div class="bonus_name">{{$bonus->getName()}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="params">
    @if(!empty($game_name))
        <div class="param">🎰 {{__('bonus.game')}}: <span class="value">{{$game_name}}</span></div>
    @endif
        <div class="param">🔻 {{__('bonus.min_dep')}}: <span class="value">{{$bonus->getJsonParam('min_sum')}} {{$symbol}}</span></div>
        <div class="param">💸 {{__('bonus.money')}}: <span class="value">{{$params["money"]??''}}{{$symbol}}</span></div>
        <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
        @if($bonus->isActive())
            <div class="param">💫 {{$bonus->getProgressHint()}}</div>
        @endif
</div>
