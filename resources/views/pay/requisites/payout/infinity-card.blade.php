@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>

<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="payment_credentials[number]" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
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
