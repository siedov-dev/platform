@php
$pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
$min_payout = $pay_system->getMinSum();
$max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">fio:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="payer_fio" value="{{$requisites['payer_fio']??''}}">
</label>
