@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="tel" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<div class="flex mb-1">
    <label id="login-form-email" class="input__wrap_modal mt-2"  style="width: 25%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="month" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="year" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <div style="width: 25%"></div>
</div>

<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">City:</span>
    <input type="text" class="input__field_modal" placeholder="city" name="city" value="{{$requisites['city']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Address Line 1:</span>
    <input type="text" class="input__field_modal" placeholder="address" name="address" value="{{$requisites['address']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="first name..." name="first_name" value="{{$requisites['first_name']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="last name..." name="last_name" value="{{$requisites['last_name']??''}}" required>
</label>
