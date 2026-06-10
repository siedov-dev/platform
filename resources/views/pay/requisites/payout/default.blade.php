@php
    $hints = [
        'Settle.RUB' => __('wallet.cardNumber'),
        'Visa RUB' => __('wallet.cardNumber'),
        'piastrix_rub' => __('wallet.walletNumberPiastrix'),
        'FK.fkrub' => __('wallet.walletNumberFK'),
        'FK.qiwi' => __('wallet.walletNumberQiwi'),
        'FK.155' => __('wallet.walletNumberQiwi'),
        'FK.ym' => __('wallet.walletNumberYandex'),
        'FK.visarub' => __('wallet.cardNumber'),
        'FK.tele2' => __('wallet.cardNumber'),
        'FK.bl' => __('wallet.cardNumber'),
        'FK.mts' => __('wallet.cardNumber'),
        'FK.megafon' => __('wallet.cardNumber'),
        'FK.advcash' => __('wallet.cardNumber'),
        'Brpay.cardrub' => __('wallet.cardNumber'),
        'Brpay.carduah' => __('wallet.cardNumber'),
        'Swift.cardvisa' => __('wallet.cardNumber'),
        'IKassa.cardvisa' => __('wallet.cardNumber'),
        'CryptoCloud.USDT' => __('wallet.walletNumberPiastrix'),
        'CryptoCloud.BTC' => __('wallet.walletNumberPiastrix'),
        'Alphapo.BTC' => __('wallet.walletNumberPiastrix'),
        'Alphapo.USDT' => __('wallet.walletNumberPiastrix'),
    ];

    $hint = isset($hints[$method]) ? $hints[$method] : reset($hints);
    if (preg_match('#^PayKassa\..*$#', $method)) {
        $hint = __('wallet.walletNumberPiastrix');
    }

    $pay_system = \App\Models\PaymentSystems::getPayoutSystemForMethod($method);
    $min_payout = $pay_system->getMinSum();
    $max_payout = $pay_system->getMaxSum();
@endphp
<div style="display: none" id="max-payout-value">{{$max_payout}}</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ $hint }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{$hint}}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
