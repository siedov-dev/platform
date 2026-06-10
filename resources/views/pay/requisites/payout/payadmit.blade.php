@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" value="{{$requisites['cardNumber']??''}}" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<div class="flex jc-sb w-100 mt-1">
    <label id="login-form-email" class="input__wrap_modal"  style="width: 45%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="month" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal ml-1" style="width: 45%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="year" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <div style="width: 25%"></div>
</div>
<label id="login-form-email" class="input__wrap_modal mt-1">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" autocomplete="cc-name" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" class="input__field_modal" name="card_holder" value="{{$requisites['card_holder']??''}}"  required>
</label>
{{--<div class="flex mt-1 w-100">--}}
{{--    <label class="input__wrap_modal" style="width: 45%">--}}
{{--        <span class="input__label_modal">First name:</span>--}}
{{--        <input type="text" class="input__field_modal" placeholder="first name" name="first_name" value="{{$requisites['first_name']??''}}" required>--}}
{{--    </label>--}}
{{--    <label class="input__wrap_modal ml-1" style="width: 45%">--}}
{{--        <span class="input__label_modal">Last name:</span>--}}
{{--        <input type="text" class="input__field_modal" placeholder="last name" name="last_name" value="{{$requisites['last_name']??''}}" required>--}}
{{--    </label>--}}
{{--</div>--}}
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="email" name="email" value="{{$email??\App\MUser::getAuth()?->email??''}}" required>
</label>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">phone:</span>
    <input type="tel" class="input__field_modal" autocomplete="phone" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
