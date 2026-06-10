<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="email" name="email" value="{{$email??\App\MUser::getAuth()?->email??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="tel" class="input__field_modal" autocomplete="phone" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="card_holder" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
