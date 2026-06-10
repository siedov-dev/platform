@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">Phone (just numbers without +):</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="phone" value="{{$requisites['phone']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/\D/ig, '')">
</label>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">First and last name:</span>
    <input type="text" class="input__field_modal" placeholder="John Dou" name="payer_fio" value="{{$requisites['payer_fio']??''}}">
</label>
<label class="input__wrap_modal reg-form__birthday mt-1 input__wrap">
    <span class="input__label_modal">{{__('reg.birthday')}}:</span>
    <input type="date" class="input__field_modal input__field_icon" placeholder="{{ __('cabinet.birthday') }}..." name="birthday" value="{{$requisites['birthday']?? Auth()->user()?->personalData()?->birthday }}">
    <div class="input__error" style="display: none"></div>
</label>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">Country code (2 sybmols):</span>
    <input type="text" class="input__field_modal" maxlength="2" placeholder="UK" name="receiver_country" value="{{$requisites['receiver_country']??''}}">
</label>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal">Receiver City:</span>
    <input type="text" class="input__field_modal" placeholder="London" name="receiver_city" value="{{$requisites['receiver_city']??''}}">
</label>
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
