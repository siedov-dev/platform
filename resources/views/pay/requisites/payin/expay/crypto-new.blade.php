<div class="">

    <label class="input__wrap w-100">
        <div class="input__title flex jc-sb">
            <span class="text-subtitle">{{ __('wallet.your_get') }}:</span>
            <span class="text-limit">Min {{ $ps->min_sum }}</span>
        </div>
        <div class="input__ddd">
            <input type="number" class="input__field" name="fiat_amount" value="{{ $ps->min_sum }}" oninput="document.getElementById('crypto-wallet-amount').value = +(this.value / {{ $course }}).toFixed(4)" id="crypto-wallet-amount-get">
            <div class="wallet-crypto-group-data">
                <div class="wallet-selector__icon currency-icon currency-icon_{{ strtolower(auth()->user()->getCurrencyName()) }}"></div>
                <span>{{ auth()->user()->getCurrencyName() }}</span>
            </div>
        </div>
    </label>

    <label class="input__wrap w-100 mt-1">
        <div class="input__title">
            <span class="text-subtitle">{{ __('wallet.your_give') }}:</span>
        </div>
        <div class="input__ddd">
            <input type="number" class="input__field" name="fiat_amount" value="{{ $pre_price }}" id="crypto-wallet-amount" oninput="document.getElementById('crypto-wallet-amount-get').value = +(this.value * {{ $course }}).toFixed(4)">
            <div class="wallet-crypto-group-data">
                <button type="button" class="cursor-pointer" onclick="navigator.clipboard.writeText(document.getElementById('crypto-wallet-amount').value); Toast.show('{{__('wallet.address_copied')}}')">
                    <img src="/img/copy.svg" alt="">
                </button>
                <div class="flex ai-center">
                    <img src="{{ $ps->image_url }}" class="wallet__payment-logo" alt="">
                </div>
                <span>{{ $ps->name }}</span>
            </div>
        </div>
    </label>

    <div class="crypt_wallet__address mt-1">
        <div class="input__title">
            <span class="text-subtitle">{{ __('wallet.crypto_address') }}:</span>
        </div>
        <div class="address-value">
            <div class="input__value">
                <span class="w-100" id="crypto-wallet-url">{{ $wallet }}</span>
                <button type="button" class="wallet__button w-100 mt-1 cursor-pointer" onclick="copyCode('#crypto-wallet-url'); Toast.show('{{__('wallet.address_copied')}}')">
                    <img src="/img/copy.svg" alt="">
                    <span>Copy</span>
                </button>
            </div>
            <div class="qr-img">
                <img src="{{\App\Helpers\QrCodeGen::genImage($wallet, 'L', 10, 1)}}" alt="">
            </div>
        </div>
    </div>

    @if($sub_name)
    <div class="warning-info mt-1">
        {{ __('wallet.crypto_pay_warning', ['sub_name' => $sub_name]) }}

    </div>
    @endif

</div>

<script>

    (() => {

        //

    })();

</script>


