<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="text" class="input__field_modal" placeholder="email" name="email" value="{{$email??\App\MUser::getAuth()?->email??''}}" required>
</label>
<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="text" class="input__field_modal" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
