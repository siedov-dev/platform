(() => {
    document.addEventListener('click:phone-select', e => {
        let t = e.detail.target;
        t.classList.toggle('shown');
    });

    document.addEventListener('click:phone-select__option', e => {
        let t = e.detail.target;

        let select = t.parentNode.parentNode;
        let value = select.querySelector('.phone-select__value .value-code');
        let val_img = select.querySelector('.phone-select__value .phone-select__option-image');
        select.querySelector('input').value = t.dataset.code;

        value.textContent = t.dataset.code;
        val_img.src = t.querySelector('.phone-select__option-image').src;
    });

})();

