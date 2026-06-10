@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="receiver" value="{{$requisites['receiver']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Bank name:</span>
    <input type="text" class="input__field_modal" placeholder="bank name" name="extra_info[bank_name]" value="{{$requisites['extra_info']['bank_name']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Recipient name:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="extra_info[recipient_name]" value="{{$requisites['extra_info']['recipient_name']??''}}">
</label>
