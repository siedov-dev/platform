<form action="/login" id="login-form" method="POST" class="login-form" data-ajax="yes">
    @csrf
    <div class="login-form__title">Авторизация</div>

    <div class="tabs login-form__tabs">
        <div class="tabs__tab tabs__tab_active" data-target="#login-form-email">Email</div>
        <div class="tabs__tab" data-target="#login-form-phone">Телефон</div>
    </div>

    <label id="login-form-email" class="input__wrap_modal">
        <span class="input__label_modal">Ваш email:</span>
        <input type="text" class="input__field_modal" placeholder="Email..." name="email">
    </label>

    <label id="login-form-phone" class="input__wrap_modal" style="display:none;">
        <span class="input__label_modal">Ваш телефон:</span>
        <input type="text" class="input__field_modal" placeholder="Телефон..." name="phone">
    </label>

    <label class="input__wrap_modal login-form__pass-field">
        <span class="input__label_modal">Ваш пароль:</span>
        <input type="password" class="input__field_modal" placeholder="Введите пароль..." name="password">
    </label>


    <a href="#" class="login-form__forgot-pass">Забыли пароль?</a>


    <button type="submit" class="login-form__submit-button">Войти</button>


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
        <span class="login-form__no-acc-msg">Нет аккаунта?</span>&nbsp
        <a href="#" class="login-form__create-acc">Создайть аккаунт</a>
    </div>
</form>


<script>
    (() => {
        let form = document.getElementById('login-form');

        form.addEventListener('submit', () => {
            Spinner.show('#login-form');
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

            // Dispatch event that indicates authorization success.
            let event = new CustomEvent('auth', {cancelable: true});
            document.dispatchEvent(event);
            if (event.defaultPrevented) {
                Modal.hide(form);
                return;
            }

            // Process cabinet content
            let c = d.getElementsByClassName('cabinet');
            if (c.length > 0) {
                Overlay.setTitle('Profile');
                c = c[0];
                c.parentNode.removeChild(c);
                Overlay.getContentNode().innerHTML = '';
                setInnerHtml(Overlay.getContentNode(), c.outerHTML);

                setTimeout(() => Overlay.show(null), 1000);
            }

            Modal.hide(form);
        });

        form.addEventListener('error', e => {
            console.log(e);
            Spinner.hide('#login-form', e.detail);
        });
    })();
</script>
