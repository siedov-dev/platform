@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (just numbers without +):</span>
    <input type="text" class="input__field_modal" placeholder="77712345678" name="phone" value="{{$mobile??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
