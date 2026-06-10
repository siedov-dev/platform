(() => {

    let layoutContent = document.getElementById('layout-content');
    let title = document.getElementById('layout-overlay-title');
    let overlay = document.getElementById('layout-overlay');
    let overlayContent = document.getElementById('layout-overlay-content');
    let overlayContentWrap = document.getElementById('layout-overlay-content-wrap');
    let closeButton = document.getElementById('layout-overlay-close-button');
    let body = document.body;
    let header = document.getElementById('header');

    let anim = new Anim(250);
    let scrollAnim = new Anim(500);
    let titleAnim = new Anim(500);

    let scrollTop = 0;

    window.Overlay = {
        async show(content) {
            if (Overlay.isShown()) return;

            if (typeof content === 'string')
                setInnerHtml(overlayContent, content);

            overlay.style.display = 'flex';
            overlay.style.opacity = '0';
            await anim.start(k => overlay.style.opacity = k.toString());
            overlay.classList.add('shown');
            scrollTop = document.body.scrollTop;
            layoutContent.style.display = 'none';
        },

        async hide() {
            if (!Overlay.isShown()) return;
            layoutContent.style.display = null;
            await anim.start(k => overlay.style.opacity = (1 - k).toString());
            overlay.classList.remove('shown');
            overlay.style.display = 'none';
            scrollAnim.start(k => document.body.scrollTop = scrollTop * k);
            overlayContent.innerHTML = '';
            for (let menu of document.querySelectorAll('.user__menu-link')) menu.classList.remove('active');
            for (let menu of document.querySelectorAll('.user-menu__link')) menu.classList.remove('active');
            for (let menu of document.querySelectorAll('.cabinet__menu-link')) menu.classList.remove('active');
        },

        isShown() {
            return overlay.classList.contains('shown');
        },

        async setTitle(t) {
            if (!Overlay.isShown()) title.textContent = '';
            await titleAnim.start(k => title.style.opacity = (1 - k).toString());
            title.textContent = t;
            await titleAnim.start(k => title.style.opacity = k.toString());
        },

        showSpinner() {
            return Spinner.show(overlayContentWrap);
        },

        hideSpinner(callback) {
            return Spinner.hide(overlayContentWrap, undefined, callback);
        },

        getContentNode() {
            return overlayContent;
        }
    };

    closeButton.addEventListener('click', Overlay.hide);
})();


