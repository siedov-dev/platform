@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>


<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">Номер телефона:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="payout_data[phone_to]" value="{{$requisites['payout_data']['phone_to']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
