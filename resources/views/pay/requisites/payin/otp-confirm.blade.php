<div class="pad" id="otp-verify-form">
    <div class="modal-title mt-2">{{__('wallet.enter_verification_code')}}</div>
    <form action="{{route('otp.confirm')}}" method="POST" class="mt-1" data-ajax="#otp-verify-form">
        @csrf
        <input type="hidden" name="otpid" value="{{$otpid}}">
        <input type="hidden" name="mid" value="{{$mid}}">
        <input type="hidden" name="pid" value="{{$pid}}">
        @if(($success??false) !== true)
            <label class="input__wrap_modal">
                <span class="input__label_modal">otp code:</span>
                <input type="text" class="input__field_modal" placeholder="otp code" name="otp" value="{{$otp??''}}">
            </label>
        @endif
        @if(!empty($error))
            <div class="text-red mt-1">{{$error??''}}</div>
        @endif
        @if(!empty($msg))
            <div class="text-green mt-1">{{$msg}}</div>
        @endif
        <button type="submit" class="hidden"></button>
    </form>
    @if(empty($success))
        <div class="flex jc-sb ai-center mt-1">
            <button type="submit" class="wallet__button" value="confirm"
                    onclick="this.closest('#otp-verify-form').querySelector('button').click(); this.parentElement.style.display='none';Spinner.show(this.closest('#otp-verify-form').querySelector('form'));">
                {{ __('wallet.continue') }}
            </button>
            <form action="{{route('otp.resend')}}" method="POST" data-ajax="#otp-verify-form">
                @csrf
                <input type="hidden" name="otpid" value="{{$otpid}}">
                <input type="hidden" name="mid" value="{{$mid}}">
                <input type="hidden" name="pid" value="{{$pid}}">
                <button type="submit" id="resend-otp-btn" class="wallet__button button_red disabled" disabled value="resend"
                        onclick="this.parentElement.parentElement.style.display='none';Spinner.show(this.closest('#otp-verify-form').querySelector('form'));">
                    {{ __('wallet.resend') }}
                </button>
            </form>
        </div>
    @endif
</div>
<script>
    (() => {
        let timer = 60;
        if (window.otpInterval !== null) {
            clearInterval(window.otpInterval);
        }
        window.otpInterval = setInterval(() => {
            let form = document.querySelector('#otp-verify-form');
            if (form) {
                form.querySelector('#resend-otp-btn').innerText = "{{ __('wallet.resend') }} (" + timer + ")";
                timer--;
                if (timer <= 0) {
                    clearInterval(window.otpInterval);
                    timer = 60;
                    document.querySelector('#resend-otp-btn').removeAttribute('disabled');
                    document.querySelector('#resend-otp-btn').classList.remove('disabled');
                    document.querySelector('#resend-otp-btn').innerText = "{{ __('wallet.resend') }}";
                }
            } else {
                clearInterval(window.otpInterval);
            }
        }, 1000)
    })()
</script>
