@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" autocomplete="cc-name" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" class="input__field_modal" name="card_holder" value="{{$requisites['card_holder']??''}}"  required>
</label>
