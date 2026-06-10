@php
    /** @var \App\Models\Bonuses\NonDepositBonus $bonus */
    $params = json_decode($bonus->json_params??'{}', true);
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
    $levels = \App\Models\BonusMenuParam::getBonusParams(\App\Models\Bonuses\UserBonus::TYPE_NON_DEPOSIT, $bonus->currency);
@endphp
<div class="conditions">{{__('bonus.conditions')}}</div>
<div class="bonus_name">{{$bonus->getName()}}</div>
@if($bonus->id !== null)
    <div class="param text-gray">id-{{$bonus->id}}</div>
@endif
<div class="param">{{__('bonus.bonus_2_descr1', ['prc' => round($bonus->multiplier * 100)])}}</div>
<div class="params">
    @foreach($levels as $level)
        <div class="param"> {{__('bonus.min_dep')}} {{\App\Helpers\UI::moneyStr($level['min_value'])}}{{$symbol}} - {{(int)$level['value']}}%</div>
    @endforeach
</div>
<div class="params">
    @if(!empty($game_name))
        <div class="param">💫 {{__('bonus.game')}} - {{$game_name}}</div>
    @endif
    <div class="param">💫 {{__('bonus.money')}} - {{\App\Helpers\UI::moneyStr($bonus->getMoneyToPayout())}}{{$symbol}}</div>
    <div class="param">💫 {{__('bonus.payed')}} - {{\App\Helpers\UI::moneyStr($bonus->money_paid_out)}}{{$symbol}}</div>
    <div class="param">💫 {{__('bonus.available')}} - {{$params['money']}}{{$symbol}}</div>
{{--    <div class="param">💫 {{__('bonus.wager')}} - x{{$params['wager']}}</div>--}}
    <div class="param">📅 {{__('bonus.available-in')}} - <span class="time-estimated" data-target_time="{{$bonus->getExpirationDate()}}" data-separator=":"></span></div>
    @if($bonus->isActive())
        <div class="param">💫 {{$bonus->getProgressHint()}}</div>
    @endif
</div>
