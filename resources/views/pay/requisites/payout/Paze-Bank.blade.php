@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">ifsc:</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="ifsc" value="{{$requisites['ifsc']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank:</span>
    <input type="text" class="input__field_modal" placeholder="bank" name="bank" value="{{$requisites['bank']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account Number:</span>
    <input type="text" class="input__field_modal" placeholder="account_number" name="account_number" value="{{$requisites['account_number']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$requisites['phone']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$requisites['email']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="customer first name..." name="first_name" value="{{$requisites['first_name']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="customer last name..." name="last_name" value="{{$requisites['last_name']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Address Line 1:</span>
    <input type="text" class="input__field_modal" placeholder="customer addressLine1" name="addressLine1" value="{{$requisites['addressLine1']??''}}" required>
</label>
