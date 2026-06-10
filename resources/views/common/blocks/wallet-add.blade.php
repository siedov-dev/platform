@php
    /** @var \App\MUser $user */
    $user = auth()->user();
    $currencies = array_diff(\App\UserWallet::activeCurrencies(), $user->getActiveCurrencies());
@endphp

<div class="pad pt-1">
    <div class="wallet__title text-title">{{ __('wallet.choose_currency') }}</div>

    <form action="{{ action('WalletController@add', [], false) }}" method="POST" id="wallet__deposit">
        @csrf
{{--        <div class="wallet__sub-title">{{ __('wallet.choose_currency') }}:</div>--}}

        <div class="flex jc-sa mt-1 mb-1 wrap w-100">
            @foreach($currencies as $currency_id)
                @php
                    $curr_name = \App\UserWallet::currencyName($currency_id);
                @endphp
                <label class="currency-plate m-05">
                    <input type="radio" name="currency" value="{{$currency_id}}">
                    <div class="currency-plate-icon p-05">
                        <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower($curr_name)}}"></div>
                        <div class="wallet__paysystem_name text-subtitle">{{$curr_name}}</div>
                    </div>
                </label>
            @endforeach
        </div>

        <button type="submit" class="wallet__button">{{ __('wallet.add') }}</button>
    </form>
</div>
