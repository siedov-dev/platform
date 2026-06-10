<div class="wallet_payout" id="wallet_payout_s" data-group="4" data-course="{{ $course }}">

    <label class="input__wrap w-100">
        <div class="input__title">
            <span class="text-subtitle">{{ __('wallet.crypto_address') }}:</span>
        </div>
        <div class="input__ddd">
            @if ($wallets_exists)
                <select name="cardNumber" class="custom-control cabinet__select w-100" required id="crypto-wallet-select-value">
{{--                    @foreach (auth()->user()->crypto_wallets(system_code: $ps->system_name, currency: $token, payment_type: 1, fiat_currency: null)->get() as $wallet)--}}
                    @foreach ($wallets as $wallet)
                        <option value="{{ $wallet->address }}">{{ $wallet->address }}</option>
                    @endforeach
                        <option value="">{{ __('wallet.add_new_wallet') }}</option>
                </select>
            @endif
            <input style="display: {{ $wallets_exists ? 'none' : 'block' }};" type="text" class="input__field" name="{{ $wallets_exists ? "cardNumberReserv" : "cardNumber" }}" {{ $wallets_exists ? '' : 'required' }} id="crypto-wallet-input-value">
        </div>
    </label>

    <label class="input__wrap w-100 mt-1">
        <div class="input__title">
            <span class="text-subtitle">{{ __('wallet.your_get') }}:</span>
        </div>
        <div class="input__ddd">
            <input disabled type="number" class="input__field" id="crypto-wallet-amount">
            <div class="wallet-crypto-group-data">
                <div class="flex ai-center">
                    <img src="{{ $ps->image_url }}" class="wallet__payment-logo" alt="">
                </div>
                <span>{{ $ps->name }}</span>
            </div>
        </div>
    </label>

    @if($sub_name)
        <div class="warning-info mt-1">
            {{ __('wallet.crypto_pay_warning', ['sub_name' => $sub_name]) }}
        </div>
    @endif

    <label style="display: {{ $wallets_exists ? 'none' : 'block' }};" class="input__wrap w-100 mt-1 blue-white-checkbox" id="crypto-wallet-select-auto-save">
        <input name="autosave_wallet" type="checkbox">
        <span>{{ __('wallet.autosave_address') }}</span>
    </label>

</div>

<style>
    .cursor-pointer{
        cursor: pointer;
    }

    .wallet_payout[data-group="4"] .wallet__paysystem{
        height: 3.5rem;
        border: 2px solid transparent;
    }

    .wallet_payout[data-group="4"] .wallet__payment-system{
        height: auto;
    }

    .wallet_payout[data-group="4"] .wallet__paysystem_name{
        margin: 0 5px;
        flex-flow: column;;
    }

    .wallet_payout[data-group="4"] .wallet__paysystem_name small{
        font-weight: 400;
        color: #8c90ca;
    }

    .wallet_payout[data-group="4"] .wallet__paysystem_limits{
        margin-top: 5px;
    }

    .wallet_payout[data-group="4"] .wallet__payment-logo{
        max-height: 1.5rem;
    }

    .wallet_payout[data-group="4"] .input__title{

    }

    .wallet_payout[data-group="4"] .input__field{
        background-color: #1c1f3e;
        font-size: 1.25rem;
        border: 2px solid transparent;
    }

    .wallet_payout[data-group="4"] .wallet__paysystem:has(> div.wallet__payment-system_active),
    .wallet_payout[data-group="4"] .input__field:focus{
        border-color: #7582F2;
    }

    .wallet_payout[data-group="4"] .input__ddd{
        position: relative;
        margin-top: 5px;
    }

    .wallet_payout[data-group="4"] .input__ddd .wallet__payment-logo{
        width: auto;
        height: 1.6rem;
        max-height: 1.6rem;
        max-width: 100%;
    }

    .wallet_payout[data-group="4"] .text-limit{
        color: #424673;
        font-size: .7rem;
        font-weight: bold;
    }

    .wallet_payout[data-group="4"] .input__wrap .wallet-crypto-group-data{
        position: absolute;
        right: 0;
        bottom: 0;
        top: 0;
        display: flex;
        align-items: center;
        padding: 0 1rem;
    }

    .wallet_payout[data-group="4"] .input__wrap .wallet-crypto-group-data span{
        margin-left: .5rem;
    }

    /*.wallet_payout[data-group="4"] .wallet__payment-system{
        border: 2px solid #1c1f3e;
    }*/

    .wallet_payout[data-group="4"] .crypt_wallet__address{

    }

    .wallet_payout[data-group="4"] .input__value{
        background-color: #1c1f3e;
        padding: 1rem;
        border-radius: 10px;
        overflow-wrap: break-word;
        margin-right: .5rem;
        max-width: 70%;
    }

    .wallet_payout[data-group="4"] .input__value span{
        font-size: .8rem;
    }

    .wallet_payout[data-group="4"] .address-value{
        display: flex;
        margin-top: 5px;
    }

    .wallet_payout[data-group="4"] .qr-img{
        width: 30%;
        height: auto;
    }

    .wallet_payout[data-group="4"] .wallet__payment-system-calc{
        padding-bottom: 2rem;
    }

    .wallet_payout[data-group="4"] .warning-info{
        color: #F79E1B;
        background: rgba(247, 158, 27, .15);
        padding: .75rem;
        font-size: .75rem;
        border-radius: .75rem;
        line-height: 1rem;
    }

    .wallet_payout[data-group="4"] .wallet__button{
        background-color: #181A32;
        color: #8C90CA;
        position: relative;
    }

    .wallet_payout[data-group="4"] .wallet__button span{
        margin-left: .25rem;
    }

    .wallet_payout[data-group="4"] .wallet-crypto-group-data button{
        margin-right: .25rem;
    }

</style>
