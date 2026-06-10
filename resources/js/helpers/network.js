(() => {
    let authListener = null;

    // setInterval(() => {
    //     let u = window.location.pathname.split('/');
    //     if (u.length < 2) return;
    //     u.shift();
    //     let p = u.shift();
    //     if (p.length === 2) p = u.shift();
    //     if (p === 'play') {
    //         document.body.classList.add('no-panels');
    //     } else {
    //         document.body.classList.remove('no-panels');
    //     }
    // }, 500);

    function navigateHandler(e) {
        let modal = document.querySelector('.modal');
        if (modal !== null) {
            e.preventDefault();
            Modal.hide(modal);
        }
    }

    window.addEventListener('navigate', navigateHandler);
    window.addEventListener('popstate', navigateHandler);

    // window.addEventListener('beforeunload', e => {
    //     if (document.querySelector('.wallet') !== null) return;
    //     let modal = document.querySelector('.modal');
    //     if (modal !== null) {
    //         e.preventDefault();
    //         e.returnValue = '';
    //         Modal.hide(modal);
    //     }
    // });

    window.network = {
        async send(url, method, data, headers, loginIfRequired, ignoreLocale = false) {
            method = method.toUpperCase();

            //add locale to relative urls
            if (!ignoreLocale && /^\/.*$/.test(url)) {
                if (!/^\/[\w]{2}\/.*$/.test(url)) {
                    url = window.userLocaleUrl + url;
                }
            }

            let f = async () => {
                let h = {'X-Requested-With': 'XMLHttpRequest'};
                if (typeof headers === 'object') {
                    for (let p in headers) h[p] = headers[p];
                }
                if (!(data instanceof FormData) && method.toLowerCase() === 'post') {
                    h['Content-Type'] = 'application/json';
                }

                let params = {method: method, headers: h};
                if (method === 'POST' && data !== undefined && data !== null) {
                    params.body = data instanceof FormData ? data : JSON.stringify(data);
                }
                let res;
                try {
                    res = await fetch(url, params);
                    if (res.redirected) {
                        console.log('redirected', res.url);
                    }
                } catch (e) {
                    console.log(e);
                }
                return res;
            }

            let res = await f();

            if (res.status === 429) {
                throw 'Please, try again later.';

            } else if (res.status === 403) {
                let msg = await res.text();
                Modal.show('<div class="layout-message"><div class="layout-message__text">Ваш аккаунт заблокирован:<br/>' + msg + '</div><div class="layout-message__button" onclick="location.reload()">OK</div></div>',true, location.reload)

            } else if (res.status === 406) {
                let msg = await res.text();
                Modal.show('<div class="layout-message"><div class="layout-message__text">Необходима верификация:<br/>' + msg + '</div><div class="layout-message__button" ' +
                    'onclick="Modal.hide(this);let settings = document.querySelector(\'#user-menu-settings\');if(settings) settings.click();">OK</div></div>')

            } else if (res.status === 401) {
                if (loginIfRequired !== false) {
                    try {
                        await requireLogin();
                        res = await f();
                    } catch (e) {
                        throw 'Authorization failed.';
                    }
                }
            } else if (res.status === 500 || res.status === 400) {
                throw await res.text();
            }

            setTimeout(() => {
                document.dispatchEvent(new CustomEvent('ajaxRequest'));
            }, 250);

            // if (url.indexOf('getNewNotifications') < 0
            //     && url.indexOf('balance') < 0
            //     && url.indexOf('GetVegasBonus') < 0
            //     && url.indexOf('GetLastWin') < 0)
            // {
            //     let walletSelector = document.querySelector('.wallet-selector');
            //     if (walletSelector !== null) walletSelector.style.display = null;
            // }

            return res;
        },

        async get(url, loginIfRequired, ignoreLocale = false) {
            return await this.send(url, 'GET', null, null, loginIfRequired, ignoreLocale);
        },

        async post(url, data, headers, loginIfRequired, ignoreLocale = false) {
            if (data === undefined) {
                data = {};
            }
            data._token = document.querySelector('meta[name="csrf"]').getAttribute('content');
            return await this.send(url, 'POST', data, headers, loginIfRequired, ignoreLocale);
        }
    };


    function requireLogin() {
        return new Promise(async (resolve, reject) => {
            await Overlay.hideSpinner();
            await Overlay.hide();

            // Add new auth event listener.
            if (authListener !== null) document.removeEventListener('auth', authListener);
            authListener = e => {
                e.preventDefault();
                resolve();
                document.removeEventListener('auth', authListener);
            };
            document.addEventListener('auth', authListener);

            // Show modal.
            Modal.show('<div class="login-form"></div>');
            Spinner.show('.login-form');

            // Fetch form and put it into the modal.
            let res = await fetch('/login', {
                headers: {'X-Requested-With': 'XMLHttpRequest'}
            });

            if (res.ok) {
                let html = await res.text();
                let content = document.querySelector('.login-form');
                if (content === null) {
                    Modal.show(html);
                } else {
                    setInnerHtml(content.parentNode, html);
                }
            } else {
                reject();
            }
        });
    }
})();
