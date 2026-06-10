(() => {
    const MODE_STANDALONE     = 1;
    const MODE_BROWSER        = 2;
    const MODE_STANDALONE_IOS = 3;


    function checkBrowser() {
        let browser = "";
        let c = navigator.userAgent.search("Chrome");
        let f = navigator.userAgent.search("Firefox");
        if (c > -1) {
            browser = "Chrome";
        } else if (f > -1) {
            browser = "Firefox";
        }
        return browser;
    }


    window.addEventListener('load', () => {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('../sw.js')
                .then(registration => {
                    console.log('Service worker successfully registered', registration);
                })
                .catch(error => {
                    console.log('Service worker registration failed', error);
                });
        }
    });


    function getMode() {
        let displayMode = MODE_BROWSER;
        if (navigator.standalone) {
            displayMode = MODE_STANDALONE_IOS;
            return displayMode;
        }
        if (window.matchMedia('(display-mode: standalone)').matches) {
            displayMode = MODE_STANDALONE;
        }
        return displayMode;
    }


    function checkVersion() {
        let agent = window.navigator.userAgent,
            start = agent.indexOf('OS ');
        if ( ( agent.indexOf( 'iPhone' ) > -1 || agent.indexOf( 'iPad' ) > -1 ) && start > -1 ) {
            return window.Number( agent.substr( start + 3, 3 ).replace( '_', '.' ) );
        }
        return 0;
    }


    function isIos() {
        let userAgent = window.navigator.userAgent.toLowerCase();
        return /iphone|ipad|ipod/.test(userAgent);
    }

    let isiPad = (navigator.userAgent.match(/iPad/i) !== null);
    let isiPhone = (navigator.userAgent.match(/iphone/i) !== null);
    let server = localStorage.getItem("done_pop");
    let mode = getMode();

    if(server == null){
        localStorage.setItem("done_pop", '1');
    }else{
        localStorage.setItem("done_pop", server);
    }


    if (mode !== MODE_BROWSER) {
        // add_pwa.style.display = "none";
    }

    let installEvent = null;
    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        installEvent = event;
    });


    if (isiPhone === true && isIos() === true && +checkVersion() >= 11.3 && mode === MODE_BROWSER) {
        hideMenuItem();
    }

    if (mode === MODE_BROWSER && checkBrowser() === 'Firefox') {
        hideMenuItem();
    }

    if (mode === MODE_BROWSER) {
        if (navigator.getInstalledRelatedApps !== undefined) {
            navigator.getInstalledRelatedApps().then(apps => {
                if (apps.length > 0) hideMenuItem();
            });
        } else {
            hideMenuItem();
        }
    }


    function hideMenuItem() {
        let items = document.getElementsByClassName('menu__item_app');
        for (let i = 0; i < items.length; i++) {
            items[i].style.display = 'none';
        }
    }

    window.addEventListener('appInstallRequest', () => {
        if(isiPhone === true && isIos() === true && +checkVersion() >= 11.3 && mode === MODE_BROWSER){
            document.querySelector(".alert-custom").classList.add('active');
        } else {
            if (installEvent !== null) {
                installEvent.prompt();
                installEvent.userChoice
                    .then(choiceResult => {
                        if (choiceResult.outcome === 'accepted') {
                            localStorage.setItem("done_pop", '0');
                            console.log('Успешно установлено');
                        } else {
                            console.log('Закрыл приложение')
                        }
                        event = null
                    })
            }
        }
    });

})();



