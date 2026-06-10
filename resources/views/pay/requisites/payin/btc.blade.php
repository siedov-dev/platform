<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">{{ __('wallet.chooseDepositSum') }} (BTC):</span>
    <input type="number" step="any" class="input__field_modal" id="wallet__dep-amount" placeholder="0.001 BTC" name="amount">
</label>

<div class="wallet__fast-sums">
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 0.001">1 mBTC</div>
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 0.010">10 mBTC</div>
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 0.050">50 mBTC</div>
</div>

<div class="wallet__hint">{{ __('wallet.fee') }}: 0%</div>
