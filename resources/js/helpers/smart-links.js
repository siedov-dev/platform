(() => {
    document.addEventListener('click', async e => {
        if (!window.fetch) return;
        let a = e.target;

        if (typeof a.className === 'string' && a.className.indexOf('webim') >= 0) {
            return;
        }

        while (a.tagName !== 'A' && a.parentNode !== null) {
            a = a.parentNode;
        }
        if (a.tagName !== 'A') return;

        if (a.classList.contains('popup-timeout')) {
            let seconds = a.dataset.seconds;
            if (seconds !== null) PopupModal.setTimeout(seconds);
        }

        if (a.getAttribute('href').startsWith('/action/')) {
            e.preventDefault();
            let event = new CustomEvent('action', {detail: {
                    target: a,
                    originalEvent: e,
                    action: a.getAttribute('href'),
                }});
            document.dispatchEvent(event);
            return;
        }

        // if (a.hasAttribute('disabled')) {
        //     e.preventDefault();
        //     return;
        // }

        let selector = a.getAttribute('data-ajax');
        if (selector === '' || selector === null) return;

        e.preventDefault();

        let href = a.getAttribute('href');
        let method = a.getAttribute('data-method');
        let pushState = a.getAttribute('data-push-state');
        let popupType = a.getAttribute('data-popup-type');
        let addClasses = a.getAttribute('data-content-class');
        let addModalClasses = a.getAttribute('data-modal-class');

        if (typeof method === 'string') method = method.toUpperCase();
        if (method !== 'POST') method = 'GET';

        let requestParams = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Redirect-To': window.location.href
            }
        };

        if (method === 'POST') {
            requestParams.headers['Content-Type'] = 'application/json';
            requestParams.body = JSON.stringify({
                _token: document.querySelector('meta[name="csrf"]').getAttribute('content')
            });
        }

        try {
            let res = await fetch(href, requestParams);

            if (res.ok) {

                if (res.redirected) {
                    let event = new CustomEvent('redirect', {cancelable: true});
                    a.dispatchEvent(event);
                    if (!event.defaultPrevented) {
                        window.location = res.url;
                    }
                    return;
                }

                let data = await res.text();
                if (selector !== 'null') {
                    if (selector === 'modal') {
                        let modal;
                        if (popupType === 'bottom-popup' && window.PopupModal.showContent !== undefined) {
                            modal = PopupModal.showContent(data);
                        } else {
                            modal = Modal.show(data, !(popupType === 'slc-popup'));
                        }
                        if (modal !== null) {
                            let slcPopup = modal.querySelector('.popup');
                            if (slcPopup !== null) {
                                modal.querySelector('.popup').style.visibility = "visible";
                                modal.querySelector('.popup').style.opacity = "1";
                            }

                            if (addModalClasses !== null) {
                                for (let cls of addModalClasses.split(" ")) {
                                    modal.querySelector('.modal').classList.add(cls);
                                }
                            }

                            let content = modal.querySelector('.content');
                            if (content !== null && addClasses !== null) {
                                for (let cls of addClasses.split(" ")) {
                                    content.classList.add(cls);
                                }
                            }
                        }
                    } else {
                        let t = document.querySelector(selector);
                        if (t !== null) {

                            let event = new CustomEvent('success', {cancelable: true, detail: data});
                            a.dispatchEvent(event);

                            if (!event.defaultPrevented) {
                                if (window.setInnerHtml === undefined) {
                                    t.innerHTML = data;
                                } else {
                                    setInnerHtml(t, data);
                                }
                            }
                        }
                    }
                }

                if (pushState !== '' && pushState !== null) {
                    history.pushState({}, document.head.getElementsByTagName('title')[0].textContent, href);
                }

            } else {
                // TODO: add event handling.
            }

        } catch (e) {
            console.error(e);
        }
    });

    document.addEventListener('click:content-link', async e => {
        e.detail.originalEvent.preventDefault();

        //hide modals enable scroll
        for ( let modal of document.querySelectorAll('.modal-wrap')) modal.remove();
        let el = document.body;
        el.style.overflowY = null;
        el.style.paddingRight = '0';
        if (el === document.body) {
            let header = document.getElementById('header');
            let overlay = document.getElementById('layout-overlay');
            if (header !== null) {
                header.style.paddingRight = '0';
            }
            if (overlay !== null) {
                overlay.style.paddingRight = '0';
            }
        }

        const content = document.getElementById('layout-content');

        showPreloader(0, 3, false);

        let t = e.detail.target;
        const url = t.getAttribute('href');

        Overlay.hide();
        PopupModal.hide();
        scrollTop();

        try {
            const headerEl = document.getElementById('header');
            if (headerEl !== null) {
                headerEl.classList.remove('sport');
                headerEl.classList.remove('tv-bet');
            }
            document.querySelector('body').classList.remove('sport');
            document.querySelector('body').classList.remove('tv-bet');

            let res = await network.get(url);
            let text = await res.text();
            history.pushState({}, t.textContent, url);
            setInnerHtml(content, text);
        } catch (errorMsg) {

        }

        completePreloader();
    });


    // window.addEventListener('popstate', async e => {
        // e.preventDefault();
        // console.log('reload');
        // window.location.reload(true);

        /*
        if (!window.fetch) return;
        let res = await fetch(window.location.href, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (res.ok) {
            let data = await res.text();

        }
        */
    // });
})();
