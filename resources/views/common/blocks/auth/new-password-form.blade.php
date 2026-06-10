<form action="{{ action('AuthController@setNewPassword', $_GET, false) }}" method="POST" data-ajax="yes" class="new-pass-form">
    @csrf
    <div class="login-form__title">{{ __('login.passChanging') }}</div>

    <label class="input__wrap_modal reg-form__pass mt-2">
        <span class="input__label_modal">{{ __('login.yourPassword') }}:</span>
        <input type="password" class="input__field_modal" placeholder="{{ __('login.inputPassword') }}..." name="pass">
        <div class="input__error" style="display: none"></div>
    </label>

    <label class="input__wrap_modal reg-form__pass mt-2">
        <span class="input__label_modal">{{ __('login.repeatPassword') }}:</span>
        <input type="password" class="input__field_modal" placeholder="{{ __('login.repeatPassword') }}..." name="passRepeat">
        <div class="input__error" style="display: none"></div>
    </label>

    <button type="submit" class="login-form__submit-button mt-2">{{ __('login.changePassword') }}</button>
</form>
<script>
    let form = document.querySelector('.new-pass-form');
    Modal.show(form);

    form.addEventListener('submit', () => {
        Spinner.show(form);
    });

    form.addEventListener('success', async e => {
        e.preventDefault();
        // await Spinner.hide(form, e.detail);
        // Modal.hide(form);
        for (let form of document.querySelectorAll('form')) {
            if (form.classList.contains('new-pass-form')) continue;
            form.remove();
        }
        form.setAttribute('data-ajax', '');
        form.submit();
    });

    form.addEventListener('error', e => {
        Spinner.hide(form, e.detail);
    });
</script>
