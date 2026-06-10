(() => {
    const checkInterval = 10;

    window.PopupModal = {
        suspended: false,

        async checkNew() {
            //postpone next popup when the other one is shown
            if (PopupModal.isShown() || PopupModal.isWalletShown()) {
                PopupModal.prolongTimeout(checkInterval);
                return;
            }

            // stop updating when the game is active
            if (PopupModal.isGameActive() || PopupModal.suspended) {
                console.log('skip');
                return;
            }

            let data = await window.network.get('/popups/getNew');
            let popupData = await data.text();
            if (popupData === "") return;
            document.getElementById('popup-modal').querySelector('.content').innerHTML = popupData;
            PopupModal.show();
        },
        async show()
        {
            document.dispatchEvent(new CustomEvent('showPopupModal'));
        },
        async hide()
        {
            document.dispatchEvent(new CustomEvent('hidePopupModal'));
        },
        isShown()
        {
            return document.querySelector('#popup-modal.shown') !== null;
        },
        isWalletShown()
        {
            return document.querySelector(".wallet-wrapper.shown") || document.querySelector(".modal-wrap.shown .wallet") !== null;
        },
        isGameActive()
        {
            return document.getElementById("game-wrapper") !== null;
        },
        async unsubCategory(category)
        {
            data  = {category: category};
            try {
                let resp = await window.network.post('/popups/unsub', data);
            } catch (e) {
                console.log(e);
            }
        },
        async setTimeout(seconds)
        {
            //console.log('timeout '+ seconds);
            data  = {seconds: seconds};
            try {
                let resp = await window.network.post('/popups/timeout', data);
            } catch (e) {
                console.log(e);
            }
        },
        async prolongTimeout(seconds)
        {
            console.log('prolong '+ seconds);
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
        if (modal && modal.querySelector('.wallet') !== null) PopupModal.setTimeout(30);
    });

    //when user closes wallet window next popup inm 30 seconds
    document.addEventListener('click:user-wallet__close-btn', (e) => {
        PopupModal.setTimeout(30);
    });

    setInterval(PopupModal.checkNew, checkInterval  * 1000);
})();
