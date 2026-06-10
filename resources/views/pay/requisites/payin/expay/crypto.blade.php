<div class="crypto-pay-dialog" style="">
    <div class="article__title mt-2">
        {{__('wallet.deposit_title', ['currency' => $currency])}}
        <img src="{{ $ps->image_url }}" alt="">
    </div>
    <div class="currency-info">
        <div>
            {{ $pre_price }} {{ $currency }}
        </div>
    </div>

    <div class="crypto-pay-dialog-info mt-2">
        <img class="qr-img" src="{{\App\Helpers\QrCodeGen::genImage($wallet, 'L', 10, 3)}}" alt="">
        <div class="calc_course">
            <h3>
                <span>{{ $amount }} {{ auth()->user()->getCurrencyName() }}</span>
                <span class="calc_course_dec">≈</span>
                <span>{{ $pre_price }} {{ $currency }}</span>crypto.blade.php
            </h3>
            <div class="text-info mt-1">
                {{ __('wallet.deposit_crypto_info', ['currency' => $currency, 'amount' => $pre_price]) }}:
            </div>
            <div class="cabinet__prtn-ref mt-1 w-100">

                <div class="cabinet__prtn-ref-link">
                    <span id="crypto-wallet-url">{{ $wallet }}</span>
                    <div class="cabinet__prtn-ref-copy" onclick="copyCode('#crypto-wallet-url'); Toast.show('{{__('wallet.address_copied')}}')">
                        <img src="/img/ui/icons/cabinet/copy2.png" alt="">
                    </div>
                </div>
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
        display: inline-block;
        border: .25rem solid black;
    }
    .crypto-pay-dialog .text-info{
        font-size: .825rem;
        font-weight: 400;
        margin-bottom: .5rem;
        color: #FFFFFF80;
        text-align: center;
    }

    .crypto-pay-dialog .currency-info{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .crypto-pay-dialog .currency-info div{
        margin: 0 .5rem;
        font-size: 2rem;
        font-family: monospace,monospace;
    }

    .crypto-pay-dialog .crypto-pay-dialog-info{
        display: flex;
        align-items: stretch;
    }

    .crypto-pay-dialog .calc_course{
        width: 100%;
        margin-left: 1rem;
        padding: 1rem;
        border: 3px dashed #313278;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .crypto-pay-dialog .calc_course h3{
        text-align: center;
    }

    .crypto-pay-dialog .calc_course h3 span{
        font-family: monospace, monospace;
    }

    .crypto-pay-dialog .calc_course h3 span.calc_course_dec{
        margin: 0 1rem;
    }

    .crypto-pay-dialog .article__title img{
        max-height: 2rem;
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
