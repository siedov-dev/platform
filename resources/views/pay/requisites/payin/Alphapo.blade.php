<div style="display: flex; flex-flow: column; justify-content: center; align-items: center; padding: 0.5rem;">
    <img src="{{\App\Helpers\QrCodeGen::genImage($wallet, 'L', 10, 3)}}">
    <div class="cabinet__prtn-ref mt-1" style="width: initial;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">Для совершения платежа отправьте BTC на указанный адрес кошелька:</div>
        <div class="cabinet__prtn-ref-link"><span id="crypto-wallet-url" style="font-size: 0.7rem">{{$wallet}}</span>
            <div class="cabinet__prtn-ref-copy" onclick="copyCode('#crypto-wallet-url'); Toast.show('Адрес кошелька скопирован')">
                <img src="/img/ui/icons/cabinet/copy.svg">
            </div>
        </div>
    </div>
</div>
