(() => {
    const checkInterval = 5;

    window.PopupModal = {
        suspended: false,
        authPopupTimerId: null,

        async checkNew() {
            //postpone next popup when the other one is shown
            if (PopupModal.isShown() || PopupModal.isWalletShown()) {
                PopupModal.prolongTimeout(checkInterval);
                return;
            }

            // stop updating when the game is active
            if (PopupModal.isGameActive() || PopupModal.suspended || Overlay.isShown()) {
                // console.log('skip popup check');
                return;
            }

            // skip if there's a block-popups element
            if (document.querySelector('.block-popups') !== null) {
                // console.log('block-popups');
                return;
            }

            let data = await window.network.get('/popups/getNew');
            let popupData = await data.text();
            if (popupData === "") return;
            document.getElementById('popup-modal').querySelector('.content').innerHTML = popupData;
            PopupModal.show();
        },
        canShow() {
            //postpone next popup when the other one is shown
            if (PopupModal.isShown() || PopupModal.isWalletShown()) {
                return false;
            }

            // stop updating when the game is active
            if (PopupModal.isGameActive() || PopupModal.suspended || Overlay.isShown()) {
                return false;
            }

            // skip if there's a block-popups element
            if (document.querySelector('.block-popups') !== null) {
                return false;
            }

            return true;
        },
        async checkRegPopup() {
            let data = await window.network.get('/popups/regPopup');
            let popupData = await data.text();
            if (popupData === "") return;
            Modal.show(popupData, true);
        },
        async checkAuthPopup() {
            let shownCount = sessionStorage.getItem("AuthPopupC");
            let showAt = sessionStorage.getItem("AuthPopupAt");
            if (shownCount === null || showAt === null) {
                shownCount = 0;
                sessionStorage.setItem("AuthPopupC", shownCount.toString());
                showAt = Date.now() + 20000; //20 sec
                sessionStorage.setItem("AuthPopupAt", showAt.toString());
            }
            if (PopupModal.isLoginShown() || PopupModal.isRegistrationShown()) {
                showAt = Date.now() + 20000; //+20 sec to show
                sessionStorage.setItem("AuthPopupAt", showAt.toString());
            }
            if (shownCount < 2 && +showAt < Date.now() && PopupModal.canShow()) {
                let data = await window.network.get('/popups/authPopup');
                let popupData = await data.text();
                if (popupData === "") return;
                document.getElementById('popup-modal').querySelector('.content').innerHTML = popupData;
                PopupModal.show();
                shownCount++;
                sessionStorage.setItem("AuthPopupC", shownCount.toString());
                PopupModal.cancelAuthPopup()
            }
            if (shownCount >= 2) PopupModal.cancelAuthPopup();
        },
        async scheduleAuthPopup() {
            if (PopupModal.authPopupTimerId === null) {
                let showAt = Date.now() + 20000; //20 sec
                sessionStorage.setItem("AuthPopupAt", showAt.toString());
                PopupModal.authPopupTimerId = setInterval(() => {
                    PopupModal.checkAuthPopup()
                }, 1000);
            }
        },
        cancelAuthPopup() {
            console.log("cancel auth popup");
            if (PopupModal.authPopupTimerId !== null) {
                clearInterval(PopupModal.authPopupTimerId);
                PopupModal.authPopupTimerId = null;
            }
        },
        async show() {
            document.dispatchEvent(new CustomEvent('showPopupModal'));
        },
        async hide() {
            document.dispatchEvent(new CustomEvent('hidePopupModal'));
        },
        isShown() {
            return document.querySelector('#popup-modal.shown') !== null;
        },
        isWalletShown() {
            return document.querySelector(".wallet-wrapper.shown") || document.querySelector(".modal-wrap.shown .wallet") !== null;
        },
        isLoginShown() {
            return document.querySelector(".modal-wrap.shown #login-form") !== null;
        },
        isRegistrationShown() {
            return document.querySelector(".modal-wrap.shown #register-form") !== null;
        },
        isGameActive() {
            return document.getElementById("game-wrapper") !== null;
        },
        async unsubCategory(category) {
            data  = {category: category};
            try {
                let resp = await window.network.post('/popups/unsub', data);
            } catch (e) {
                console.log(e);
            }
        },
        async setTimeout(seconds) {
            // console.log('timeout '+ seconds);
            data  = {seconds: seconds};
            try {
                let resp = await window.network.post('/popups/timeout', data);
            } catch (e) {
                console.log(e);
            }
        },
        async prolongTimeout(seconds) {
            // console.log('prolong '+ seconds);
            data  = {seconds: seconds};
            try {
                let resp = await window.network.post('/popups/prolong', data);
            } catch (e) {
                console.log(e);
            }
        },
        setSuspended($suspended) {
            PopupModal.suspended = $suspended;
        },
    };

    document.addEventListener('click:popup-timeout', (e) => {
        let t = e.detail.target;
        let seconds = t.dataset.seconds;
        if (seconds !== null) PopupModal.setTimeout(seconds);
        PopupModal.hide();
    });

    document.addEventListener('click:popup-unsub_click', (e) => {
        let t = e.detail.target;
        let category = t.dataset.category;
        if (category !== null) PopupModal.unsubCategory(category);
        PopupModal.hide();
    });

    document.addEventListener('click:close-button', (e) => {
        let modal = e.detail.target.closest('.modal-wrap');
        if (modal && modal.querySelector('.wallet') !== null) PopupModal.setTimeout(10);

        let modalParams = modal.querySelector('.modal-params');

        if (modalParams !== null && modalParams.hasAttribute('data-refresh_on_close')) {
            reloadContent();
        }
        if (modalParams !== null && modalParams.hasAttribute('data-show-next-popup')) {
            PopupModal.setTimeout(0);
            PopupModal.checkNew();
        }
    });

    document.addEventListener('click:popup-later-btn', (e) => {
        let modal = e.detail.target.closest('.modal-wrap');

        let modalParams = modal.querySelector('.modal-params');

        if (modalParams !== null && modalParams.hasAttribute('data-refresh_on_close')) {
            reloadContent();
        }
        if (modalParams !== null && modalParams.hasAttribute('data-show-next-popup')) {
            PopupModal.setTimeout(0);
            PopupModal.checkNew();
        }
    });

    //when user closes wallet window next popup in 10 seconds
    document.addEventListener('click:user-wallet__close-btn', (e) => {
        PopupModal.setTimeout(10);
    });

    document.addEventListener('showPopupModal', () => {
        let menuDialog = document.querySelector('#popup-modal');
        if (menuDialog !== null) {
            menuDialog.classList.add('shown');
        }
    });

    document.addEventListener('hidePopupModal', () => {
        let menuDialog = document.querySelector('#popup-modal');
        if (menuDialog !== null) {
            menuDialog.classList.remove('shown');
        }

        let modalParams = menuDialog.querySelector('.modal-params');
        // console.log('params', modalParams);
        if (modalParams !== null && modalParams.hasAttribute('data-refresh_on_close')) {
            reloadContent();
        }
        if (modalParams !== null && modalParams.hasAttribute('data-show-next-popup')) {
            PopupModal.setTimeout(5);
            // PopupModal.checkNew();
        }
    });

    setInterval(PopupModal.checkNew, checkInterval  * 1000);
})();
