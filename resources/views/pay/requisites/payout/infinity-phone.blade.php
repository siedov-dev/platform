@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>

<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">Номер телефона:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="payment_credentials[number]" value="{{$mobile??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<input type="hidden" name="payment_credentials[payment_credentials_type]" value="card">
<label id="login-form-email" class="input__wrap_modal mt-2">
    <select class="input__field_modal" name="pay_method_id" required>
        @foreach($methods as $id => $name)
            <option value="{{$id}}">{{$name}}</option>
        @endforeach
    </select>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">ФИО:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="payment_credentials[account_holder]" value="{{$requisites['payment_credentials']['account_holder']??''}}">
</label>
