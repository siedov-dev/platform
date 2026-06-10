<form action="{{ route('checkPasswordToken', [], false) }}" method="POST" id="recovery-token-form" data-ajax="yes" style="background-color: white;">
    @csrf
    <div class="cabinet__vrf-hint">{{ __('cabinet.passTokenHint', ['phone' => $phone?? '']) }}</div>
    <div class="cabinet__vrf-hint">{{ __('cabinet.passTokenHint2') }}</div>
    <label class="cabinet__vrf-code-field">
        <span class="cabinet__vrf-label">{{ __('cabinet.passwordToken') }}</span>
        <input type="text" name="token" class="cabinet__vrf-code-input" autocomplete="off" required>
    </label>
    <button type="submit" class="cabinet__vrf-button w-10">{{ __('cabinet.ok') }}</button>
    <button type="button" class="cabinet__vrf-button w-10 resend-token-button" disabled>{{ __('cabinet.resend') }}</button>
    <div class="cabinet__vrf-hint mt-1" style="display: none;">{{ __('cabinet.resend_time_estimated') }} <span class="token-resend-seconds"></span> s</div>
</form>
<script>
    (() => {
        let form = document.getElementById('recovery-token-form');
        let resendDisabled = 240;
        let timerEl = form.querySelector('.token-resend-seconds')
        let resendPhoneBtn = form.querySelector('.resend-token-button')
        let resendTimer;
        let forgotPassForm = document.getElementById('forgot-pass-form');

        timerEl.parentElement.style.display = '';
        resendPhoneBtn.disabled = true;

        resendTimer = setInterval(() => {
            resendDisabled--;
            timerEl.innerText = resendDisabled.toString();
            if (resendDisabled <= 0) {
                clearInterval(resendTimer);
                resendPhoneBtn.disabled = false;
                timerEl.parentElement.style.display = 'none';
                timerEl.innerText = '';
            }
        }, 1000);

        form.addEventListener('submit', () => {
            Spinner.show(form);
        });
        form.addEventListener('success', (e) => {
            form.setAttribute('data-ajax', '');
            form.submit();
        });
        form.addEventListener('error', e => {
            Spinner.hide(form, e.detail);
        });
        resendPhoneBtn.addEventListener('click', () => {
            Modal.hide(form);
            Modal.show(forgotPassForm);
        });
    })()
</script>
