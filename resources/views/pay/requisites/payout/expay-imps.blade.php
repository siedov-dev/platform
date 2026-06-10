@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Account number:</span>
    <input type="text" class="input__field_modal" placeholder="Account number" name="receiver" value="{{$requisites['receiver']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">ifsc:</span>
    <input type="text" class="input__field_modal" placeholder="ifsc" name="extra_info[ifsc_code]" value="{{$requisites['ifsc_code']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Recipient name:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="extra_info[recipient_name]" value="{{$requisites['recipient_name']??''}}">
</label>
