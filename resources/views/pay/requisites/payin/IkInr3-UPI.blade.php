<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Email:</span>
    <input type="text" class="input__field_modal" placeholder="customer email..." name="email" value="{{$email??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone (10 numbers after 91):</span>
    <input type="text" class="input__field_modal" placeholder="customer phone..." name="phone" value="{{$mobile??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">First name:</span>
    <input type="text" class="input__field_modal" placeholder="customer name..." name="first_name" value="{{$name??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Last name:</span>
    <input type="text" class="input__field_modal" placeholder="customer surname..." name="last_name" value="{{$surname??''}}" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">UPI wallet ID:</span>
    <input type="text" class="input__field_modal" placeholder="customer UPI wallet ID..." name="upi" value="{{$upi??''}}" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
