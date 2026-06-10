@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account name:</span>
    <input type="text" class="input__field_modal" placeholder="account name" name="accountname" value="{{$requisites['accountname']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account Number:</span>
    <input type="text" class="input__field_modal" placeholder="account number" name="accountnumber" value="{{$requisites['accountnumber']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank name:</span>
    <input type="text" class="input__field_modal" placeholder="bank name" name="bankname" value="{{$requisites['bankname']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank ifsc:</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="bankifsc" value="{{$requisites['bankifsc']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank branch:</span>
    <input type="text" class="input__field_modal" placeholder="bank branch" name="bankbranch" value="{{$requisites['bankbranch']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">bank address:</span>
    <input type="text" class="input__field_modal" placeholder="bank address" name="bankaddress" value="{{$requisites['bankaddress']??''}}" required>
</label>
