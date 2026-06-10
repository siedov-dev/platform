@php
    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-1">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" value="{{$requisites['cardNumber']??''}}" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<input type="hidden" class="input__field_modal" placeholder="bank name" name="bank_name" value="SBERBANK" required>
{{--<input type="hidden" class="input__field_modal" placeholder="bank name" name="bank_code" value="100000000111" required>--}}
