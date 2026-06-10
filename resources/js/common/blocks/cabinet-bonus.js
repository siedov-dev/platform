(() => {
    // Bonus modal.
    document.addEventListener('click:cabinet__bonus-menu-btn', e => {
        let btn = e.detail.target;
        let content = btn.nextSibling;
        if (content.nodeType === Node.TEXT_NODE) content = content.nextSibling;
        Modal.show(content.innerHTML);
        Modal.hide(btn);
    });


    document.addEventListener('click:cabinet__bonus-menu-item', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;
        let deposit = btn.getAttribute('data-deposit');

        Spinner.show('.modal .content');

        try {
            let res = await network.post(url, {deposit: deposit});
            let showDep = res.headers.get('show_deposit');
            let showModal = res.headers.get('showModal') !== null;
            let msg = await res.text();
            if (showModal) {
                updateCabinetBonuses();
                Spinner.hide('.modal .content');
                Modal.show(msg, true);
                return;
            }

            await updateCabinetBonuses();
            await Spinner.hide('.modal .content', msg);

            Modal.hide(btn);
            // Open deposit modal.
            if (showDep !== null) {
                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent("showDeposit", {detail: {deposit: deposit} }));
                }, 0);
            }

        } catch (msg) {
            Spinner.hide('.modal .content', msg);
        }
    });

    document.addEventListener('click:activate-bonus_and_show-bonuses', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;
        let deposit = btn.getAttribute('data-deposit');

        await cabinetShowBonuses();

        try {
            let res = await network.post(url, {deposit: deposit});
            let showDep = res.headers.get('show_deposit');
            let showModal = res.headers.get('showModal') !== null;
            let msg = await res.text();

            if (showModal) {
                Modal.show(msg, true);
                updateCabinetBonuses();
                return;
            }

            await updateCabinetBonuses();
            // Open deposit modal.
            if (showDep !== null) {
                setTimeout(() => {
                    document.dispatchEvent(new CustomEvent("showDeposit", {detail: {deposit: deposit} }));
                }, 0);
            }

            if (btn.closest('.bonus-activate-dialog') !== null) {
                Modal.hide(btn.closest('.bonus-activate-dialog'));
            }

        } catch (msg) {
            console.log(msg);
        }
    });

    document.addEventListener('click:cabinet__bonus-activate-btn', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;
        let deposit = btn.getAttribute('data-deposit');

        let wrap = btn.parentNode.parentNode.parentNode;

        Spinner.show(wrap);

        try {
            let res = await network.post(url);
            let showDep = res.headers.get('show_deposit');
            let showModal = res.headers.get('showModal') !== null;
            let msg = await res.text();
            if (showModal) {
                updateCabinetBonuses();
                Spinner.hide(wrap);
                Modal.show(msg, true);
                return;
            }
            if (res.ok) await updateCabinetBonuses();

            // Open deposit modal.
            if (showDep !== null) {
                document.dispatchEvent(new CustomEvent("showDeposit", {detail: {deposit: deposit} }));
            }

            await Spinner.hide(wrap, msg);
            if (btn.closest('.bonus-activate-dialog') !== null) {
                Modal.hide(btn.closest('.bonus-activate-dialog'));
            }
        } catch (msg) {
            Spinner.hide(wrap, msg);
        }
    });


    document.addEventListener('click:cabinet__bonus-take-btn', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;
        let deposit = btn.getAttribute('data-deposit');

        let wrap = btn.parentNode.parentNode.parentNode;

        Spinner.show(wrap);

        try {
            let res = await network.post(url);
            let showDep = res.headers.get('show_deposit');
            let msg = await res.text();
            // Open deposit modal.
            if (showDep !== null) {
                document.dispatchEvent(new CustomEvent("showDeposit", {detail: {deposit: deposit} }));
            }
            await updateCabinetBonuses();

            await Spinner.hide(wrap, msg);
        } catch (msg) {
            Spinner.hide(wrap, msg);
        }
    });

    document.addEventListener('click:bonus-info-action-btn', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;
        let wrap = btn.parentNode.parentNode;

        let question = btn.dataset.question;
        ConfirmModal.show(question, "OK", __('cancel'))
            .then(async () => {
                Spinner.show(wrap);

                try {
                    let res = await network.post(url);

                    await updateCabinetBonuses();
                    await Spinner.hide(wrap);

                    Modal.hide(btn);
                } catch (msg) {
                    Spinner.hide(wrap, msg);
                }
            })
            .catch();
    });

    document.addEventListener('click:cabinet__event-activate-btn', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;

        let wrap = btn.closest('.cabinet__bonuses-item');
        if (wrap === null) wrap = btn.closest('.popup-modal .content');
        if (wrap === null) wrap = btn.closest('.modal');

        if (wrap !== null) await Spinner.show(wrap);

        try {
            let user_data_el = wrap.querySelector('.input__wrap input');
            let data = {};
            if (user_data_el){
                let user_data = 0 + user_data_el.value;
                data = {
                    user_data: user_data,
                };
            }

            let res = await network.post(url, data);
            let msg = await res.text();

            await updateCabinetBonuses();
            await Spinner.hide(wrap, msg);

            Modal.hide(btn);
        } catch (msg) {
            Spinner.hide(wrap, msg);
        }
    });

    document.addEventListener('click:bonus-drop-btn', async e => {
        let btn = e.detail.target;
        let url = btn.dataset.url;

        let wrap = btn.closest('.cabinet__bonuses-item');
        if (wrap === null) wrap = btn.closest('.popup-modal .content');
        if (wrap === null) wrap = btn.closest('.modal');

        if (wrap !== null) await Spinner.show(wrap);

        try {
            let user_data_el = wrap.querySelector('.input__wrap input');
            let data = {};
            if (user_data_el){
                let user_data = 0 + user_data_el.value;
                data = {
                    user_data: user_data,
                };
            }

            let res = await network.post(url, data);
            let msg = await res.text();

            await updateCabinetBonuses();
            Spinner.hide(wrap, msg);

            Modal.hide(btn);
        } catch (msg) {
            await Spinner.hide(wrap, msg);
        }
    });
})();

function isCabinetBonusSectionShown() {
    const cabPositon = document.getElementById("cabinet-position");
    if (cabPositon === null) return false;
    return cabPositon.dataset.position === "bonuses";
}

async function updateCabinetBonuses() {
    if (!isCabinetBonusSectionShown()) return;
    let res = await network.get('/cabinet/bonuses');
    let html = await res.text();
    setInnerHtml('.cabinet__content', html);
}

// update cabinet bonus section when bonus state changes
document.addEventListener('bonus:changed', async e => {
    await updateCabinetBonuses();
});
