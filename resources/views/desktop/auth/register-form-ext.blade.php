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
$detected_country = '';
$info = \App\Services\IpInfo\IpInfo::getInfo(request()->ip());
if (!empty($info)) {
    $country = mb_strtolower($info->country_code);
    $detected_country = mb_strtolower($info->country_code);
    if (!empty($country) && isset($phoneCodes[$country])) {
        $phone = $phoneCodes[$country];
        unset($phoneCodes[$country]);
        $phoneCodes = array_merge([$country => $phone], $phoneCodes);
    } else {
        $country = 'in';
    }
}

$currencyMap = \App\Helpers\Currency::getLangCurrencyMap();
if (!empty($currency)) {
    $user_currency = \App\UserWallet::currencyName($currency);
} else {
    $user_currency = \App\Services\IpInfo\IpInfo::getInfo(request()->ip())?->currency ?? \App\Helpers\Currency::EUR;
    if (!\App\Helpers\Currency::isValid($user_currency)) $user_currency = \App\Helpers\Currency::EUR;
}

if (request()->getHost() === 'krakencasino.co') $user_currency = 'USDT';
//if (request()->getHost() === 'krakencasino.club') $user_currency = 'EUR';

$captcha_enabled = \App\Models\SiteData::getFromJson('captcha_reg', false);

?>

@include('desktop.auth.login-form', ['hidden' => true])

<div class="register-form__wrap-ext sections-wrapper">

    <div class='register-form__banner-wrap'>
        <a href="{{ action('BonusesController@info', ['id' => 'welcome-short', 'in_modal' => false, 'currency' => $currency], false) }}"
           class="flex" data-ajax="modal" data-content-class="page-info">
            <img src="/img/banners/register_desktop_{{ app()->getLocale() }}.webp" class="register-form__banner">
        </a>
        {{--        <img src="/img/banners/register-triangle-1_desktop.webp" class="register-form__banner-t1">--}}
        {{--        <img src="/img/banners/register-triangle-2_desktop.webp" class="register-form__banner-t2">--}}
    </div>

    {{--    <div class="register-form__banner-wrap-ext">--}}
    {{--        <div class="login-form__title-ext">{{ __('reg.registration') }}</div>--}}

    {{--        <div class="sections-names">--}}
    {{--            <div data-section-name="login-details" class="active">--}}
    {{--                <img src="/img/ui/icons/login.png" width="30px" height="30px">--}}
    {{--                Login details--}}
    {{--            </div>--}}
    {{--            <div data-section-name="personal-info" class="">--}}
    {{--                <img src="/img/ui/icons/info.png" width="30px" height="30px">--}}
    {{--                Personal info--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="login-form__links-ext">--}}
    {{--            <a href="{{ action('BonusesController@info', ['id' => 'first-deposit', 'in_modal' => 'true'], false) }}" data-ajax="modal">--}}
    {{--                <img src="/img/banners/register_phone_{{ app()->getLocale() }}.webp" class="register-form__banner" style="height: initial">--}}
    {{--            </a>--}}
    {{--            <hr class="mt-2 mb-1">--}}
    {{--            <span class="login-form__no-acc-msg">{{ __('reg.haveAccount') }}</span>&nbsp--}}
    {{--            <a href="{{ route('login', [], false) }}" class="login-form__create-acc">{{ __('reg.login') }}</a>--}}
    {{--        </div>--}}
    {{--    </div>--}}

    <form action="{{ route('register', [], false) }}" id="register-form" method="POST"
          class="register-form sections-form" data-ajax="yes">
        @csrf
        <input type="hidden" name="login_type" value="ext">
        <div class="section-prev-btn"></div>

        <div class="section-wrapper active" data-section-name="login-details">
            <div class="register-form__title">{{ __('reg.registration') }}</div>

            <label id="reg-form-email" class="input__wrap_modal reg-form__email mt-2">
                <span class="input__label_modal">{{ __('reg.yourEmail') }}:</span>
                <input type="text" class="input__field_modal" maxlength="40" placeholder="Email..." name="email"
                       id="email">
                <div class="input__error"></div>
            </label>

            {{--            <label id="reg-form-phone" class="input__wrap_modal reg-form__phone mt-2">--}}
            {{--                <span class="input__label_modal">{{ __('reg.yourPhone') }}:</span>--}}
            {{--                <div class="input__field-wrap">--}}

            {{--                    <div class="phone-select">--}}
            {{--                        <input type="hidden" name="phone_prefix" value="{{$phoneCodes[$country]}}">--}}
            {{--                        <div class="phone-select__value">{{$phoneCodes[$country]}}</div>--}}
            {{--                        <div class="phone-select__dropdown">--}}
            {{--                            @foreach ($phoneCodes as $countryCode => $phoneCode)--}}
            {{--                                <div class="phone-select__option" data-value="{{ $phoneCode }}" data-code="{{ $phoneCode }}">--}}
            {{--                                    <img src="/img/flags/{{ $countryCode }}.svg" width="30" class="phone-select__option-image">--}}
            {{--                                    <div class="phone-select__option-text">{{ __('countries.' . $countryCode) }} ({{ $phoneCode }})</div>--}}
            {{--                                </div>--}}
            {{--                            @endforeach--}}
            {{--                        </div>--}}
            {{--                    </div>--}}

            {{--                    <input type="text" class="input__field_modal" maxlength="10" oninput="if(!/^\d*$/.test(this.value)){if(this._lastValue===undefined){this.value='';}else{this.value=this._lastValue;}};this._lastValue = this.value;" placeholder="{{ __('reg.phone') }}..." name="phone">--}}
            {{--                </div>--}}
            {{--                <div class="input__error" style="display: none"></div>--}}
            {{--            </label>--}}

            <label class="input__wrap_modal reg-form__password mt-2">
                <span class="input__label_modal">{{ __('reg.yourPass') }}:</span>
                <input type="password" class="input__field_modal" maxlength="20"
                       placeholder="{{ __('reg.inputPass') }}..." name="password">
                <img src="/img/ui/icons/eye.svg" class="input__eye-icon" style="filter: invert(1);top: 26px;">
                <div class="input__error" style="display: none"></div>
            </label>

            <div class="promo-switcher mt-1 @if(session('promocode') !== null) shown @endif">
                <div class="input__label_modal">{{ __('cabinet.doYouHave') }} <span class="green-text"
                                                                                    onclick="this.parentElement.parentElement.classList.toggle('shown')">Promocode</span>?
                </div>
                <label class="input__wrap_modal reg-form__promo">
                    <input type="text" class="input__field_modal" maxlength="20"
                           placeholder="{{ __('cabinet.inputPromoCode') }}..." name="promocode"
                           value="{{session('promocode')}}">
                    <div class="hide-promo"
                         onclick="event.stopPropagation();event.preventDefault();this.parentElement.parentElement.classList.remove('shown');">
                        &times;
                    </div>
                    <div class="input__error" style="display: none"></div>
                </label>
            </div>

            <label class="input__wrap_modal reg-form__currency mt-2">
                <span class="input__label_modal">{{ __('reg.chooseCurrency') }}:</span>
                <div class="img-drop-down drop-down input__wrap_modal reg-form__currency mt-2" id="currency-dropdown">
                    <div class="img-drop-down__current-value"
                         data-value="{{\App\UserWallet::getCurrencyCode($user_currency)}}">
                        @if($user_currency === 'USDT')
                            <div class="currency-icon currency-icon_usd"></div>USDT/BTC/ETH/LTC…
                        @else
                            <div
                                class="currency-icon currency-icon_{{mb_strtolower($user_currency)}}"></div>{{$user_currency}}
                        @endif
                    </div>
                    <input type="hidden" class="img-drop-down__selected-value" name="currency"
                           value="{{\App\UserWallet::getCurrencyCode($user_currency)}}">
                    <div class="arrow-down"></div>
                    <div class="img-drop-down__values">
                        @foreach(\App\UserWallet::activeCurrencies() as $currency_code)
                            @php
                                $currency_name = \App\UserWallet::currencyName($currency_code);
                            @endphp
                            @if($currency_code == \App\UserWallet::CURRENCY_USDT)
                                <div class="img-drop-down__value" data-value="{{$currency_code}}">
                                    <div class="currency-icon currency-icon_usd"></div>
                                    USDT/BTC/ETH/LTC…
                                </div>
                            @else
                                <div class="img-drop-down__value" data-value="{{$currency_code}}">
                                    <div
                                        class="currency-icon currency-icon_{{strtolower($currency_name)}}"></div>{{$currency_name}}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </label>

            @if($captcha_enabled)
                <div class="reg-form__captcha">
                    <div id="g-recaptcha" class="mt-1 flex">
                        <img src="{{ route('captcha', [], false) }}?t={{time()}}" alt="captcha"
                             onclick='this.src = "{{ route('captcha', [], false) }}?t=" + new Date().getTime();'>
                        <input type="tel" class="input__field_modal ml-2" maxlength="4" name="captcha"
                               style="width: 3rem;" oninput="this.value = this.value.replaceAll(/[^\d]/ig, '')">
                    </div>
                    <div class="input__error" style="display: none"></div>
                </div>
            @endif

            <div class="mt-1" style="display: flex; align-items: center; flex-wrap: nowrap; flex-flow: row">
                <label class="blue-white-checkbox">
                    <input name="subscribe" type="checkbox" checked>
                    <span style="font-size: 0.75rem">&nbsp;{{__('cabinet.mail_subscribe')}}</span>
                </label>
            </div>

            <div id="reg-form-ageConfirm" class="reg-form__ageConfirm mt-1">
                <div class="flex ai-center">
                    <label class="blue-white-checkbox">
                        <input type="checkbox" name="ageConfirm" checked>
                        <span style="font-size: 0.75rem">&nbsp;{{__('reg.iam18')}}</span>
                    </label>
                </div>
                <div class="input__error" style="display: none; font-size: 0.75rem;"></div>
            </div>

        </div>

        <div class="section-wrapper" data-section-name="personal-info">
            <div class="register-form__title">{{ __('reg.personal-info') }}</div>

            {{--            <div class="flex" style="overflow: hidden">--}}
            <label class="input__wrap_modal mt-1 reg-form__name">
                <span class="input__label_modal">{{__('reg.name')}}:</span>
                <input type="text" class="input__field_modal" maxlength="50" placeholder="{{__('reg.name')}}"
                       name="name">
                <div class="input__error" style="display: none"></div>
            </label>

            <label class="input__wrap_modal mt-1 reg-form__surname">
                <span class="input__label_modal">{{__('reg.surname')}}:</span>
                <input type="text" class="input__field_modal" maxlength="50" placeholder="{{__('reg.surname')}}"
                       name="surname">
                <div class="input__error" style="display: none"></div>
            </label>
            {{--            </div>--}}

            <label class="input__wrap_modal reg-form__birthday mt-1 input__wrap">
                <span class="input__label_modal">{{__('reg.birthday')}}:</span>
                <input type="date" class="input__field_modal input__field_icon"
                       placeholder="{{ __('cabinet.birthday') }}..." name="birthday"
                       value="{{ now()->format('Y-m-d') }}">
                <div class="input__error" style="display: none"></div>
            </label>


            <label class="input__wrap_modal mt-1 reg-form__country">
                <span class="input__label_modal">{{__('reg.country')}}:</span>
                <input type="text" class="input__field_modal" placeholder="{{__('reg.country')}}" name="country">
                <div class="input__error" style="display: none"></div>
            </label>
            {{--                <label class="input__wrap_modal mt-1 reg-form__postcode">--}}
            {{--                    <span class="input__label_modal">{{__('reg.post_code')}}:</span>--}}
            {{--                    <input type="text" class="input__field_modal" maxlength="10" placeholder="{{__('reg.post_code')}}" name="postcode" style="width: 120px;">--}}
            {{--                    <div class="input__error" style="display: none"></div>--}}
            {{--                </label>--}}

            {{--            <div class="flex" style="overflow: hidden">--}}
            <label class="input__wrap_modal mt-1 reg-form__city">
                <span class="input__label_modal">{{__('reg.city')}}:</span>
                <input type="text" class="input__field_modal" maxlength="30" placeholder="{{__('reg.city')}}:"
                       name="city">
                <div class="input__error" style="display: none"></div>
            </label>

            <label class="input__wrap_modal mt-1 reg-form__state">
                <span class="input__label_modal">{{__('reg.state')}}:</span>
                <input type="text" class="input__field_modal" maxlength="60" name="state"
                       placeholder="{{__('reg.state')}}:">
                <div class="input__error" style="display: none"></div>
            </label>
            {{--            </div>--}}

            {{--            <div class="mt-1" style="font-size: 0.75rem; text-align: center;">--}}
            {{--                {{ __('reg.byClickingIAgreeWith') }} <a href="/privacy-policy/policy_{{ app()->getLocale() }}.html" data-ajax="modal">{{ __('reg.privacyPolicy') }}</a>--}}
            {{--            </div>--}}
        </div>

        <div class="section-controls">
            <button class="login-form__submit-button mt-2 section-next-btn active">{{__('reg.next')}}</button>
            <button type="submit"
                    class="login-form__submit-button mt-2 section-done-btn">{{ __('reg.createAccount') }}</button>
            {{--            <button type="submit" class="login-form__submit-button mt-2">{{ __('reg.createAccount') }}</button>--}}
        </div>

        <div class="mt-1 terms mb-1" style="font-size: 0.75rem; text-align: center;">
            {{ __('reg.byClickingIAgreeWith') }} <a href="/privacy-policy/policy_{{ app()->getLocale() }}.html"
                                                    data-ajax="modal">{{ __('reg.privacyPolicy') }}</a>
        </div>


        @if(config('auth.socialLogin'))
            <div class="login-form__or-with-wrap">
                <div class="login-form__or-with">{{ __('reg.orUsing') }}</div>
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

        <input type="hidden" name="fingerprint" value="" id="fingerprint">
        <input type="hidden" name="timezone" value="" id="tz">

        <div class="login-form__links mt-2">
            <span class="login-form__no-acc-msg">{{ __('reg.haveAccount') }}</span>&nbsp
            <a href="{{ route('login', [], false) }}" class="register-form__create-acc">{{ __('reg.login') }}</a>
        </div>
    </form>
</div>


<script>
    (() => {
        // ext script
        let form = document.getElementById('register-form');
        let emailError = form.querySelector('.reg-form__email .input__error');
        let phoneError = form.querySelector('.reg-form__phone .input__error');
        let passError = form.querySelector('.reg-form__password .input__error');
        let nameError = form.querySelector('.reg-form__name .input__error');
        let surnameError = form.querySelector('.reg-form__surname .input__error');
        let addressError = form.querySelector('.reg-form__address .input__error');
        let cityError = form.querySelector('.reg-form__city .input__error');
        let stateError = form.querySelector('.reg-form__state .input__error');
        let postcodeError = form.querySelector('.reg-form__postcode .input__error');
        let countryError = form.querySelector('.reg-form__country .input__error');

        let emailInput = form.querySelector('input[name="email"]');
        // let phoneInput = form.querySelector('input[name="phone"]');
        let passInput = form.querySelector('input[name="password"]');
        let nameInput = form.querySelector('input[name="name"]');
        let surnameInput = form.querySelector('input[name="surname"]');
        let addressInput = form.querySelector('input[name="address"]');
        let cityInput = form.querySelector('input[name="city"]');
        let stateInput = form.querySelector('input[name="state"]');
        let postcodeInput = form.querySelector('input[name="postcode"]');
        let countryInput = form.querySelector('input[name="country"]');

        let tzInput = form.querySelector('#tz');
        let tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (tzInput !== null) tzInput.value = tz;

        generateFingerprint().then(fingerprint => {
            form.querySelector('#fingerprint').value = fingerprint.hash;
        })


    {{--let captchaWidget = null;--}}
        {{--awaitRecaptchaLoad().then( () => {--}}
        {{--    captchaWidget = grecaptcha.render('g-recaptcha', {--}}
        {{--        'theme' : 'dark',--}}
        {{--        'sitekey' : '{{ config('captcha.siteKey') }}',--}}
        {{--        callback: () => {--}}
        {{--            captchaVerified = true;--}}
        {{--            setTimeout(() => captchaVerified = false, 120000);--}}
        {{--        },--}}
        {{--    });--}}
        {{--});--}}

        // let currencySelect = form.querySelector('select[name="currency"]');

        document.addEventListener('select:currency-dropdown', eurCurrencyListener);

        async function eurCurrencyListener(e) {
            let currency = e.detail.value;
            if (currency != {{\App\UserWallet::CURRENCY_EUR}}) {
                document.removeEventListener('select:currency-dropdown', eurCurrencyListener);
                let href = '{{ route('register.form', [], false) }}?currency=' + currency;
                let method = 'GET';
                let selector = '.modal .content';

                // let phone = phoneInput.value;
                let email = emailInput.value;
                let password = passInput.value;

                let requestParams = {
                    method: method,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        // 'X-Redirect-To': window.location.href
                    },
                };

                let res = await fetch(href, requestParams);

                if (res.ok) {

                    if (res.redirected) {
                        let event = new CustomEvent('redirect', {cancelable: true});
                        if (!event.defaultPrevented) {
                            window.location = res.url;
                        }
                        return;
                    }

                    let data = await res.text();
                    let t = document.querySelector(selector);
                    if (t !== null) {
                        if (window.setInnerHtml === undefined) {
                            t.innerHTML = data;
                        } else {
                            setInnerHtml(t, data);
                            let form = document.getElementById('register-form');
                            let emailInput = form.querySelector('input[name="email"]');
                            // let phoneInput = form.querySelector('input[name="phone"]');
                            let passInput = form.querySelector('input[name="password"]');

                            // phoneInput.value = phone;
                            emailInput.value = email;
                            passInput.value = password;
                        }
                    }

                } else {
                    console.log('error fetching register form');
                }

            } else {
                // change bonus-info currency
                let href = '{{ action('BonusesController@info', ['id' => 'welcome-short', 'in_modal' => true], false) }}&currency=' + currency;
                let bonusLink = document.querySelector('.register-form__wrap-ext .register-form__banner-wrap a');
                if (bonusLink) bonusLink.href = href;
            }
        }

        function inputHandler() {
            let res = true;
            if (passInput.value.trim().length < 3) res = false;
            let loginType = getLoginType();
            // if (loginType === 'phone' && phoneInput.value.trim().length < 8) res = false;
            if (loginType === 'email' && emailInput.value.trim().length < 8) res = false;
            if (res) {
                document.querySelector('.login-form__submit-button').removeAttribute('disabled');
            } else {
                document.querySelector('.login-form__submit-button').setAttribute('disabled', 'disabled');
            }
        }

        form.addEventListener('click', inputHandler);
        emailInput.addEventListener('input', inputHandler);
        //phoneInput.addEventListener('input', inputHandler);
        passInput.addEventListener('input', inputHandler);
        nameInput.addEventListener('input', inputHandler);
        surnameInput.addEventListener('input', inputHandler);
        // addressInput.addEventListener('input', inputHandler);
        cityInput.addEventListener('input', inputHandler);
        stateInput.addEventListener('input', inputHandler);

        // postcodeInput.addEventListener('input', inputHandler);

        function getLoginType() {
            let data = new FormData(form);
            return data.get('login_type');
        }

        form.addEventListener('submit', () => {
            Spinner.show('#register-form');
        });

        form.addEventListener('success', async e => {
            e.preventDefault();
            window.location.href = '{{route('welcome', [], false)}}';
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
                let h = d.getElementsByClassName('header');
                if (h.length > 0) {
                    h = h[0];
                    h.parentNode.removeChild(h);
                    setInnerHtml(document.getElementById('header'), h.innerHTML)
                }
            }

            let event = new CustomEvent('auth', {cancelable: true});
            document.dispatchEvent(event);
            if (event.defaultPrevented) {
                Modal.hide(form);
                return;
            }

            // Enable favourite games button in the menu.
            let favBtn = document.getElementById('fav-games-btn');
            favBtn.style.opacity = '1';
            favBtn.style.pointerEvents = 'all';

            Modal.hide(form);
        });

        form.addEventListener('error', e => {
            e.preventDefault();

            let errors = e.detail.errors;

            if (errors.hasOwnProperty('captcha')) {
                let img = form.querySelector('#g-recaptcha img');
                img.src = "{{ route('captcha', [], false) }}?t=" + new Date().getTime();
            }

            Spinner.hide('#register-form');
        });


        let emailField = form.querySelector('#email');

        if (emailField) {
            emailField.addEventListener('change', function () {
                let expr = /^([a-zA-Z0-9_\-.]+)@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/;

                if (this.value.match(expr) == null) {
                    form.querySelector('.reg-form__email').classList.add('has-error');
                    emailError.textContent = '{{__('reg.invalid_email')}}';
                    emailError.style.display = 'block'
                } else {
                    emailError.style.display = 'none';
                    form.querySelector('.reg-form__email').classList.remove('has-error');
                }
            })
        }

        form.addEventListener('form-closed', () => {
            let event = new CustomEvent('userEvent', {detail: {
                    name: "regnotfin",
                    payload: "null",
                }});
            document.dispatchEvent(event);
        })
    })();
</script>
