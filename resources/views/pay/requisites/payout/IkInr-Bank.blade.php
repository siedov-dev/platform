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
    <span class="input__label_modal">bank acccount No:</span>
    <input type="text" class="input__field_modal" placeholder="bank acccount No" name="cardNumber" value="{{$requisites['bank_account']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">ifsc:</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="ifsc" value="{{$requisites['ifsc']??''}}">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">address:</span>
    <input type="text" class="input__field_modal" placeholder="customer address..." name="customer_address" value="{{$requisites['customer_address']??''}}">
</label>
{{--<label id="login-form-email" class="input__wrap_modal mt-2">--}}
{{--    <span class="input__label_modal">ifsc:</span>--}}
{{--    <input type="text" class="input__field_modal" placeholder="ifsc" name="ifsc" value="{{$requisites['ifsc']??''}}">--}}
{{--</label>--}}
{{--<label id="login-form-email" class="input__wrap_modal mt-2">--}}
{{--    <span class="input__label_modal">Wallet:</span>--}}
{{--    <input type="text" class="input__field_modal" placeholder="Wallet" name="wallet" value="{{$requisites['wallet']??''}}">--}}
{{--</label>--}}
{{--<label id="login-form-email" class="input__wrap_modal mt-2">--}}
{{--    <span class="input__label_modal">First name:</span>--}}
{{--    <input type="text" class="input__field_modal" placeholder="customer first name..." name="first_name" value="{{$requisites['first_name']??''}}">--}}
{{--</label>--}}
{{--<label id="login-form-email" class="input__wrap_modal mt-2">--}}
{{--    <span class="input__label_modal">Last name:</span>--}}
{{--    <input type="text" class="input__field_modal" placeholder="customer last name..." name="last_name" value="{{$requisites['last_name']??''}}">--}}
{{--</label>--}}
