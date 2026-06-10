@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (just numbers without +):</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="phone" value="{{$mobile??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label  class="input__wrap_modal mt-2">
    <span class="input__label_modal">Bank:</span>
    <select class="option__field_modal" name="bank">
        <option value="100000000010">PSB</option>
        <option value="100000000007">RAIF</option>
        <option value="100000000001">GAZPROM</option>
        <option value="100000000004">TINKOFF</option>
        <option value="100000000005">VTB</option>
        <option value="100000000008">ALFA</option>
        <option value="100000000015">OPEN</option>
        <option value="100000000111">SBER</option>
    </select>
</label>
