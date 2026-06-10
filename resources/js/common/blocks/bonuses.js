(() => {
    async function bonusLinkHandler(e) {
        e.detail.originalEvent.preventDefault();
        let btn = e.detail.target;
        let url = btn.getAttribute('href');

        Spinner.show('#layout-content');
        try {
            let res = await network.get(url);
            let html = await res.text();

            setInnerHtml('#layout-content', html);
            scrollTop();
        } catch (errorMsg) {
            console.error(errorMsg);
        }
        Spinner.hide('#layout-content');
    }


    // document.addEventListener('click:bonuses__item-button', bonusLinkHandler);
    // document.addEventListener('click:bonuses__item-image-wrap', bonusLinkHandler);

    document.addEventListener('click:use-promocode-btn', async e => {
        let form = document.querySelector('.cabinet__code-form-wrap');
        form.style.opacity = '0';
        form.style.display = 'flex';
        form.querySelector('input[type="text"]').value = '';
        let anim = new Anim(250);
        await anim.start(k => {
            form.style.opacity = k.toString();
        });
    });

    document.addEventListener('click:cabinet__code-form-close', async e => {
        let form = document.querySelector('.cabinet__code-form-wrap');
        let anim = new Anim(250);
        await anim.start(k => {
            form.style.opacity = (1-k).toString();
        });
        form.style.display = 'none ';
    });

    let bonusContent = null;

    let lastClickTime = Date.now()
    document.addEventListener('click:cabinet__bonus-info-btn', async e => {
        if (Date.now() - lastClickTime < 1000) return;
        lastClickTime = Date.now();
        let t = e.detail.target;
        if(t.dataset.bonusId === undefined || t.dataset.bonusId === null || t.dataset.bonusId === '') return;

        if (bonusContent === null) {
            bonusContent = document.getElementById('bonus-info-modal');
        }

        try {
            let resp = await network.get('/bonuses/' + t.dataset.bonusId + '?in_modal=true&bonusId=' + t.dataset.id);
            bonusContent.innerHTML = await resp.text();

            bonusContent.style.display = 'flex';

            let modal = Modal.show(bonusContent);
            modal.querySelector('.close-button').style.backgroundColor = 'transparent';
        } catch (e) {
            console.log(e.toString())
        }
    });

    document.addEventListener('click:bonus-buy-btn', async e => {
        if (Date.now() - lastClickTime < 1000) return;
        lastClickTime = Date.now();
        const btn = e.detail.target;
        const url = btn.getAttribute('href');
        const dialog = btn.closest('.bonus-buy-form');
        if (dialog === null) return;
        await Spinner.show(dialog);
        try {
            await network.post(url, {bonus_id: btn.dataset.id});
            await updateCabinetBonuses();
            await Spinner.hide(dialog);
            Modal.hide(btn);
        } catch(e) {
            await Spinner.hide(dialog, e.toString());
        }
    });

})();


