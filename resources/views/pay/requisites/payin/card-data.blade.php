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
    <input type="tel" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="card_holder" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>
<div class="flex mb-1">
    <label id="login-form-email" class="input__wrap_modal mt-2"  style="width: 25%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="month" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="year" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <div style="width: 25%"></div>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">{{__('wallet.cvc')}}:</span>
        <input type="tel" class="input__field_modal" placeholder="cvv" pattern="\d{3,4}" inputmode="numeric" name="cvc" autocomplete="cc-csc" value="{{$requisites['cvc']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
</div>
