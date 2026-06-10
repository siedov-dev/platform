@php
    /** @var \App\Models\BonusEvents\BonusEvent $event */
    $symbol = \Illuminate\Support\Facades\Auth::user()?->getCurrencyName()??'';

    $user = \App\MUser::getAuth();

            $money_k = \App\Models\BonusCurrencyConfig::getMoneyK($user->currency);
            $max_sum = $event->max_sum;
            $min_sum = $event->min_sum;
            $koef = $event->koef;

            $params = json_decode($event->json_params, true);
            if ($params != null && is_array($params)) {
                if (isset($params['params_' . \App\UserWallet::currencyName($user->currency)])) {
                    $money_k = 1;
                    $max_sum = $params['params_' . \App\UserWallet::currencyName($user->currency)]['max_sum'];
                    $min_sum = $params['params_' . \App\UserWallet::currencyName($user->currency)]['min_sum'];
                    $koef = $params['params_' . \App\UserWallet::currencyName($user->currency)]['koef'];
                }
            } else {
                $min_sum = $min_sum * $money_k;
                $max_sum = $max_sum * $money_k;
            }

            $descr = __('event-1.description', [
                'min_sum' => number_format($min_sum, 2, '.', ''),
                'max_sum' => number_format($max_sum, 2, '.', ''),
                'coef' => $koef,
                'curr' => $user->getCurrencyName(),
            ]);
@endphp
{{--<div class="bonus_name">{{$event->getName()}}</div>--}}
<div class="conditions">{{__('bonus.conditions')}}</div>
<div class="params">{!! $descr !!}</div>
<div class="params">
    @if(!empty($game_name))
        <div class="param">💫 {{__('bonus.game')}} - {{$game_name}}</div>
    @endif
        <div class="param">💫 {{__('bonus.min_dep')}} - {{$min_sum}}&nbsp;{{$symbol}}</div>
        <div class="param">💫 {{__('bonus.max_dep')}} - {{$max_sum}}&nbsp;{{$symbol}}</div>
        <div class="param">💫 {{__('bonus.available_to')}} - {{$event->end_time->isoFormat('DD.MM.YYYY')}}</div>
</div>
@if($event->getActivationsLeft() >= 0)
    <div class="cabinet__bonus-balance-value">{{__('bonus.activations_available')}}: {{$event->getActivationsLeft()}}</div>
@endif
