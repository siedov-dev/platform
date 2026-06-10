document.addEventListener('click:input__eye-icon', e => {
    let t = e.detail.target;
    let i = t.parentNode.querySelector('input');

    if (i === null) return;

    e.detail.originalEvent.preventDefault();
    e.detail.originalEvent.stopPropagation();

    if (i.getAttribute('type') === 'password') {
        i.setAttribute('type', 'text');
        t.classList.add('input__eye-icon_visible');
    } else {
        i.setAttribute('type', 'password');
        t.classList.remove('input__eye-icon_visible');
    }
});
