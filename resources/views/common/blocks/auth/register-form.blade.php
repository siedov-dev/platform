<form action="{{ route('register', [], false) }}" id="register-form" method="POST" class="login-form" data-ajax="yes">
    @csrf

    <div class="login-form__hint">150% + 40FS За первый депозит</div>

    <div class="login-form__title">Регистрация</div>

    <div class="tabs login-form__tabs">
        <div class="tabs__tab tabs__tab_active" data-target="#login-form-email">Email</div>
        <div class="tabs__tab" data-target="#login-form-phone">Телефон</div>
    </div>

    <label id="login-form-email" class="input__wrap_modal reg-form__email mt-2">
        <span class="input__label_modal">Ваш email:</span>
        <input type="text" class="input__field_modal" placeholder="Email..." name="email">
        <div class="input__error" style="display: none"></div>
    </label>

    <label id="login-form-phone" class="input__wrap_modal reg-form__phone mt-2" style="display:none;">
        <span class="input__label_modal">Ваш телефон:</span>
        <input type="text" class="input__field_modal" placeholder="Телефон..." name="phone">
        <div class="input__error" style="display: none"></div>
    </label>

    <label class="input__wrap_modal reg-form__pass mt-2">
        <span class="input__label_modal">Ваш пароль:</span>
        <input type="password" class="input__field_modal" placeholder="Введите пароль..." name="password">
        <div class="input__error" style="display: none"></div>
    </label>

    <label class="input__wrap_modal reg-form__currency mt-2">
        <span class="input__label_modal">Выбирите основную валюту:</span>
        <select class="input__field_modal" name="currency">
            <option value="2">Гривны</option>
            <option value="1">Рубли</option>
        </select>
        <div class="input__error" style="display: none"></div>
    </label>


    <a href="#" class="login-form__forgot-pass">Забыли пароль?</a>


    <button type="submit" class="login-form__submit-button">Создать аккаунт</button>


    <div class="login-form__or-with-wrap">
        <div class="login-form__or-with">или через</div>
    </div>

    <div class="login-form__social-links">
        <a href="#" class="login-form__social-link"><img src="/img/auth/telegram.svg"></a>
        <a href="#" class="login-form__social-link"><img src="/img/auth/facebook.svg"></a>
        <a href="#" class="login-form__social-link"><img src="/img/auth/twitter.svg"></a>
        <a href="#" class="login-form__social-link"><img src="/img/auth/google.svg"></a>
    </div>


    <div class="login-form__links">
        <span class="login-form__no-acc-msg">Есть аккаунт?</span>&nbsp
        <a href="#" class="login-form__create-acc">Войти</a>
    </div>
</form>


<script>
    (() => {
        let form = document.getElementById('register-form');
        let emailError = form.querySelector('.reg-form__email .input__error');
        let phoneError = form.querySelector('.reg-form__phone .input__error');
        let passError = form.querySelector('.reg-form__pass .input__error');

        form.addEventListener('submit', () => {
            Spinner.show('#register-form');
            emailError.style.display = 'none';
            phoneError.style.display = 'none';
            passError.style.display = 'none';
            form.querySelector('.reg-form__email').classList.remove('has-error');
            form.querySelector('.reg-form__phone').classList.remove('has-error');
            form.querySelector('.reg-form__pass').classList.remove('has-error');
        });

        form.addEventListener('success', async e => {
            e.preventDefault();

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

            let event = new CustomEvent('auth', {cancelable: true});
            document.dispatchEvent(event);
            if (event.defaultPrevented) {
                Modal.hide(form);
                return;
            }

            // Open bonuses.
            let bonusesLink = document.querySelector('.cabinet__bonuses-link');
            if (bonusesLink !== null) bonusesLink.click();

            Modal.hide(form);
        });

        form.addEventListener('error', e => {
            e.preventDefault();

            Spinner.hide('#register-form');

            let errors = e.detail.errors;

            if (errors.hasOwnProperty('email')) {
                form.querySelector('.reg-form__email').classList.add('has-error');
                emailError.textContent = errors.email[0];
                emailError.style.display = 'block';
            }

            if (errors.hasOwnProperty('phone')) {
                form.querySelector('.reg-form__phone').classList.add('has-error');
                phoneError.textContent = errors.email[0];
                phoneError.style.display = 'block'
            }

            if (errors.hasOwnProperty('password')) {
                form.querySelector('.reg-form__pass').classList.add('has-error');
                passError.textContent = errors.password[0];
                passError.style.display = 'block';
            }
        });
    })();
</script>
