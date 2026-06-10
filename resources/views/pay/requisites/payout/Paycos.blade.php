@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank code (ifsc):</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="ifsc_code" value="{{$requisites['ifsc_code']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account Number:</span>
    <input type="text" class="input__field_modal" placeholder="account_number" name="bank_account_number" value="{{$requisites['bank_account_number']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank name:</span>
    <input type="text" class="input__field_modal" placeholder="bank_name" name="bank_name" value="{{$requisites['bank_name']??''}}" required>
</label>
<input type="hidden" name="transfer_type" value="{{$transfer_type}}">
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$requisites['phone']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Name:</span>
    <input type="text" class="input__field_modal" placeholder="name" name="name" value="{{$requisites['name']??''}}" required>
</label>
