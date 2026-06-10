@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Номер телефона:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="phone" value="{{$requisites['phone']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Банк:</span>
    <select name="option_id" class="custom-control cabinet__select w-100">
        @foreach($banks as $name => $id)
            <option value="{{$id}}">{{$name}}</option>
        @endforeach
    </select>
</label>
