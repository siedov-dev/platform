<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">VPA:</span>
    <input type="text" class="input__field_modal" maxlength="130" oninput="if(!/^[\w\d._-]*@?[\w\d._-]*$/.test(this.value)){if(this._lastValue===undefined){this.value='';}else{this.value=this._lastValue;}};this._lastValue = this.value;" placeholder="UPI wallet number (VPA)" name="upi_address" required>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
