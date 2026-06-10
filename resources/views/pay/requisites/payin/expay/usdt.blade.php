<div class="crypto-pay-dialog" style="">
    <div class="article__title mt-2">{{__('wallet.deposit_title', ['currency' => $currency])}}</div>
    <div class="currency-info">
        <div><img src="/img/paysystem/expay/usdt_tron.png" alt="" style="max-width: 92px"></div>
        <div>
            {{ $amount }} {{ $currency }}
        </div>
    </div>
    <img class="qr-img mt-2" src="{{\App\Helpers\QrCodeGen::genImage($wallet, 'L', 10, 3)}}">
    <div class="cabinet__prtn-ref mt-1" style="width: 100%;">
        <div class="text-info mt-1">
            {{ __('wallet.deposit_crypto_info', ['currency' => $currency, 'amount' => $amount]) }}:
        </div>
        <div class="cabinet__prtn-ref-link">
            <span id="crypto-wallet-url">{{$wallet}}</span>
            <div class="cabinet__prtn-ref-copy" onclick="copyCode('#crypto-wallet-url'); Toast.show('{{__('wallet.address_copied')}}')">
                <img src="/img/ui/icons/cabinet/copy2.png">
            </div>
        </div>
    </div>
</div>
<style>
    .crypto-pay-dialog{
        display: flex;
        flex-flow: column;
        justify-content: center;
        align-items: center;
        padding: 1rem;
        background-color: #191C42;
        border-radius: 1rem;
    }
    .crypto-pay-dialog .qr-img{
        width: min(80%, 15rem);
    }
    .crypto-pay-dialog .text-info{
        font-size: 0.825rem;
        font-weight: 400;
        margin-bottom: 0.5rem;
        color: #FFFFFF80;
    }

    .crypto-pay-dialog .currency-info{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .crypto-pay-dialog .currency-info div{
        margin: 0 0.5rem;
        font-size: 2rem;
        font-family: monospace,monospace;
    }

    .crypto-pay-dialog #crypto-wallet-url{
        color: #FFFFFF;
        inline-size: 90%;
        overflow-wrap: anywhere;
    }
    .crypto-pay-dialog .cabinet__prtn-ref-link{
        height: auto;
        display: flex;
        align-items: center;
        padding: 1rem;
    }
    .crypto-pay-dialog .cabinet__prtn-ref-copy{
        width: 7rem;
        height: 2.5rem;
        background: none;
        position: relative;
    }

    body.light .crypto-pay-dialog{
        background-color: #FFFFFF;
    }
    body.light .crypto-pay-dialog .text-info{
        color: #31327880;
    }
    body.light .crypto-pay-dialog #crypto-wallet-url{
        color: #313278;
    }
</style>
