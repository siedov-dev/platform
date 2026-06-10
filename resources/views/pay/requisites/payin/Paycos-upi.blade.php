<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$email}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$mobile}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">address:</span>
    <input type="text" class="input__field_modal" placeholder="customer address..." name="address" value="{{$address??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">payer address:</span>
    <input type="text" class="input__field_modal" placeholder="payer@address" name="payer_address" value="{{$payer_address??''}}" required>
</label>
<input type="hidden" name="ip" value="{{request()->ip()}}">
@include('pay.requisites.payin.default', ['method' => $method])
