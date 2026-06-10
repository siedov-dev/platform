@php
    $phone_placeholder = match (\App\MUser::getAuth()->getCurrency()) {
        \App\UserWallet::CURRENCY_RUB => '+71230000000',
        \App\UserWallet::CURRENCY_UAH => '+380970000000',
        \App\UserWallet::CURRENCY_EUR => '+491230000000',
        \App\UserWallet::CURRENCY_USDT => '+132100000000',
        \App\UserWallet::CURRENCY_KZT => '+77123000000',
        \App\UserWallet::CURRENCY_INR => '+91123000000',
        default => '+71231234567',
    }
@endphp
<div id="cabinet-position" data-position="settings"></div>
<div class="cabinet__sett">

    <div class="cabinet__sett-top">

        <!-- Password -->
        <form action="{{ route('cabinet.changePassword', [], false) }}" method="POST" class="cabinet__pad" id="cabinet-pass-form">
            @csrf

            <div class="cabinet__pad-head">
                <div class="cabinet__pad-title">{{ __('cabinet.password') }}</div>
            </div>

            <div class="cabinet__sett-pass-fields">
                <label class="input__wrap cabinet__sett-pass-wrap">
                    <input type="password" class="input__field input__field_icon-both" placeholder="{{ __('cabinet.newPassword') }}" name="new_password">
                    <img src="/img/ui/icons/lock-2.svg" class="input__icon">
                    <img src="/img/ui/icons/eye.svg" class="input__eye-icon">
                </label>

                <label class="input__wrap cabinet__sett-pass-wrap">
                    <input type="password" class="input__field input__field_icon-both cabinet__sett-pass-field" placeholder="{{ __('cabinet.confirmPassword') }}" name="new_password_confirmation">
                    <img src="/img/ui/icons/lock-2.svg" class="input__icon">
                    <img src="/img/ui/icons/eye.svg" class="input__eye-icon">
                </label>

                <label class="input__wrap cabinet__sett-pass-wrap">
                    <input type="password" class="input__field input__field_icon-both cabinet__sett-pass-field" placeholder="{{ __('cabinet.oldPassword') }}" name="old_password">
                    <img src="/img/ui/icons/lock-2.svg" class="input__icon">
                    <img src="/img/ui/icons/eye.svg" class="input__eye-icon">
                </label>
            </div>

            <button type="submit" class="cabinet__sett-submit-btn">{{ __('cabinet.save') }}</button>
        </form>


        <!-- Email and phone -->
        <div class="cabinet__pad">
            <div class="flex jc-fe">
                <a href="{{route('slc.page-info', ['page' => 'email-settings'], false)}}" class="page-info-icon mr-1" data-ajax="modal"></a>
            </div>
            <div class="cabinet__pad-head">
                <div class="cabinet__pad-title">Email:</div>
            </div>

            <div class="cabinet__verify-fields">
                <label class="input__wrap cabinet__sett-pass-wrap">
                    <input type="email" class="input__field input__field_icon-both cabinet__sett-pass-field" autocomplete="off" @if(empty(auth()->user()->email_verification_token)) disabled @endif placeholder="info@krakencassino.com" name="email" value="{{ auth()->user()->email }}">
                    <img src="/img/ui/icons/email-2.svg" class="input__icon">
                </label>

                @if(!empty(auth()->user()->email_verification_token))
                    <div class="cabinet__pad-status_fail ml-auto">{{ __('cabinet.emailNotVerified') }}</div>
                @else
                        <div class="cabinet__pad-status_success ml-auto">{{ __('cabinet.contactsVerified') }}</div>
                @endif
                <div class="cabinet__link cabinet__verify-link" id="verify-email-button" @if(empty(auth()->user()->email_verification_token) || empty(auth()->user()->email)) style="display: none"@endif>{{ __('cabinet.verifyEmail') }}</div>


                <label class="input__wrap cabinet__sett-pass-wrap mt-2">
                    <input type="text" class="input__field input__field_icon-both cabinet__sett-pass-field" autocomplete="off" @if(auth()->user()->isPhoneVerified(true)) disabled @endif
                    placeholder="{{$phone_placeholder}}" name="phone" value="{{ auth()->user()->phone }}">
                    <img src="/img/ui/icons/email-2.svg" class="input__icon">
                </label>

                @if(!auth()->user()->isPhoneVerified(true))
                    <div class="cabinet__pad-status_fail ml-auto">{{ __('cabinet.phoneNotVerified') }}</div>
                @else
                    <div class="cabinet__pad-status_success ml-auto">{{ __('cabinet.contactsVerified') }}</div>
                @endif

                <div class="cabinet__link cabinet__verify-link" id="verify-phone-button" @if(auth()->user()->isPhoneVerified(true)) style="display: none"@endif>{{ __('cabinet.verifyPhone') }}</div>
            </div>

            <form method="post" class="mt-2" action="{{route('subscribe.check')}}" data-ajax="silent" onclick="this.lastElementChild.click()">
                @csrf
                <label class="blue-white-checkbox">
                    <input name="subscribe" type="checkbox" @if(\App\MUser::getAuth()?->isSubscribed()) checked @endif>
                    <span>&nbsp;{{__('cabinet.mail_subscribe')}}</span>
                </label>
                <button type="submit" class="hidden"></button>
            </form>
        </div>
    </div>

    <form action="{{ route('cabinet.personalData') }}" method="POST" class="cabinet__sett-personal cabinet__pad" id="personal-data-form">
        <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />
        @csrf

        <div class="cabinet__pad-head">
            <div class="cabinet__pad-title">{{ __('cabinet.personalData') }}:</div>
            @if (auth()->user()->personalData()->isConfirmed())
                <div class="cabinet__pad-status_success fail ml-auto">{{ __('cabinet.identified') }}</div>
            @else
                <div class="cabinet__pad-status_fail ml-auto">{{ auth()->user()->personalData()->getStatusString() }}</div>
            @endif
        </div>

        @php
            $data = \App\MUser::getAuth()->personalData();
            if ($data !== null && $data->getStatus() === \App\Models\PersonalData::STATUS_DECLINED){
                $message = $data->getMessage();
            } else {
                $message = empty(\App\MUser::getAuth()->verification_msg)? '' : \App\MUser::getAuth()->verification_msg;
            }
        @endphp
        @if (!empty($message))
            <div class="cabinet__pad-head">
                <div class="cabinet__pad-status_fail">{{ $message }}</div>
            </div>
        @endif

        <div class="cabinet__sett-personal-cols">

            <div class="cabinet__sett-personal-user">
                <label class="input__wrap">
                    <input type="text" class="input__field input__field_icon-both" placeholder="{{ __('cabinet.name') }}..." name="name" value="{{ auth()->user()->personalData()->name?? auth()->user()->name }}">
                    <img src="/img/ui/icons/user.svg" class="input__icon">
                </label>

                <label class="input__wrap">
                    <input type="text" class="input__field input__field_icon-both" placeholder="{{ __('cabinet.surname') }}..." name="surname" value="{{ auth()->user()->personalData()->surname?? auth()->user()->surname }}">
                    <img src="/img/ui/icons/users.svg" class="input__icon">
                </label>

                @if(\Illuminate\Support\Facades\Auth::user()->currency != \App\UserWallet::CURRENCY_INR)
                    <label class="input__wrap">
                        <input type="date" class="input__field input__field_icon-both" placeholder="{{ __('cabinet.birthday') }}..." name="birthday" value="{{ auth()->user()->personalData()->birthday }}">
                        <img src="/img/ui/icons/calendar.svg" class="input__icon">
                    </label>
                @endif
                <div class="input__error" id="cabinet-birthday-error" style="display: none">{{ __('cabinet.youMustBe18YearsOld') }}</div>

                <button type="submit" class="cabinet__sett-submit-btn">{{ __('cabinet.save') }}</button>
            </div>

            <div class="cabinet__sett-personal-passport">
                @csrf
                @if(\Illuminate\Support\Facades\Auth::user()->currency != \App\UserWallet::CURRENCY_INR)
                    <label class="input__wrap">
                        <input type="text" class="input__field input__field_icon-both" placeholder="{{ __('cabinet.passport') }}..." name="passport" value="{{ auth()->user()->personalData()->passport }}">
                        <img src="/img/ui/icons/document.svg" class="input__icon">
                    </label>
                @endif

                <div class="cabinet__sett-address">
                    <label class="input__wrap">
                        <input class="input__field" name="country" placeholder="{{ __('cabinet.country') }}" value="{{ auth()->user()->personalData()->country }}">
                    </label>
                    <label class="input__wrap">
                        <input class="input__field" name="city" placeholder="{{ __('cabinet.city') }}" value="{{ auth()->user()->personalData()->city?? auth()->user()->city }}">
                    </label>
                </div>


                <label class="input__wrap">
{{--                    <input type="file" class="input__file" name="verification_img" id="file-input" accept=".jpg,.jpeg,.png,.bmp,.webp">--}}
                    <input type="file" class="input__file" name="verification_img" id="file-input" accept=".jpg,.jpeg,.png,.bmp,.webp">
                    <div class="input__file-area" id="drop-area">
                        <img src="/img/ui/icons/upload.svg" class="input__file-icon" id="output">
                        <div class="input__file-hint">{{ __('cabinet.dropFileHereOr') }} <span class="input__file-hint-link">{{ __('cabinet.browse') }}</span> Max size 8MB</div>
                    </div>

                </label>
            </div>
        </div>

    </form>

    <div class="cabinet__pad mt-2">
        <div class="cabinet__pad-head">
            <div class="cabinet__pad-title flex ai-center">
                <img class="mr-05" src="/img/ui/icons/red-hand.svg" alt="red hand">
                {{__('cabinet.self-lock')}}:
            </div>
        </div>
        <div class="flex">
            <p class="">{{__('cabinet.self-lock-text')}}</p>
            <form class="ml-2 flex" action="{{route('cabinet.lock', [], false)}}" method="GET" data-ajax="modal" data-popup-type="bottom-popup">
                <select name="days" class="custom-control cabinet__select">
                    <option value="3">{{__('cabinet.lock-3d')}}</option>
                    <option value="7">{{__('cabinet.lock-7d')}}</option>
                    <option value="14">{{__('cabinet.lock-14d')}}</option>
                    <option value="30">{{__('cabinet.lock-30d')}}</option>
                    <option value="90">{{__('cabinet.lock-90d')}}</option>
                    <option value="180">{{__('cabinet.lock-180d')}}</option>
                    <option value="365">{{__('cabinet.lock-365d')}}</option>
                </select>
                <button class="button button_danger ml-1">{{__('cabinet.confirm')}}</button>
            </form>
        </div>
    </div>

</div>



<!-- Modal with email verification code input -->
<form action="{{ route('cabinet.verifyEmail', [], false) }}" method="GET" id="verify-email-form" style="display: none">
    <div class="cabinet__vrf-hint">{{ __('cabinet.emailVerificationHint') }}</div>
    <label class="cabinet__vrf-code-field">
        <span class="cabinet__vrf-label">{{ __('cabinet.verificationCode') }}</span>
        <input type="text" name="token" class="cabinet__vrf-code-input" autocomplete="off">
    </label>
    <button type="submit" class="cabinet__vrf-button w-10">{{ __('cabinet.verify') }}</button>
</form>

<!-- Modal with email verification code input -->
<form action="{{ route('cabinet.verifyPhone', [], false) }}" method="GET" id="verify-phone-form" style="display: none">
    <div class="cabinet__vrf-hint">{{ __('cabinet.phoneVerificationHint') }}</div>
    <label class="cabinet__vrf-code-field">
        <span class="cabinet__vrf-label">{{ __('cabinet.verificationCode') }}</span>
        <input type="text" name="token" class="cabinet__vrf-code-input" autocomplete="off">
    </label>
    <button type="submit" class="cabinet__vrf-button w-10">{{ __('cabinet.verify') }}</button>
    <button type="button" class="cabinet__vrf-button w-10" id="resend-button" disabled>{{ __('cabinet.resend') }}</button>
    <div class="cabinet__vrf-hint mt-1" style="display: none;">{{ __('cabinet.resend_time_estimated') }} <span id="resend-seconds"></span> s</div>
</form>


<script>

    (() => {
        let passForm = document.getElementById('cabinet-pass-form');


        async function reloadCabinet() {
            try {
                let res = await network.get('{{ route('cabinet.settings', [], false) }}');
                let html = await res.text();
                setInnerHtml('.cabinet__content', html);
            } catch (errorMsg) {
                console.error(errorMsg);
            }
        }


        passForm.addEventListener('submit', async e => {
            e.preventDefault();

            Spinner.show(passForm);

            try {
                let res = await network.post(passForm.getAttribute('action'), new FormData(passForm));
                let msg = await res.text();
                Spinner.hide(passForm, msg);
                passForm.querySelector('input[name="new_password"]').value = '';
                passForm.querySelector('input[name="new_password_confirmation"]').value = '';
                passForm.querySelector('input[name="old_password"]').value = '';
            } catch (errorMsg) {
                Spinner.hide(passForm, errorMsg);
            }
        });



        // Verification
        let verifyEmailBtn = document.getElementById('verify-email-button');
        let verifyPhoneBtn = document.getElementById('verify-phone-button');
        let resendPhoneBtn = document.getElementById('resend-button');
        let verifyEmailForm = document.getElementById('verify-email-form');
        let verifyPhoneForm = document.getElementById('verify-phone-form');
        let personalDataForm = document.getElementById('personal-data-form');
        let resendDisabled = 10;
        let resendTimer;
        let timerEl = document.getElementById('resend-seconds');

        let emailInput = document.querySelector('.cabinet__verify-fields input[name="email"]');
        let phoneInput = document.querySelector('.cabinet__verify-fields input[name="phone"]');
        let initialEmail = emailInput.value;
        let initialPhone = phoneInput.value;

        emailInput.addEventListener('input', () => {
            let email = emailInput.value.trim();
            verifyEmailBtn.style.display = email === '' || email === initialEmail ? 'none' : 'block';
        });

        phoneInput.addEventListener('input', () => {
            let phone = phoneInput.value.replace(/\s/g, '').trim();
            let regex = /^\+\d{10,}$/gi;
            verifyPhoneBtn.style.display = !regex.test(phone) || phone === initialPhone ? 'none' : 'block';
        });

        verifyEmailBtn.addEventListener('click', async e => {
            let email = emailInput.value.trim();
            if (email === '') return;

            Spinner.show(verifyEmailBtn.parentNode.parentNode);

            let data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('email', email);

            try {
                await network.post('{{ route('cabinet.sendEmailVerification', [], false) }}', data);
                verifyEmailForm.style.display = 'flex';
                Modal.show(verifyEmailForm);
            } catch (errorMsg) {
                Modal.alert(errorMsg);
            }
            Spinner.hide(verifyEmailBtn.parentNode.parentNode);
        });

        verifyPhoneBtn.addEventListener('click', async e => {
            let phone = phoneInput.value.replace(/\s/g, '').trim();
            if (phone === "") {
                shortMessage.show("{{__('login.inputPhone')}}")
                phoneInput.focus();
                return;
            }
            let regex = /^\+\d{10,}$/gi;
            if (!regex.test(phone)) return;

            Spinner.show(verifyEmailBtn.parentNode.parentNode);

            let data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('phone', phone);
            resendDisabled = 60;
            timerEl.parentElement.style.display = '';

            try {
                verifyPhoneForm.style.display = 'flex';
                Modal.show(verifyPhoneForm);
                resendTimer = setInterval(() => {
                    resendDisabled--;
                    timerEl.innerText = resendDisabled.toString();
                    if (resendDisabled <= 0) {
                        clearInterval(resendTimer);
                        resendPhoneBtn.removeAttribute('disabled');
                        timerEl.parentElement.style.display = 'none';
                        timerEl.innerText = '';
                    }
                }, 1000);

            } catch (errorMsg) {
                Modal.alert(errorMsg);
            }
            try {
                await network.post('{{ route('cabinet.sendPhoneVerification', [], false) }}', data);
            } catch (errorMsg) {
                Modal.alert(errorMsg);
            }
            Spinner.hide(verifyEmailBtn.parentNode.parentNode);
        });

        resendPhoneBtn.addEventListener('click', async e => {
            let phone = phoneInput.value.replace(/\s/g, '').trim();
            let regex = /^\+\d{10,}$/gi;
            if (!regex.test(phone)) return;

            Spinner.show(resendPhoneBtn.parentNode.parentNode);

            resendDisabled = 60;
            timerEl.parentElement.style.display = '';
            resendPhoneBtn.disabled = true;

            try {
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

            } catch (errorMsg) {
                Modal.alert(errorMsg);
            }

            let data = new FormData();
            data.append('_token', '{{ csrf_token() }}');
            data.append('phone', phone);

            try {
                await network.post('{{ route('cabinet.sendPhoneVerification', [], false) }}', data);
            } catch (errorMsg) {
                Modal.alert(errorMsg);
            }
            Spinner.hide(resendPhoneBtn.parentNode.parentNode);
        });


        async function verifyFormHandler(e) {
            e.preventDefault();

            let url = this.getAttribute('action') + '?token=' + this.querySelector('input[name="token"]').value;

            Spinner.show(this);

            try {
                await network.get(url);
                await Spinner.hide(this);
                Modal.hide(this);
                await reloadCabinet();
            } catch (errorMsg) {
                Spinner.hide(this, errorMsg);
            }
        }

        verifyPhoneForm.addEventListener('submit', verifyFormHandler);
        verifyEmailForm.addEventListener('submit', verifyFormHandler);


        personalDataForm.addEventListener('submit', async e => {
            e.preventDefault();

            let birthdayError = document.getElementById('cabinet-birthday-error');
            let birthdayInput = personalDataForm.querySelector('input[name="birthday"]');
            if (birthdayInput !== null) {
                let year = parseInt(birthdayInput.value.slice(0, 4));
                let d = new Date();
                console.log(d.getFullYear(), year);
                if (d.getFullYear() - year < 18) {
                    if (birthdayError !== null) {
                        birthdayError.style.display = 'flex';
                    }
                    return;
                }
            }

            birthdayError.style.display = 'none';
            let url = personalDataForm.getAttribute('action');
            let data = new FormData(personalDataForm);
            if (files !== false) {
                data.set('verification_img', files[0]);
            }

            Spinner.show(personalDataForm);

            try {
                let res = await network.post(url, data);
                let msg = await res.text();
                await Spinner.hide(personalDataForm, msg);
                await reloadCabinet();
            } catch (errorMsg) {
                Spinner.hide(personalDataForm, errorMsg);
            }
        });

        let files = false;

        document.getElementById("drop-area").addEventListener('click', function (e) {
            e.preventDefault();
            let file_input = document.getElementById('file-input');
            file_input.focus();
            file_input.click();
        });

        document.getElementById('file-input').addEventListener('change', function (e) {
            let output = document.getElementById('output');
            output.src = URL.createObjectURL(e.target.files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            };
            files = false;
        });

        document.getElementById("drop-area").addEventListener('dragenter', function (e) {
            e.preventDefault();
            this.classList.add("highlighted");
        });

        document.getElementById("drop-area").addEventListener('dragover', function (e) {
            e.preventDefault();
            this.classList.add("highlighted");
        });

        document.getElementById("drop-area").addEventListener('dragleave', function (e) {
            e.preventDefault();
            this.classList.remove("highlighted");
        });

        document.getElementById("drop-area").addEventListener('drop', function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.classList.remove("highlighted");
            let dt = e.dataTransfer;
            files = dt.files;

            let output = document.getElementById('output');
            output.src = URL.createObjectURL(files[0]);
            output.onload = function() {
                URL.revokeObjectURL(output.src) // free memory
            };
        });

    })();

</script>
