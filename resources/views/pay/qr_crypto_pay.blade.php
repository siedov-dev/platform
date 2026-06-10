<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8"/>
    <title></title>
    <link href="/css/desktop.css?t={{ time() }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="/js/desktop.js?t={{ time() }}"></script>
</head>
<body class="light">
<div style="display: flex; flex-flow: column; justify-content: space-evenly; height: 100%; width: 100%; justify-content: center; align-items: center; padding: 1rem;">
    <div style="font-weight: 600; margin-bottom: 0.5rem;">Перевод средств:</div>
    <img src="{{\App\Helpers\QrCodeGen::genImage($wallet, 'L', 10, 4)}}">
    <div class="cabinet__prtn-ref mt-1" style="width: initial;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">Для совершения платежа отправьте BTC на указанный адрес кошелька:</div>
        <div class="cabinet__prtn-ref-link"><span id="crypto-wallet-url" style="font-size: 0.7rem">{{$wallet}}</span>
            <div class="cabinet__prtn-ref-copy" onclick="copyCode('#crypto-wallet-url'); Toast.show('Адрес кошелька скопирован')">
                <img src="/img/ui/icons/cabinet/copy.svg">
            </div>
        </div>
    </div>
</div>
</body>
</html>
