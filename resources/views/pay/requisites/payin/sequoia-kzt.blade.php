@php
    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';
    $currencyLabel = $currencyCode;

        $money_k = 1;
        $min_payin = 100;

        if (auth()->check() && !empty($method)){
            $pay_system = \App\Models\PaymentSystems::getPayinSystemForMethod($method);
            $min_payin = $pay_system->getMinSum();
            $max_payin = $pay_system->getMaxSum();
        }
    $minDeposit = $min_payin;
@endphp
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="tel" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="add[card_number]" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<input type="hidden" name="add[wallet_provider]" value="bank">
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="add[sender_name]" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="text" class="input__field_modal" placeholder="email" name="add[email]" value="{{$email??''}}" required>
</label>
