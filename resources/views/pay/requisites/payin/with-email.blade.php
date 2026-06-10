<label class="input__wrap_modal">
    <span class="input__label_modal">email:</span>
    <input type="text" class="input__field_modal" placeholder="email" name="email" value="{{$email??''}}" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
