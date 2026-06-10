(() => {
    let header = null;
    let content = null;
    let anim = new Anim(500);

    document.addEventListener('DOMContentLoaded', () => {
        header = document.getElementById('header');
        content = document.getElementById('layout-content');
    });



    async function shadeContent() {
        scrollTop();
        Spinner.show('#layout-content-wrap');
        anim.start(k => content.style.opacity = (1-k*0.9).toFixed(2));
    }


    async function removeContentShading() {
        Spinner.hide('#layout-content-wrap');
        anim.stop();
        await anim.start(k => content.style.opacity = k);
    }

    let up = false;
    let lastScrollTop = document.body.scrollTop || document.documentElement.scrollTop;

    function checkScrollPosition() {
        if (header === null) return;
        let st = document.body.scrollTop || document.documentElement.scrollTop;
        let _up = st - lastScrollTop < 0;
        if (_up !== up) up = _up;
        // if (window.innerWidth < 800) {
            // let top = parseInt(header.style.top.replace('px', ''));
            // if (isNaN(top)) top = 0;
            // let d = Math.abs(st - lastScrollTop);
            // if (up) {
            //     if (st <= 0) {
            //         header.style.top = '0';
            //     } else {
            //         header.style.top = Math.min(top + d, 0) + 'px';
            //     }
            // } else {
            //     if (st <= 0) {
            //         header.style.top = '0';
            //     } else {
            //         header.style.top = Math.max(top - d, -80) + 'px';
            //     }
            // }
        // } else {
            if (st > 10) {
                header.classList.add('header_scrolled');
            } else {
                header.classList.remove('header_scrolled');
            }
        // }
        lastScrollTop = st;
    }


    document.body.onscroll = checkScrollPosition;
    document.addEventListener('DOMContentLoaded', checkScrollPosition);

    document.addEventListener('click:header__button', async e => {
        let t = e.detail.target;
        e.detail.originalEvent.preventDefault();

        shadeContent();
        Overlay.showSpinner();

        let url = t.getAttribute('href');
        let res = await network.get(url);
        let html = await res.text();
        Overlay.hideSpinner();
        Overlay.hide();
        let content = document.getElementById('layout-content');
        history.pushState({}, t.textContent, url);
        setInnerHtml(content, html);
        removeContentShading();
    });


    (() => {
        let changeLoginForm = null;
        document.addEventListener('click:change-login-button', async e => {
            if (changeLoginForm === null) {
                changeLoginForm = document.getElementById('change-login-form');
            }
            if (changeLoginForm !== null) {
                changeLoginForm.querySelector('input[type="text"]').value = '';
                Modal.show(changeLoginForm);
                changeLoginForm.style.display = 'flex';
            }
        });
    })();
})();
