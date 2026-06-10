(
    () => {
        document.addEventListener('click:tournament__banner-button', async e => {
            let btn = e.detail.target;
            let url = btn.dataset.url;

            Spinner.show('.tournament__banner');

            try {
                let res = await network.post(url);
                let msg = await res.text();
                Spinner.hide('.tournament__banner');
                let lt = document.querySelector('#layout-content');
                lt.innerHTML = msg;
            } catch (msg) {
                Spinner.hide('.tournament__banner');
                Modal.show(`<div style="width: 28rem; padding: 20px; background-color: #dddddda0;border-radius: 1rem;font-size: 22px; font-weight: 800; color: black; text-align: center">${msg}</div>`, false);
                setTimeout(() => {Modal.hide('.modal .content')}, 1800);
            }
        });

        document.addEventListener('click:tnt-info-button', async e => {
            let btn = e.detail.target;
            document.location = btn.dataset.url;
            return;
            let url = btn.dataset.url;
            Overlay.showSpinner();

            try {
                let res = await network.get(url);
                let msg = await res.text();
                let lt = document.querySelector('#layout-content');
                lt.innerHTML = msg;
                Overlay.hideSpinner();
                Overlay.hide();
            } catch (msg) {
                Overlay.hideSpinner();
                Modal.show(`<div style="width: 28rem; padding: 20px; background-color: #dddddda0;border-radius: 1rem;font-size: 22px; font-weight: 800; color: black; text-align: center">${msg}</div>`, false);
                setTimeout(() => {Modal.hide('.modal .content')}, 1800);
            }
        });
    }
)();
