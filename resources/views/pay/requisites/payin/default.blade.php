{{--@php--}}
{{--    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';--}}
{{--    $currencyLabel = $currencyCode;--}}

{{--        $money_k = 1;--}}
{{--        $min_payin = 100;--}}

{{--        if (auth()->check() && !empty($method)){--}}
{{--            $pay_system = \App\Models\PaymentSystems::getPayinSystemForMethod($method);--}}
{{--            $min_payin = $pay_system->getMinSum();--}}
{{--            $max_payin = $pay_system->getMaxSum();--}}
{{--        }--}}
{{--    $minDeposit = $min_payin;--}}
{{--@endphp--}}
{{--<label id="login-form-email" class="input__wrap_modal">--}}
{{--    <span class="input__label_modal">{{ __('wallet.chooseDepositSum') }}:</span>--}}
{{--    <input type="number" min="1" class="input__field_modal" id="wallet__dep-amount" placeholder="1000" name="amount">--}}
{{--</label>--}}

{{--<div class="wallet__fast-sums">--}}
{{--    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = {{$minDeposit}}">{{$minDeposit}} {{ $currencyLabel }}</div>--}}
{{--    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = {{5 * $minDeposit}}">{{5 * $minDeposit}} {{ $currencyLabel }}</div>--}}
{{--    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = {{10 * $minDeposit}}">{{10 * $minDeposit}} {{ $currencyLabel }}</div>--}}
{{--</div>--}}

{{--<div class="wallet__hint">{{ __('wallet.fee') }}: 0%</div>--}}
