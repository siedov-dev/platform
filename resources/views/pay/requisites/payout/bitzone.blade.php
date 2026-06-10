@php
    /** @var \App\Models\PaymentSystems $pay_method */
    $min_payout = $pay_method->getMinSum();
    $max_payout = $pay_method->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="requisite[requsites]" required oninput="this.value = this.value.replaceAll(/[\s-]/ig, '')">
</label>
<label id="login-form-email" class="input__wrap_modal mt-2">
    <span class="input__label_modal">{{__('wallet.card_holder')}}:</span>
    <input type="text" class="input__field_modal" placeholder="{{__('wallet.card_holder')}}" pattern="[a-zA-Z.,-]+\s[a-zA-Z.,-]+" inputmode="text" name="requisite[ownerName]" value="{{$requisites['card_holder']??''}}" autocomplete="cc-name" required>
</label>
<input id="fingerprint" type="hidden" name="fingerprint" value="">
<input type="hidden" name="userAgent" value="{{request()->userAgent()}}">

<script>
    (async () => {
        const fingerprintVal = document.getElementById('fingerprint');
        const f = await generateFingerprint();
        console.log(f.hash);
        fingerprintVal.value = f.hash
    })()
</script>
