<label id="login-form-email" class="input__wrap_modal">
    <span class="input__label_modal">{{ __('wallet.chooseDepositSum') }}:</span>
    <input type="number" min="10" class="input__field_modal" id="wallet__dep-amount" placeholder="1000" name="amount">
</label>

<div class="wallet__fast-sums">
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 10">10 USD</div>
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 50">50 USD</div>
    <div class="wallet__fast-sum" onclick="document.getElementById('wallet__dep-amount').value = 100">100 USD</div>
</div>

<div class="wallet__hint">{{ __('wallet.fee') }}: 0%</div>
