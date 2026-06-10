(() => {

    let currencySelector = null;
    let int = null;
    let rest = null;
    let icon = null;
    let items = null;
    let ready = true;

    // Currency switching
    document.addEventListener('click:wallet-selector__item', async e => {
        let t = e.detail.target;
        ready = false;
        currencySelector = t.closest('.header__wallet-selector.wallet-selector');
        int = currencySelector.querySelector('.wallet-selector__amount-int');
        rest = currencySelector.querySelector('.wallet-selector__amount-rest');
        icon = currencySelector.querySelector('.wallet-selector__icon');
        items = currencySelector.getElementsByClassName('wallet-selector__item');

        let curr = t.querySelector('.wallet-selector__currency').textContent;
        icon.className = 'wallet-selector__icon currency-icon currency-icon_' + curr.toLowerCase();
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('wallet-selector__item_active');
        }
        t.classList.add('wallet-selector__item_active');

        let a = t.querySelector('.wallet-selector__amount-int');
        let r = t.querySelector('.wallet-selector__amount-rest');

        int.textContent = a.textContent;
        rest.textContent = r.textContent;

        try {
            await network.post('/wallet/switch', {currency: t.dataset.id});
            updateCurrencies(curr.toLowerCase());
            ready = true;
        } catch (e) {
            ready = true;
            console.log(e.toString());
        }
    });

    document.addEventListener('click:cabinet__change-currency', async e => {
        let t = e.detail.target;
        ready = false;
        try {
            await network.post('/wallet/switch', {currency: t.dataset.id});
            updateCurrencies(t.dataset.name.toLowerCase());
            ready = true;
        } catch (e) {
            ready = true;
            console.log(e.toString());
        }
    });

    function updateCurrencies(currencyName)
    {
        updateBalance();
        let cabinetWallets = document.querySelector('.cabinet__money-wallets');
        if (cabinetWallets !== null) {
            for (let el of cabinetWallets.querySelectorAll('.cabinet__money-wallet')) el.classList.remove('active');
            cabinetWallets.querySelector('.currency-icon_'+currencyName).parentElement.classList.add('active');
        }
         for (let walletSelector of document.querySelectorAll('.wallet-selector')) {
             let t = walletSelector.querySelector('.wallet-selector__dropdown .wallet-selector__item_'+currencyName);
             if (t !== null) {
                 ready = false;
                 currencySelector = t.closest('.header__wallet-selector.wallet-selector');
                 int = currencySelector.querySelector('.wallet-selector__amount-int');
                 rest = currencySelector.querySelector('.wallet-selector__amount-rest');
                 icon = currencySelector.querySelector('.wallet-selector__icon');
                 items = currencySelector.getElementsByClassName('wallet-selector__item');

                 let curr = t.querySelector('.wallet-selector__currency').textContent;
                 icon.className = 'wallet-selector__icon currency-icon currency-icon_' + curr.toLowerCase();
                 for (let i = 0; i < items.length; i++) {
                     items[i].classList.remove('wallet-selector__item_active');
                 }
                 t.classList.add('wallet-selector__item_active');

                 let a = t.querySelector('.wallet-selector__amount-int');
                 let r = t.querySelector('.wallet-selector__amount-rest');

                 int.textContent = a.textContent;
                 rest.textContent = r.textContent;

                 ready = true;
             }
         }
    }


    // Balance updating.
    // setInterval(async () => {
    //     updateBalance();
    // }, 1000);

    document.addEventListener('wallet:update_balances', async e => {
        await updateBalance();
    })

    document.addEventListener('wallet:amount_changed', async e => {
        console.log("update balance");
        try {
            const payload = e.detail.payload;
            const data = JSON.parse(payload);
            const currency = data['currency'];
            const currentCurrId = data['currency_id'];
            const amount = data['amount'];
            const bonusID = data['bonus_id'];
            const walletSelector = document.querySelector('.header__wallet-selector.wallet-selector');
            if (walletSelector === null) return;

            let r = amount.toString().split(".")[1] ? amount.toString().split(".")[1].padEnd(2, '0') : '00';

            let mainItems = document.querySelectorAll('.wallet-selector .currency-icon_' + currency.toLowerCase());
            for (let mainItem of mainItems) {
                let mainInt = mainItem.parentElement.querySelector('.wallet-selector__amount > .wallet-selector__amount-int');
                let mainRest = mainItem.parentElement.querySelector('.wallet-selector__amount > .wallet-selector__amount-rest');

                mainInt.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                // mainRest.textContent = r.toString().padEnd(2, '0');
                mainRest.textContent = r;
            }

            let items = document.querySelectorAll('.wallet-selector__item_' + currency.toLowerCase());
            for (let item of items) {
                let int = item.querySelector('.wallet-selector__amount-int');
                let rest = item.querySelector('.wallet-selector__amount-rest');

                int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                // rest.textContent = r.toString().padEnd(2, '0');
                rest.textContent = r;

                if (item.classList.contains('wallet-selector__item_active')) {
                    int = item.parentNode.parentNode.querySelector('.wallet-selector__amount-int');
                    rest = item.parentNode.parentNode.querySelector('.wallet-selector__amount-rest');

                    int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                    // rest.textContent = r.toString().padEnd(2, '0');
                    rest.textContent = r;
                }
            }

            if (bonusID > 0) {
                let amount = data['bonus_balance'];
                let r = amount.toString().split(".")[1] ? amount.toString().split(".")[1].padEnd(2, '0') : '00';
                let items = document.querySelectorAll('.wallet-bonus-balance .wallet-selector__item_' + currency.toLowerCase());
                for (let item of items) {
                    let int = item.querySelector('.wallet-selector__amount-int');
                    let rest = item.querySelector('.wallet-selector__amount-rest');

                    int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                    rest.textContent = r;

                    if (item.classList.contains('wallet-selector__item_active')) {
                        int = item.parentNode.parentNode.querySelector('.wallet-selector__amount-int');
                        rest = item.parentNode.parentNode.querySelector('.wallet-selector__amount-rest');

                        int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                        rest.textContent = r;
                    }
                }
            }
        } catch (e) {
            console.log(e.toString());
        }
    })

    async function updateBalance() {
        // If no selector on page (for example user is not authorized) - don't do anything.
        let selector = document.querySelector('.wallet-selector');
        if (selector === null) return;

        // Don't update balance if user plays game.
        //if (window.location.pathname.indexOf('/play/') !== -1) return;

        let res = await fetch(window.userLocaleUrl + '/wallet/balance');

        if (res.ok) {
            let data = await res.json();

            // User can start playing when request was sent but response didn't receive yet.
            // Avoid balance updating in this situation.
            //if (window.location.pathname.indexOf('/play/') !== -1) return;

            // let walletSelector = document.querySelector('.header__wallet-selector.wallet-selector');
            // if (walletSelector !== null) walletSelector.style.display = null;

            let currentCurrId = data['current']['id'];
            let currentCurrName = data['current']['name'];
            let currentCurrVal = data['current']['value'];

            let amountR = currentCurrVal.toString().split(".")[1] ? currentCurrVal.toString().split(".")[1].padEnd(2, '0') : '00';
            let amountInt = Math.floor(currentCurrVal).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';

            document.querySelectorAll('.main-currency-int').forEach(e => e.textContent = amountInt);
            document.querySelectorAll('.main-currency-rest').forEach(e => e.textContent = amountR);
            document.querySelectorAll('.main-currency-icon')
                .forEach(e => e.classList.forEach((className) => {
                    if (className.startsWith('currency-icon_')) {
                        e.classList.remove(className);
                        e.classList.add('currency-icon_' + currentCurrName.toLowerCase())
                    }
                }));

            for (let currency in data) {
                // if (currency === 'events') {
                //     for (let ev of data['events']) {
                //         let event = new CustomEvent('userEvent', {detail: {
                //                 name: ev.event,
                //                 payload: ev.payload,
                //             }});
                //         document.dispatchEvent(event);
                //     }
                //     continue;
                // }

                if (currency === 'current') continue;

                let amount = data[currency];
                // let r = Math.floor(Math.round((amount - Math.floor(amount))*100));
                let r = amount.toString().split(".")[1] ? amount.toString().split(".")[1].padEnd(2, '0') : '00';

                let mainItems = document.querySelectorAll('.wallet-selector .currency-icon_' + currency.toLowerCase());
                for (let mainItem of mainItems) {
                    let mainInt = mainItem.parentElement.querySelector('.wallet-selector__amount > .wallet-selector__amount-int');
                    let mainRest = mainItem.parentElement.querySelector('.wallet-selector__amount > .wallet-selector__amount-rest');

                    mainInt.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                    // mainRest.textContent = r.toString().padEnd(2, '0');
                    mainRest.textContent = r;
                }

                let items = document.querySelectorAll('.wallet-selector__item_' + currency.toLowerCase());
                for (let item of items) {
                    let int = item.querySelector('.wallet-selector__amount-int');
                    let rest = item.querySelector('.wallet-selector__amount-rest');

                    int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                    // rest.textContent = r.toString().padEnd(2, '0');
                    rest.textContent = r;

                    if (item.classList.contains('wallet-selector__item_active')) {
                        int = item.parentNode.parentNode.querySelector('.wallet-selector__amount-int');
                        rest = item.parentNode.parentNode.querySelector('.wallet-selector__amount-rest');

                        int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                        // rest.textContent = r.toString().padEnd(2, '0');
                        rest.textContent = r;
                    }
                }
            }

            // update bonus balance values
            if (data['bonus_balance'] !== undefined) {
                let amount = data['bonus_balance']['value'];
                let currency = data['bonus_balance']['currency'];
                let r = amount.toString().split(".")[1] ? amount.toString().split(".")[1].padEnd(2, '0') : '00';
                let items = document.querySelectorAll('.wallet-bonus-balance .wallet-selector__item_' + currency.toLowerCase());
                for (let item of items) {
                    let int = item.querySelector('.wallet-selector__amount-int');
                    let rest = item.querySelector('.wallet-selector__amount-rest');

                    int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                    rest.textContent = r;

                    if (item.classList.contains('wallet-selector__item_active')) {
                        int = item.parentNode.parentNode.querySelector('.wallet-selector__amount-int');
                        rest = item.parentNode.parentNode.querySelector('.wallet-selector__amount-rest');

                        int.textContent = Math.floor(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + '.';
                        rest.textContent = r;
                    }
                }
            }
        }
    }

    function waitUntilReady() {
        return new Promise(resolve => {
            if (ready) resolve();
            let ti = setInterval(() => {
                if (ready) {
                    clearInterval(ti);
                    resolve();
                }
            }, 100);
        });
    }


    // Payment modal.
    document.addEventListener('click:wallet-selector__plus-button', async e => {
        document.dispatchEvent(new CustomEvent("showWallet"));
    });

    // Payment modal.
    document.addEventListener('showDeposit', async e => {
        await waitUntilReady();

        let walletWrap = document.querySelector(".wallet-wrapper");
        walletWrap.classList.add('shown');
        document.body.classList.add('_lock');

        let wallet = document.getElementById('user-wallet-pad');

        Spinner.show(wallet);
        let url = '/wallet/deposit';
        let urlParams = new URLSearchParams();
        if (e.detail !== null && e.detail.group !== undefined) {
            urlParams.append('group_id', e.detail.group);
        }
        if (e.detail !== null && e.detail.deposit !== undefined) {
            urlParams.append('deposit', e.detail.deposit);
        }
        if (e.detail !== null && e.detail.without_bonus !== undefined) {
            urlParams.append('without_bonus', e.detail.without_bonus);
        }
        if (urlParams.size > 0) {
            url += '?' + urlParams.toString();
        }

        let res = await network.get(url);
        let html = await res.text();
        setInnerHtml(document.getElementById('wallet-content'), html);

        if (e.detail !== null && e.detail.deposit !== null) {
            let depositField = document.querySelector('#wallet__dep-amount');
            if (depositField !== null) depositField.value = parseFloat(e.detail.deposit);
        }

        Spinner.hide(wallet);

        // Modal.updatePosition(document.querySelector('.modal-wrap.shown'));
    });

    // Payout modal.
    document.addEventListener('showPayout', async e => {
        await waitUntilReady();

        let walletWrap = document.querySelector(".wallet-wrapper");
        walletWrap.classList.add('shown');
        document.body.classList.add('_lock');

        let wallet = document.getElementById('user-wallet-pad');
        // wallet.querySelector('.withraw-btn').classList.add('active');
        // wallet.querySelector('.deposit-btn').classList.remove('active');
        // document.dispatchEvent(new CustomEvent("hideWallet"));

        // let modal = Modal.show('<div style="width: 25rem; max-width: 100%; height: 30rem;background-color: #00000080;border-radius: 1rem;"></div>');
        Spinner.show(wallet);
        let url = '/wallet/payout';
        if (e.detail !== null && e.detail.group !== undefined) {
            url += '?group_id=' + e.detail.group
        }

        let res = await network.get(url);
        let html = await res.text();
        setInnerHtml(document.getElementById('wallet-content'), html);

        if (e.detail !== null && e.detail.deposit !== null) {
            let depositField = document.querySelector('#wallet__dep-amount');
            if (depositField !== null) depositField.value = parseFloat(e.detail.deposit);
        }

        Spinner.hide(wallet);
    });

    document.addEventListener('showBuyCrypto', async e => {
        await waitUntilReady();
        document.dispatchEvent(new CustomEvent("hideWallet"));

        Modal.show('<div style="width: 25rem; max-width: 100%; height: 30rem;background-color: #00000080;border-radius: 1rem;"></div>');
        Spinner.show('.modal .content');

        let res = await network.get('/wallet/buyCrypto');
        let html = await res.text();
        setInnerHtml('.modal .content', html);
        Modal.updatePosition(document.querySelector('.modal-wrap.shown'))
    });

    //user wallet
    document.addEventListener('toggleWallet', async e => {
        // let walletBtn = document.querySelector("#header .header__deposit-btn");
        // let walletWrap = document.querySelector(".wallet-wrapper");
        // if (walletBtn.classList.contains('shown')) {
        //     document.dispatchEvent(new CustomEvent("hideWallet"));
        // } else {
        //     document.dispatchEvent(new CustomEvent("showWallet"));
        // }
    });

    document.addEventListener('showWallet', async e => {
        let walletWrap = document.querySelector(".wallet-wrapper");
        walletWrap.classList.add('shown');
        document.body.classList.add('_lock');
        document.dispatchEvent(new CustomEvent("showDeposit"));
    });

    document.addEventListener('hideWallet', async e => {
        let walletBtn = document.querySelector("#header .header__deposit-btn");
        let walletWrap = document.querySelector(".wallet-wrapper");
        walletWrap.classList.remove('shown');
        if (walletBtn) walletBtn.classList.remove('shown');
        document.body.classList.remove('_lock');
    });

    //add wallet (set active)
    document.addEventListener('click:button_add_currency', async e => {
        await waitUntilReady();
        let modal = Modal.show('<div style="width: 25rem; height: 39rem;background-color: #fff;border-radius: 1rem;"></div>');
        Spinner.show('.modal .content');
        let res = await network.get('/wallet/add');
        let html = await res.text();
        setInnerHtml('.modal .content', html);
        Modal.updatePosition(modal);
    });

    //del wallet (deactivate)
    document.addEventListener('click:button_del_currency', async e => {
        // await waitUntilReady();
        let t = e.detail.target;
        let modal = Modal.show('<div style="width: 25rem; height: 39rem;background-color: #fff;border-radius: 1rem;"></div>');
        Spinner.show('.modal .content');
        let res = await network.get('/wallet/del/' + t.dataset.id);
        let html = await res.text();
        setInnerHtml('.modal .content', html);
        Modal.updatePosition(modal);
    });

    document.addEventListener('click:wallet-selector', async e => {
        let menu = e.detail.originalEvent.target.closest('.user-menu');
        if (menu !== null) {
            menu.classList.add('click-shown');
        }
        e.detail.target.classList.toggle('click-shown');
        e.detail.originalEvent.processed = 'wallet-selector';
    });

    window.wallet = {
        show: () => {
            document.dispatchEvent(new CustomEvent("showWallet"));
        },
        hide: () => {
            document.dispatchEvent(new CustomEvent("hideWallet"));
        },
        showDeposit: (amount = 0, group = null, withoutBonus = false) => {
            let detail = {};
            if (amount > 0) detail.deposit = amount;
            if (group !== null) detail.group = group;
            detail.without_bonus = withoutBonus === true;
            document.dispatchEvent(new CustomEvent("showDeposit", {detail: detail}));
        },
        showPayout: (group = null) => {
            let detail = {};
            if (group !== null) detail.group = group;
            document.dispatchEvent(new CustomEvent("showPayout", {detail: detail}));
        },
    };
})();
