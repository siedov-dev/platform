@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>

<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" class="input__field_modal" autocomplete="cc-number" placeholder="{{__('wallet.cardNumber')}}" name="payout_data[pan_to]" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">Card Holder:</span>
    <input type="text" autocomplete="cc-name" class="input__field_modal" placeholder="customer name..." name="payout_data[holder]" value="{{$requisites['payout_data']['holder']??''}}">
</label>
<div class="flex mb-1">
    <label id="login-form-email" class="input__wrap_modal mt-2"  style="width: 50%">
        <span class="input__label_modal">{{__('wallet.month')}}:</span>
        <input type="text" class="input__field_modal" placeholder="month" name="payout_data[month]" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-month" value="{{$requisites['payout_data']['month']??''}}" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
    <label id="login-form-email" class="input__wrap_modal mt-2" style="width: 50%">
        <span class="input__label_modal">{{__('wallet.year')}}:</span>
        <input type="text" class="input__field_modal" placeholder="year" name="payout_data[year]" pattern="\d{2,2}" inputmode="numeric" autocomplete="cc-exp-year" value="{{$requisites['payout_data']['year']??''}}"  required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
    </label>
</div>
