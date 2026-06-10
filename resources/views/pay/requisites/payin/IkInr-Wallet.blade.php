<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">wallet:</span>
    <select class="option__field_modal" name="mop">
        <option value="101">PAYTM</option>
        <option value="102">MOBIKWIK</option>
        <option value="103">AIRTEL MONEY</option>
        <option value="106">RELIANCE JIO</option>
        <option value="107">OLA MONEY</option>
        <option value="113">FREECHARGE</option>
        <option value="115">PHONEPE</option>
    </select>
</label>
@include('pay.requisites.payin.default', ['method' => $method])
