document.addEventListener('click:cancel-payout-button', async e => {
    e.detail.originalEvent.preventDefault();
    let t = e.detail.target;
    let row = t.parentNode.parentNode;


    try {
        await ConfirmModal.show(t.dataset.confirm);
        let res = await network.post(t.getAttribute('href'), {id: t.dataset.id});
        let html = await res.text();
        if (document.querySelector('.bottom-modal .payout-success-modal') !== null) Modal.hide('.bottom-modal .payout-success-modal');
        if (document.querySelector('.modal .payout-success-modal') !== null) Modal.hide('.modal .payout-success-modal');
        if (document.querySelector(".wallet-wrapper.shown") !== null) document.dispatchEvent(new CustomEvent("showWallet"));
        setInnerHtml('.cabinet__content', html);
    } catch (errorMsg) {
        if (document.querySelector(".wallet-wrapper.shown") !== null) document.dispatchEvent(new CustomEvent("showWallet"));
    }
});
