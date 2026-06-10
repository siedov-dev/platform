<div id="wallet-content">

    <div class="user-wallet__pad" id="user-wallet-pad">
        <div class="user-wallet__close-btn ml-1" onclick="document.dispatchEvent(new CustomEvent('hideWallet'))">
            <div>&times;</div>
        </div>
        <div class="user-wallet__header">
        </div>

        <div class="user-wallet__buttons mt-2 mb-1">
            <div onclick="document.dispatchEvent(new CustomEvent('showDeposit'))" class="deposit-btn">
                <img src="/img/ui/icons/deposit-icon.png">
                <div>{{__('wallet.deposit')}}</div>
            </div>
            <div onclick="document.dispatchEvent(new CustomEvent('showPayout'))" class="withraw-btn">
                <img src="/img/ui/icons/payout-icon.png">
                <div>{{__('wallet.withdraw')}}</div>
            </div>
    {{--        <div onclick="document.dispatchEvent(new CustomEvent('showBuyCrypto'))">--}}
    {{--        <div class="disabled-btn">--}}
    {{--            <img src="/img/ui/icons/buy.png">--}}
    {{--            <div>{{__('wallet.buy')}}</div>--}}
    {{--        </div>--}}
            <a href="{{route('slc.cashback', [], false)}}" class="flex disabled-btn" data-ajax="modal" data-content-class="page-info" data-modal-class="bottom-popup">
                <img src="/img/ui/icons/cashback-icon.png">
                <div>{{__('wallet.buy')}}</div>
            </a>
        </div>

    </div>

</div>
