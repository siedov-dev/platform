<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.cardNumber') }}:</span>
    <input type="tel" class="input__field_modal" placeholder="{{__('wallet.cardNumber')}}" name="cardNumber" inputmode="numeric" pattern="\d{12,19}" autocomplete="cc-number" required oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
</label>

@include('pay.requisites.payin.default', ['method' => $method])
