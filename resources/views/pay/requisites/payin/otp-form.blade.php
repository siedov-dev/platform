<div class="wallet">
    <div class="wallet__title">Телефоныңызға жіберілген растау кодын енгізіңіз</div>
    <form action="{{action('PayController@confirmOtpCode')}}" method="post" class="mt-1">
        @csrf
        <input type="hidden" name="otpurl" value="{{$url}}">
        <label class="input__wrap_modal">
            <span class="input__label_modal">otp code:</span>
            <input type="text" class="input__field_modal" placeholder="otp code" name="otp" value="{{$otp??''}}" required>
        </label>
        <div class="flex jc-end mt-2">
            <button type="submit" class="wallet__button">{{ __('wallet.continue') }}</button>
        </div>
    </form>
</div>
