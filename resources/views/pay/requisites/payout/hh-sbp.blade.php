@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Phone:</span>
    <input type="tel" class="input__field_modal" placeholder="77712345678" name="receiver[phone]" value="{{$requisites['phone']??''}}" autocomplete="tel" required oninput="this.value = this.value.replaceAll(/\D/g, '').replace(/^([^7])/, '')">
</label>

<label id="login-form-email" class="input__wrap_modal mt-2">
    Bank:
    <select class="custom-control cabinet__select w-100" name="receiver[bank_code]" required>
        @foreach($banks as $bank)
            <option value="{{$bank['sbp_code']}}">{{$bank['name']}}</option>
        @endforeach
    </select>
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Card Holder:</span>
    <input type="text" class="input__field_modal" autocomplete="cc-name" placeholder="customer name..." name="receiver[card_holder]" value="{{$requisites['receiver']['card_holder']??''}}">
</label>
