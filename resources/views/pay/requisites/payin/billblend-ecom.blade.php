<label class="input__wrap_modal">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="First name" name="first_name" required="" value="{{ request()->cookie('bill_first_name') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="Last name" name="last_name" required="" value="{{ request()->cookie('bill_last_name') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="Email" name="email" value="{{ Auth::user()->email }}" required="">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Phone:</span>
    <input type="text" class="input__field_modal" placeholder="Phone" name="phone" required="" value="{{ request()->cookie('bill_phone') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Address1:</span>
    <input type="text" class="input__field_modal" placeholder="Address1" name="address1" required="" value="{{ request()->cookie('bill_address1') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">City:</span>
    <input type="text" class="input__field_modal" placeholder="City" name="city" required="" value="{{ request()->cookie('bill_city') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Zip code:</span>
    <input type="text" class="input__field_modal" placeholder="Zip code" name="zip_code" required="" value="{{ request()->cookie('bill_zip_code') }}">
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Country:</span>
    <select name="country" class="input__field_modal" required="">
        @foreach ($countries as $key => $country)
            <option value="{{$key}}" @if(request()->cookie('bill_country') == $key) @selected(true) @endif>{{ $country }}</option>
        @endforeach
    </select>
</label>

<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="tel" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="card_holder" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>
<div class="flex mb-1">
    <label id="login-form-email" class="input__wrap_modal mt-2"  style="width: 25%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="month" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="year" pattern="\d{4,4}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <div style="width: 25%"></div>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 25%">
        <span class="input__label_modal">CVV2:</span>
        <input type="tel" class="input__field_modal" placeholder="cvv" pattern="\d{3,4}" inputmode="numeric" name="cvc" autocomplete="cc-csc" value="{{$requisites['cvc']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
</div>
