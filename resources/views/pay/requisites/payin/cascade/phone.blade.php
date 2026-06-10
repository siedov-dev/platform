<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="text" class="input__field_modal" placeholder="phone number" name="phone" value="{{$phone??\App\MUser::getAuth()?->phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
