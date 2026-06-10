<?php
$phoneCodes = [
    'ru' => '+7',
    'ua' => '+380',
    'by' => '+375',
    'in' => '+91',
    'tj' => '+992',
    'kz' => '+7',
    'pl' => '+48',
    'cz' => '+420',
    'ro' => '+40',
    'hu' => '+36',
    'sk' => '+421',
    'de' => '+49',
    'md' => '+373',
    'uz' => '+998',
    'lv' => '+371',
    'ee' => '+372',
    'kg' => '+996',
    'am' => '+374',
    'tm' => '+993',
    'lt' => '+370',
    'ge' => '+995',
    'az' => '+994',
];
$country = 'in';
$info = \App\Services\IpInfo\IpInfo::getInfo(request()->ip());
if (!empty($info)) {
    $country = mb_strtolower($info->country_code);
    if (!empty($country) && isset($phoneCodes[$country])) {
        $phone = $phoneCodes[$country];
        unset($phoneCodes[$country]);
        $phoneCodes = array_merge([$country => $phone], $phoneCodes);
    } else {
        $country = 'in';
    }
}

$captcha_enabled = \App\Models\SiteData::getFromJson('captcha_recovery', false);

$email = request('email');
$phone = request('phone');
$login_error = request('login_error');
$show_phone = !empty($phone);

foreach ($phoneCodes as $c => $code) {
    if (!empty($phone) && str_starts_with('+' . $phone, $code)) {
        $country = $c;
        $phone = substr($phone, strlen($code) - 1);
    }
}

?>

<form action="{{route('login')}}" id="login-form" method="POST" class="login-form" data-ajax="yes"
      @if(isset($hidden)) style="display: none"@endif>
    @csrf
    <input type="hidden" name="lsUserId" value="">
    <div class="login-form__title">{{ __('login.authorization') }}</div>

    <div class="tabs login-form__tabs">
        <label class="tabs__tab @if(!$show_phone) tabs__tab_active @endif" data-target="#login-form-email">
            <input type="radio" name="login_type" value="email" style="display: none" checked>
            {{ __('login.email') }}
        </label>
        <label class="tabs__tab @if($show_phone) tabs__tab_active @endif" data-target="#login-form-phone">
            <input type="radio" name="login_type" value="phone" style="display: none">
            {{ __('login.phone') }}
        </label>
    </div>

    <label id="login-form-email" class="input__wrap_modal login-form__email"
           @if($show_phone) style="display:none;" @endif>
        <span class="input__label_modal">{{ __('login.yourEmail') }}:</span>
        <input type="text" class="input__field_modal" maxlength="50" placeholder="{{ __('login.email') }}..."
               name="email" value="{{$email}}" autocomplete="username email">
        @if(!empty($login_error))
            <div class="input__error">{{$login_error}}</div>
        @else
            <div class="input__error" style="display: none"></div>
        @endif
    </label>

    <label id="login-form-phone" class="input__wrap_modal login-form__phone"
           @if(!$show_phone) style="display:none;" @endif>
        <span class="input__label_modal">{{ __('login.yourPhone') }}:</span>
        <div class="input__field-wrap" style="position: relative;">

            <div class="phone-select">
                <input type="hidden" name="phone_prefix" value="{{$phoneCodes[$country]}}">
                <div class="phone-select__value">
                    <img src="/img/flags-wave/{{$country}}.png" width="20" class="phone-select__option-image">
                    <span class="value-code">{{$phoneCodes[$country]}}</span>
                    <div class="arrow-down"></div>
                </div>
                <div class="phone-select__dropdown">
                    @foreach ($phoneCodes as $countryCode => $phoneCode)
                        <div class="phone-select__option" data-value="{{ $phoneCode }}" data-code="{{ $phoneCode }}">
                            <img src="/img/flags-wave/{{ $countryCode }}.png" width="30"
                                 class="phone-select__option-image">
                            <div class="phone-select__option-text">{{ __('countries.' . $countryCode) }}
                                ({{ $phoneCode }})
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <input type="tel" class="input__field_modal" maxlength="10"
                   oninput="if(!/^\d*$/.test(this.value)){if(this._lastValue===undefined){this.value='';}else{this.value=this._lastValue;}};this._lastValue = this.value;"
                   placeholder="XX - XX - XXXX" name="phone" value="{{$phone}}" autocomplete="username phone">
        </div>
        @if(!empty($login_error))
            <div class="input__error">{{$login_error}}</div>
        @else
            <div class="input__error" style="display: none"></div>
        @endif
    </label>

    <div id="login-error" class="input__error" style="display: none"></div>

    <label class="input__wrap_modal login-form__pass mt-2">
        <span class="input__label_modal">{{ __('login.yourPassword') }}:</span>
        <input type="password" class="input__field_modal" placeholder="{{ __('login.password') }}..." name="password"
               autocomplete="password">
        <img src="/img/ui/icons/eye.svg" class="input__eye-icon" style="filter: invert(1);top: 26px;" alt="">
        <span class="input__error" style="display: none"></span>
    </label>


    <div class="login-form__links mt-2 mb-2">
        <span class="login-form__no-acc-msg">{{ __('login.forgotPassword') }}</span>&nbsp
        <a href="{{ route('forgotPass', [], false) }}" class="login-form__forgot-pass"
           rel="nofollow">{{ __('cabinet.restore') }}</a>
    </div>


    <button type="submit" class="login-form__submit-button mb-2">{{ __('login.login') }}</button>


    @if(config('auth.socialLogin'))
        <div class="login-form__or-with-wrap">
            <div class="login-form__or-with">{{ __('login.orUsing') }}</div>
        </div>

        <script src="/js/ulogin.js"></script>
        @if($country !== 'in' && app()->getLocale() !== 'hi' && app()->getLocale() !== 'en')
            <div id="uLogin"
                 data-ulogin="display=panel;theme=flat;fields=first_name,last_name;providers=google,facebook,vkontakte,odnoklassniki,mailru,facebook;hidden=other;redirect_uri={{ urlencode(route('landing')) }};mobilebuttons=0;"></div>
        @else
            <div id="uLogin"
                 data-ulogin="display=panel;theme=flat;fields=first_name,last_name;providers=google,facebook;hidden=other;redirect_uri={{ urlencode(route('landing')) }};mobilebuttons=0;"></div>
        @endif
    @endif

    <hr>

    <div class="login-form__links mt-2">
        <span class="login-form__no-acc-msg">{{ __('login.noAccount') }}</span>&nbsp
        <a href="{{ route('register', [], false) }}" class="login-form__create-acc"
           rel="nofollow">{{ __('login.signUp') }}</a>
    </div>
</form>


<form action="{{ route('forgotPass', [], false) }}" method="POST" id="forgot-pass-form"
      class="forgot-pass-form login-form" data-ajax="yes" style="display: none">
    @csrf
    <div class="login-form__title">{{ __('login.passRecovery') }}</div>

    <div class="tabs login-form__tabs">
        <label class="tabs__tab tabs__tab_active" data-target="#forgot-pass-email-tab">
            <input type="radio" name="login_type" value="email" style="display: none" checked>
            {{ __('login.email') }}
        </label>
        <label class="tabs__tab" data-target="#forgot-pass-phone-tab">
            <input type="radio" name="login_type" value="phone" style="display: none">
            {{ __('login.phone') }}
        </label>
    </div>

    <label id="forgot-pass-email-tab" class="input__wrap_modal">
        <span class="input__label_modal">{{ __('login.yourEmail') }}:</span>
        <input type="text" class="input__field_modal" placeholder="{{ __('login.email') }}..." name="email">
    </label>

    <label id="forgot-pass-phone-tab" class="input__wrap_modal login-form__phone" style="display:none;">
        <span class="input__label_modal">{{ __('login.yourPhone') }}:</span>
        <div class="input__field-wrap" style="position: relative;">

            <div class="phone-select">
                <input type="hidden" name="phone_prefix" value="{{$phoneCodes[$country]}}">
                <div class="phone-select__value">
                    <img src="/img/flags-wave/{{$country}}.png" width="20" class="phone-select__option-image">
                    <span class="value-code">{{$phoneCodes[$country]}}</span>
                    <div class="arrow-down"></div>
                </div>
                <div class="phone-select__dropdown">
                    @foreach ($phoneCodes as $countryCode => $phoneCode)
                        <div class="phone-select__option" data-value="{{ $phoneCode }}" data-code="{{ $phoneCode }}">
                            <img src="/img/flags-wave/{{ $countryCode }}.png" width="30"
                                 class="phone-select__option-image">
                            <div class="phone-select__option-text">{{ __('countries.' . $countryCode) }}
                                ({{ $phoneCode }})
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <input type="tel" class="input__field_modal" maxlength="10"
                   oninput="if(!/^\d*$/.test(this.value)){if(this._lastValue===undefined){this.value='';}else{this.value=this._lastValue;}};this._lastValue = this.value;"
                   placeholder="XX - XX - XXXX" name="phone">
        </div>
        <div class="input__error" style="display: none"></div>
    </label>

    @if($captcha_enabled)
        <div class="reg-form__captcha">
            <div id="g-recaptcha" class="mt-1 flex">
                <img src="{{ route('captcha', [], false) }}?t={{time()}}" alt="captcha"
                     onclick='this.src = "{{ route('captcha', [], false) }}?t=" + new Date().getTime();'>
                <input type="tel" class="input__field_modal ml-2" maxlength="4" name="captcha" style="width: 3rem;"
                       oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
            </div>
            <div class="input__error" style="display: none"></div>
        </div>
    @endif

    <button type="submit" class="login-form__submit-button mt-2">{{ __('login.recoverPassword') }}</button>
</form>


<script>
    (() => {
        let form = document.getElementById('login-form');
        let emailError = form.querySelector('.login-form__email .input__error');
        let phoneError = form.querySelector('.login-form__phone .input__error');
        let passError = form.querySelector('.login-form__pass .input__error');
        let captchaVerified = false;

        {{--let recoverCaptcha = null;--}}
        {{--awaitRecaptchaLoad().then( () => {--}}
        {{--    recoverCaptcha = grecaptcha.render('pr-recaptcha', {--}}
        {{--        'theme' : 'dark',--}}
        {{--        'sitekey' : '{{ config('captcha.siteKey') }}',--}}
        {{--        callback: () => {--}}
        {{--            captchaVerified = true;--}}
        {{--            setTimeout(() => captchaVerified = false, 120000);--}}
        {{--        },--}}
        {{--    });--}}
        {{--});--}}

        let lsUserIdField = form.querySelector('input[name=lsUserId]')
        if (lsUserIdField !== null) lsUserIdField.value = localStorage.getItem('userId');

        let forgotPassForm = document.getElementById('forgot-pass-form');

        forgotPassForm.addEventListener('submit', () => {
            Spinner.show(forgotPassForm);
        });

        forgotPassForm.addEventListener('success', async e => {
            await Spinner.hide(forgotPassForm);
            Modal.hide(forgotPassForm);
            if (forgotPassForm.querySelector('.tabs__tab.tabs__tab_active').dataset.target === "#forgot-pass-email-tab") {
                Modal.alert(e.detail);
            } else {
                Modal.show(e.detail);
            }
            let img = forgotPassForm.querySelector('#g-recaptcha img');
            img.src = "{{ route('captcha', [], false) }}?t=" + new Date().getTime();
        });

        forgotPassForm.addEventListener('error', e => {
            Spinner.hide(forgotPassForm, e.detail);
            // grecaptcha.reset(recoverCaptcha);
        });


        document.querySelector('.login-form__forgot-pass').addEventListener('click', e => {
            e.preventDefault();
            let img = forgotPassForm.querySelector('#g-recaptcha img');
            img.src = "{{ route('captcha', [], false) }}?t=" + new Date().getTime();
            Modal.show(document.getElementById('forgot-pass-form'));
        });

        document.addEventListener('requestLogin', e => {
            e.preventDefault();
            Modal.show(document.getElementById('login-form'));
        });


        form.addEventListener('submit', e => {
            // if (!captchaVerified) e.preventDefault();
            Spinner.show('#login-form');
            // grecaptcha.reset('g-recaptcha');
            // captchaVerified = false;

            emailError.style.display = 'none';
            phoneError.style.display = 'none';
            passError.style.display = 'none';
            form.querySelector('.login-form__email').classList.remove('has-error');
            form.querySelector('.login-form__phone').classList.remove('has-error');
            form.querySelector('.login-form__pass').classList.remove('has-error');
        });

        form.addEventListener('error', e => {
            e.preventDefault();
            Spinner.hide(form);

            let errors;
            if (typeof e.detail === 'string' && e.detail[0] === '{') {
                errors = JSON.parse(e.detail);
            } else {
                let errors = e.detail.errors;
            }


            if (errors.hasOwnProperty('email')) {
                form.querySelector('.login-form__email').classList.add('has-error');
                emailError.innerHTML = Array.isArray(errors.email) ? errors.email[0] : errors.email;
                emailError.style.display = 'block';
            }

            if (errors.hasOwnProperty('phone')) {
                form.querySelector('.login-form__phone').classList.add('has-error');
                phoneError.innerHTML = Array.isArray(errors.phone) ? errors.phone[0] : errors.phone;
                phoneError.style.display = 'block'
            }

            if (errors.hasOwnProperty('password')) {
                form.querySelector('.login-form__pass').classList.add('has-error');
                passError.textContent = Array.isArray(errors.password) ? errors.password[0] : errors.password;
                passError.style.display = 'block';
            }
        });

        form.addEventListener('success', async e => {
            e.preventDefault();

            // remove all modals restraining location change
            let modal = document.querySelector('.modal');
            while (modal !== null) {
                modal.remove();
                modal = document.querySelector('.modal');
            }
            window.location.href = '{{route('welcome')}}';
            return;

            let d = document.createElement('div');
            d.innerHTML = e.detail;

            // For phones replace bottom and right panels.
            if (document.body.offsetWidth <= 600) {

                let authButtons = document.querySelector('.auth-buttons');
                if (authButtons !== null) {
                    let bottomMenu = d.querySelector('.bottom-menu-wrap');
                    if (bottomMenu !== null) {
                        d.removeChild(bottomMenu);
                        replaceNode(authButtons, bottomMenu);
                    }
                }

                let rightSidebar = document.querySelector('.right-sidebar-wrap');
                if (rightSidebar !== null) {
                    let newSidebar = d.querySelector('.right-sidebar-wrap');
                    if (newSidebar !== null) {
                        d.removeChild(newSidebar);
                        replaceNode(rightSidebar, newSidebar);
                    }
                }

                // For desktop replace header.
            } else {
                // Process header
                let h = d.getElementsByClassName('header');
                if (h.length > 0) {
                    h = h[0];
                    h.parentNode.removeChild(h);
                    setInnerHtml(document.getElementById('header'), h.innerHTML)
                }
            }

            // Dispatch event that indicates authorization success.
            let event = new CustomEvent('auth', {cancelable: true});
            document.dispatchEvent(event);
            if (event.defaultPrevented) {
                Modal.hide(form);
                return;
            }

            // Enable favourite games button in the menu.
            // let favBtn = document.getElementById('fav-games-btn');
            // favBtn.style.opacity = '1';
            // favBtn.style.pointerEvents = 'all';

            Modal.hide(form);
        });
    })();
</script>
