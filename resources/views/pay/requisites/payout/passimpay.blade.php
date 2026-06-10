<div class="input__label_modal mb-1">{{__('wallet.currency')}}:</div>
<div id="user-wallet__assets" style="height: 14rem">
    @foreach($currencies['list']??[] as $currency)
        <label class="input__wrap_modal wallet-asset">
            <div class="flex">
                <div>
                    @include('common.blocks.currency-icon', ['curr' => $currency['currency']])
                </div>
                <div class="grow flex column ml-1">
                    <div class="text-title">{{$currency['currency']}} {{$currency['platform']}}</div>
                    <div class="text-subtitle text-smaller">{{$currency['name']}}</div>
                </div>
            </div>
            <div class="custom-radio mr-1">
                <input name="currencyOut" type="radio" value="{{$currency['id']}}">
            </div>
        </label>
    @endforeach
</div>
<label class="input__wrap_modal mt-2">
    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.walletNumberPiastrix') }}:</span>
    <input type="text" class="input__field_modal" placeholder="{{ __('wallet.walletNumberPiastrix') }}" name="cardNumber" required oninput="this.value = this.value.replaceAll(/[\s]/ig, '')">
</label>
