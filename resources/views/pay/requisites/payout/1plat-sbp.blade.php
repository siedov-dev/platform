@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>

<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">Номер телефона:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="purse" value="{{$mobile??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<input type="hidden" name="payment_credentials_type" value="sbp">
<label id="login-form-email" class="input__wrap_modal mt-2">
    <select class="input__field_modal" name="bank" required>
        @foreach($banks as $id => $name)
            <option value="{{$name}}">{{$name}}</option>
        @endforeach
    </select>
</label>
