@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account number:</span>
    <input type="text" class="input__field_modal" placeholder="account number" name="bank_account[account_number]" value="{{$requisites['bank_account[account_number]']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">ifsc:</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="bank_account[ifsc]" value="{{$requisites['bank_account']['ifsc']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="customer first name..." name="customer[first_name]" value="{{$requisites['customer']['first_name']??''}}">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="customer last name..." name="customer[last_name]" value="{{$requisites['customer']['last_name']??''}}">
</label>
