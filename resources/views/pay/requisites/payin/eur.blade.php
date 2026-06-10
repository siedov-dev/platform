@php
/** @var \App\MUser $m_user */
@endphp
<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="email" name="email" value="{{$email??\App\MUser::getAuth()?->email??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="tel" class="input__field_modal" autocomplete="phone" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-1">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="card_holder" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>

<label class="input__wrap_modal reg-form__birthday mt-1 input__wrap">
    <span class="input__label_modal">{{__('reg.birthday')}}:</span>
    <input type="date" class="input__field_modal input__field_icon" placeholder="{{ __('cabinet.birthday') }}..." name="birthday" value="{{ $m_user->personalData()?->birthday }}">
    <div class="input__error" style="display: none"></div>
</label>

<label id="login-form-email" class="input__wrap_modal mt-1">
    <span class="input__label_modal">{{__('wallet.address1')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.address1')}}" inputmode="text" name="address1" value="{{$requisites['address1']??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-1">
    <span class="input__label_modal">{{__('wallet.address1')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.address2')}}" inputmode="text" name="address2" value="{{$requisites['address2']??''}}">
</label>

<div class="flex" style="overflow: hidden">
    <label class="input__wrap_modal mt-1 reg-form__country">
        <span class="input__label_modal">{{__('reg.country')}}:</span>
        <input type="text" class="input__field_modal" placeholder="{{__('reg.country')}}" name="country" value="{{ $m_user->personalData()?->country }}">
        <div class="input__error" style="display: none"></div>
    </label>
    <label class="input__wrap_modal mt-1 reg-form__postcode">
        <span class="input__label_modal">{{__('reg.post_code')}}:</span>
        <input type="tel" class="input__field_modal" maxlength="10" placeholder="{{__('reg.post_code')}}" name="postcode" style="width: 120px;" value="{{$requisites['postcode']??''}}">
        <div class="input__error" style="display: none"></div>
    </label>
</div>
<div class="flex" style="overflow: hidden">
    <label class="input__wrap_modal mt-1 reg-form__city">
        <span class="input__label_modal">{{__('reg.city')}}:</span>
        <input type="text" class="input__field_modal" maxlength="30" placeholder="{{__('reg.city')}}:" name="city" value="{{ $m_user->personalData()?->city }}">
        <div class="input__error" style="display: none"></div>
    </label>

    <label class="input__wrap_modal mt-1 reg-form__state">
        <span class="input__label_modal">{{__('reg.state')}}:</span>
        <input type="text" class="input__field_modal" maxlength="60" name="state" style="width: 120px;" value="{{ $m_user->personalData()?->region}}">
        <div class="input__error" style="display: none"></div>
    </label>
</div>
@include('pay.requisites.payin.default', ['method' => $method])
