<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">bank:</span>
    <select class="option__field_modal" name="mop_type">
        <option value="MWL">Mobikwik</option>
        <option value="PPL">PayTm</option>
        <option value="APWL">Amazon Pay</option>
        <option value="PPWL">PhonePe</option>
        <option value="OLAWL">Ola Money</option>
        <option value="AWL">Airtel Wallet</option>
        <option value="FCWL">Freecharge</option>
        <option value="ICWL">ITZ Cash</option>
        <option value="JMWL">Jio Money</option>
        <option value="MPWL">M Pesa Wallet</option>
        <option value="OXWL">Oxygen Wallet</option>
        <option value="SBWL">SBI Buddy</option>
        <option value="ZPWL">Zip Cash</option>
    </select>
</label>
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
<input type="hidden" name="ip" value="{{request()->ip()}}">
@include('pay.requisites.payin.default', ['method' => $method])
