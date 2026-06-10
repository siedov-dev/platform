<style>
    #change-login-form {
        display: none;
        flex-direction: column;
        padding: 3rem;
        background-color: #fff;
        border-radius: 1rem;
        width: 27rem;
        max-width: 100%;
    }
    #change-login-form .spinner {
        background-color: rgba(255,255,255, 0.75);
        border-radius: 1rem;
    }
    .change-login-form__title {
        text-align: center;
        margin-bottom: 2rem;
        font-weight: 700;
        font-size: 1.5rem;
        color: #333;
    }
</style>



<form action="{{ route('changeLogin', [], false) }}" method="POST" id="change-login-form">
    @csrf

    <div class="change-login-form__title">{{ __('cabinet.loginChanging') }}</div>

    <label class="cabinet__vrf-code-field">
        <span class="cabinet__vrf-label">{{ __('cabinet.newLogin') }}</span>
        <input type="text" name="login" class="cabinet__vrf-code-input" autocomplete="off" maxlength="16">
    </label>

    <button type="submit" class="cabinet__vrf-button">{{ __('userMenu.changeNickname') }}</button>
</form>



<script>
    (() => {
        let form = document.getElementById('change-login-form');

        form.addEventListener('submit', async e => {
            e.preventDefault();

            let url = form.getAttribute('action');
            let data = new FormData(form);
            let newLogin = data.get('login');

            if (newLogin === '') return;

            Spinner.show(form);
            try {
                let res = await network.post(url, data);
                let msg = await res.text();
                await Spinner.hide(form, msg);
                Modal.hide(form);

                let t = document.querySelector('.user-menu__name');
                if (t !== null) t.textContent = newLogin;

                t = document.querySelector('.user-menu__dd-name');
                if (t !== null) t.textContent = newLogin;

                t = document.querySelector('.cabinet__menu-name');
                if (t !== null) t.textContent = newLogin;

            } catch (errorMsg) {
                await Spinner.hide(form, errorMsg);
            }
        });
    })();
</script>
