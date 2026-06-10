@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">name:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="customer_name" value="{{$requisites['customer_name']??''}}">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">VPA:</span>
    <input type="text" class="input__field_modal" placeholder="UPI wallet number (VPA)" name="cardNumber" value="{{$requisites['vpa']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">address:</span>
    <input type="text" class="input__field_modal" placeholder="customer address..." name="customer_address" value="{{$requisites['customer_address']??''}}">
</label>
