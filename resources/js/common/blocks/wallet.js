document.addEventListener('click:wallet__payment-system', e => {
    let t = e.detail.target;

    let hint = t.getAttribute('data-hint');
    if (hint !== null && hint !== undefined && hint !== '') {
        let label = document.querySelector('.wallet__requisites-label');
        if (label !== null) label.textContent = hint;

        let input = document.querySelector('input[name="cardNumber"]');
        if (input !== null) input.setAttribute('placeholder', hint);
    }

    let active = t.parentNode.parentNode.querySelector('.wallet__payment-system_active');
    if (active !== null) active.classList.remove('wallet__payment-system_active');
    t.classList.add('wallet__payment-system_active');
});
