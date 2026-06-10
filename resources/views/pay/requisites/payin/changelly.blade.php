<div class="input__label_modal">currency:</div>
<div id="user-wallet__assets">
    @foreach($currencies as $currency)
        <label class="input__wrap_modal wallet-asset">
            <div class="flex">
                <div>
                    @include('common.blocks.currency-icon', ['curr' => $currency['ticker']])
                </div>
                <div class="grow flex column ml-1">
                    <div class="text-title">{{$currency['ticker']}}</div>
                    <div class="text-subtitle text-smaller">{{$currency['name']}}</div>
                </div>
            </div>
            <div class="custom-radio mr-1">
                <input name="currencyIn" type="radio" value="{{$currency['ticker']}}">
            </div>
        </label>
    @endforeach
</div>
