<div class="my-profile-btn" onclick="document.dispatchEvent(new CustomEvent('showRightSidebar'))">
    @include('common.blocks.user-avatar', ['percent' => 0])
    <div class="flex-column ml-05">
        <div class="title">{{__('userMenu.myProfile')}}</div>
        <div class="amount" data-currency="{{ auth()->user()->getCurrencyName() }}">
            @php
                $amount = auth()->check() ? auth()->user()->getWallet()->getBalance() : 0;
                $symbol = \App\Wallet::currencySymbol(auth()->user()->getCurrency())?? '';
            @endphp
            <span class="main-currency-int bonus-balance">{{ number_format(explode('.', $amount)[0], 0, '.', ',') }}.</span><span class="main-currency-rest bonus-balance">{{ explode('.', number_format($amount, 2))[1]??'00' }}</span>
            <span class="current-balance-symbol">{{ $symbol }}</span>
        </div>
    </div>
    <div class="wallet-selector__arrow arrow-down smaller"></div>
</div>
