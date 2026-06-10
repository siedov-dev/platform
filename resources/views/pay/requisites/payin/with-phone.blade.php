<label class="input__wrap_modal">
    <span class="input__label_modal">phone:</span>
    <input type="text" class="input__field_modal" placeholder="phone number" name="phone" value="{{$phone??''}}" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
@include('pay.requisites.payin.default', ['method' => $method])
