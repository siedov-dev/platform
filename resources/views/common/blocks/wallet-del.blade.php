@php
    /** @var \App\MUser $user */
    $user = auth()->user();
@endphp

<div class="wallet">
    <div class="pad">
        <div class="wallet__title text-title">{{ __('wallet.del_currency') }}</div>

        <form action="{{ action('WalletController@del', ['currency' => $currency_id], false) }}" method="POST">
            @csrf
            <div class="wallet__sub-title text-subtitle">{{ __('wallet.sure_del_currency') }}:</div>
            <div class="flex mt-1 jc-center">
                    @php
                        $curr_name = \App\UserWallet::currencyName($currency_id);
                    @endphp
                <label class="currency-plate mb-05 p-1">
                    <input type="radio" name="currency" value="{{$currency_id}}">
                    <div class="currency-plate-icon">
                        <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower($curr_name)}}"></div>
                        <div class="wallet__paysystem_name text-subtitle">{{$curr_name}}</div>
                    </div>
                </label>
            </div>

            <div class="flex jc-sa">
                <button type="submit" class="button button_green">{{ __('cabinet.yes') }}</button>
                <div class="button button_red" onclick="Modal.hide(this.closest('.modal'))">
                    {{__('cabinet.no')}}
                </div>
            </div>
        </form>
    </div>
</div>
