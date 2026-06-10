<div class="wallet" id="sreda-otp-form">
    <div class="wallet__title">{{__('wallet.enter_verification_code')}}</div>
    <form action="{{route('sredapay.otp')}}" method="GET" class="mt-1" data-ajax="#sreda-otp-form">
        <input type="hidden" name="otpid" value="{{$id}}">
        <input type="hidden" name="otpmethod" value="{{$otp_method}}">
        <input type="hidden" name="action" value="">
        @if(($success??false) !== true)
            <label class="input__wrap_modal">
                <span class="input__label_modal">otp code:</span>
                <input type="text" class="input__field_modal" placeholder="otp code" name="otp" value="{{$otp??''}}">
            </label>
        @endif
        @if(!empty($error))
            <div class="text-red">{{$error??''}}</div>
        @endif
        @if(!empty($msg))
            <div class="text-green">{{$msg}}</div>
        @endif
        @if(empty($success))
            <div class="flex jc-sb mt-2">
                <button type="submit" class="wallet__button" value="confirm" onclick="this.parentElement.parentElement.querySelector('input[name=\'action\']').value = this.value; this.parentElement.style.display='none'">{{ __('wallet.continue') }}</button>
                <button type="submit" class="wallet__button" value="resend" onclick="this.parentElement.parentElement.querySelector('input[name=\'action\']').value = this.value; this.parentElement.style.display='none'">{{ __('wallet.resend') }}</button>
            </div>
        @endif
    </form>
</div>
