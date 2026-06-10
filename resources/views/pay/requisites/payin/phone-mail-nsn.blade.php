<label class="input__wrap_modal">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="first name" name="first_name" value="{{$first_name??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="last name" name="last_name" value="{{$last_name??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="email" autocomplete="email" class="input__field_modal" placeholder="email" name="email" value="{{$email??\App\MUser::getAuth()?->email??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="tel" class="input__field_modal" autocomplete="phone" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
@include('pay.requisites.payin.default', ['method' => $method])
