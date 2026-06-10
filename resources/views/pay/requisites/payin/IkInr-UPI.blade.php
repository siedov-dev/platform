<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">VPA:</span>
    <input type="text" class="input__field_modal" maxlength="130" oninput="if(!/^[\w\d._-]*@?[\w\d._-]*$/.test(this.value)){if(this._lastValue===undefined){this.value='';}else{this.value=this._lastValue;}};this._lastValue = this.value;" placeholder="UPI wallet number (VPA)" name="vpa" required>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">wallet:</span>
    <select class="option__field_modal" name="mop">
        <option value="504">UPI</option>
        <option value="504">Google Pay</option>
        <option value="504">JioMoney</option>
    </select>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
