<div class="header__wallet-selector wallet-selector">
    <div class="flex ai-center relative">
        <div class="wallet-selector__icon currency-icon currency-icon_{{ strtolower(auth()->user()->getCurrencyName()) }}"></div>
        <div class="wallet-selector__amount">
            @php
                $amount = auth()->check() ? auth()->user()->getWallet()->getBalance() : 0;
                $user_currencies = auth()->user()->getActiveCurrencies();
            @endphp
            <span class="wallet-selector__amount-int">{{ number_format(explode('.', $amount)[0], 0, '.', ',') }}.</span><span class="wallet-selector__amount-rest">{{ explode('.', number_format($amount, 2))[1]??'00' }}</span>
        </div>

        @if(count($user_currencies) > 1)
            <div class="wallet-selector__arrow arrow-down smaller"></div>
        @endif
    </div>
    <div class="wallet-selector__plus-button">{{ __('wallet.deposit') }}</div>

    @if(count($user_currencies) > 1)
        <div class="wallet-selector__dropdown">
            @foreach($user_currencies as $currency_code)
                @php
                    $currency_name = \App\UserWallet::currencyName($currency_code);
                @endphp
                <div class="wallet-selector__item wallet-selector__item_{{strtolower($currency_name)}} @if(auth()->user()->getCurrencyName() === $currency_name) wallet-selector__item_active @endif" data-id="{{ $currency_code }}">
                    <div class="currency-icon currency-icon_{{strtolower($currency_name)}}"></div>
                    <div class="wallet-selector__amount">
                        @php
                            $amount = auth()->check() ? auth()->user()->getWallet($currency_code)->getBalance() : 0;
                        @endphp
                        <span class="wallet-selector__amount-int">{{ number_format(explode('.', $amount)[0], 0, '.', ',') }}.</span><span class="wallet-selector__amount-rest">{{ explode('.', number_format($amount, 2))[1]??'00' }}</span>
                    </div>
                    <div class="wallet-selector__currency">{{$currency_name}}</div>
                    <div class="checked-icon">✓</div>
                </div>
            @endforeach
        </div>
    @endif
</div>
