@php
/** @var \App\MUser $m_user */
@endphp
<label id="login-form-email" class="input__wrap_modal mt-1">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="card_holder" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>

@include('pay.requisites.payin.default', ['method' => $method])
