(() => {
    document.addEventListener('click:cabinet__prtn-submit-btn', async e => {
        let t = e.detail.target;
        let url = t.dataset.url;

        Spinner.show(t.parentNode);

        try {
            let res = await network.post(url);
            let msg = await res.text();
            Spinner.hide(t.parentNode, msg, 700);
            setTimeout(() => {
                location.href = '/cabinet/partnership';
            }, 700);
        } catch (errorMsg) {
            Spinner.hide(t.parentNode, errorMsg, null, 3000);
        }
    });
})();
