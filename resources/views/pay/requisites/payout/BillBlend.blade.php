@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>

<div class="flex mb-1">
    <label id="login-form-email" class="input__wrap_modal mt-2"  style="width: 25%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="exp_month" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="exp_year" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <div style="width: 25%"></div>
</div>

<label class="input__wrap_modal mt-1 reg-form__name">
    <span class="input__label_modal">{{__('reg.name')}}:</span>
    <input type="text" class="input__field_modal" maxlength="50" placeholder="{{__('reg.name')}}" name="first_name" value="{{$requisites['first_name']??''}}">
    <div class="input__error" style="display: none"></div>
</label>

<label class="input__wrap_modal mt-1 reg-form__surname">
    <span class="input__label_modal">{{__('reg.surname')}}:</span>
    <input type="text" class="input__field_modal" maxlength="50" placeholder="{{__('reg.surname')}}" name="last_name" value="{{$requisites['last_name']??''}}">
    <div class="input__error" style="display: none"></div>
</label>
<label class="input__wrap_modal mt-1 reg-form__country">
    <span class="input__label_modal">address1:</span>
    <input type="text" class="input__field_modal" placeholder="" name="address1" value="{{$requisites['address1']??''}}">
    <div class="input__error" style="display: none"></div>
</label>

<label class="input__wrap_modal mt-1 reg-form__country">
    <span class="input__label_modal">Country code:</span>
    <input type="text" class="input__field_modal" placeholder="UK" name="country_code" maxlength="3" value="{{$requisites['country_code']??''}}">
    <div class="input__error" style="display: none"></div>
</label>
<label class="input__wrap_modal mt-1 reg-form__postcode">
    <span class="input__label_modal">{{__('reg.post_code')}}:</span>
    <input type="text" class="input__field_modal" maxlength="10" placeholder="{{__('reg.post_code')}}" name="zip_code" style="width: 120px;" value="{{$requisites['zip_code']??''}}">
    <div class="input__error" style="display: none"></div>
</label>

<label class="input__wrap_modal mt-1 reg-form__city">
    <span class="input__label_modal">{{__('reg.city')}}:</span>
    <input type="text" class="input__field_modal" maxlength="30" placeholder="{{__('reg.city')}}:" name="city" value="{{$requisites['city']??''}}">
    <div class="input__error" style="display: none"></div>
</label>

<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (just numbers without +):</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="phone" value="{{$requisites['phone']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="text" class="input__field_modal" placeholder="email" name="email" value="{{$requisites['email']??\App\MUser::getAuth()?->email??''}}" required>
</label>
