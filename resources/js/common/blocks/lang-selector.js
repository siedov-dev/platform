(() => {
    let expandedDropdown = null;
    let anim = new Anim(150);

    // Expanding and collapsing language selector.
    document.addEventListener('click:lang-selector', async e => {
        e.detail.originalEvent.processed = true;
        let selector = e.detail.target;
        let dropdown = selector.querySelector('.lang-selector__dropdown');

        if (selector.classList.contains('expanded')) {
            await anim.start(k => {
                dropdown.style.opacity = (1 - k).toFixed(2);
                dropdown.style.transform = 'scale(' + (1 - k).toFixed(2) + ')';
            });
            dropdown.style.display = 'none'
            if (dropdown === expandedDropdown) {
                expandedDropdown = null;
            }
        } else {
            dropdown.style.opacity = '0';
            dropdown.style.transform = 'scale(0)';
            dropdown.style.display = 'flex';
            anim.start(k => {
                dropdown.style.opacity = k.toFixed(2);
                dropdown.style.transform = 'scale(' + k.toFixed(2) + ')';
            });
            expandedDropdown = dropdown;
        }
        selector.classList.toggle('expanded');
    });

    // Close dropdown if user clicks everywhere else of language selector.
    document.addEventListener('click', async e => {
        if (e.processed !== undefined && e.processed !== null && e.processed === true) return;
        let t = e.target;
        do {
            t = t.parentNode;
        } while (t !== null && (typeof e.target.className === 'string' && e.target.className.indexOf('lang-selector') < 0));

        if (t !== null && typeof e.target.className === 'string' && e.target.className.indexOf('lang-selector') >= 0) {
            return;
        }

        if (expandedDropdown !== null) {
            await anim.start(k => {
                expandedDropdown.style.opacity = (1 - k).toFixed(2);
                expandedDropdown.style.transform = 'scale(' + (1 - k).toFixed(2) + ')';
            });
            expandedDropdown.style.display = 'none';
            expandedDropdown.parentNode.classList.remove('expanded');
            expandedDropdown = null;
        }
    });


    document.addEventListener('click:lang-selector__option', e => {
        let option = e.detail.target;
        let lang = option.getAttribute('data-lang');

        let value = option.parentNode.parentNode.querySelector('.lang-selector__value');
        value.textContent = option.textContent;

        let flag = option.parentNode.parentNode.querySelector('.lang-selector__flag');
        flag.setAttribute('src', '/img/flags/' + lang + '.svg');
    });
})();
