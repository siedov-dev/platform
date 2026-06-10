(() => {
    let counter = 0;
    let taskId = 0;

    document.addEventListener('click:user__menu-link', async e => {
        e.detail.originalEvent.preventDefault();

        let myTaskId = counter++;
        taskId = myTaskId;

        let t = e.detail.target;
        let url = t.getAttribute('href');

        let cabinetContent = Overlay.getContentNode().querySelector('.cabinet__content');
        let cabinetContentWrap = Overlay.getContentNode().querySelector('.cabinet__content-wrap');


        Overlay.setTitle(t.textContent);

        if (Overlay.isShown()) {
            let anim = new Anim(500);
            let s = document.body.scrollTop;
            anim.start(k => document.body.scrollTop = s - (s*k));
        } else {
            Overlay.getContentNode().innerHTML = '';
        }

        if (cabinetContent !== null) {
            Spinner.show(cabinetContentWrap);
        } else {
            url += '?layout=1';
            Overlay.showSpinner();
        }
        Overlay.show();

        try {
            let res = await network.get(url);
            if (taskId !== myTaskId) return;

            let html = await res.text();

            if (cabinetContent !== null) {
                setInnerHtml(cabinetContent, html);
                Spinner.hide(cabinetContentWrap);
            } else {
                let d = document.createElement('d');
                d.innerHTML = html;

                let c = d.getElementsByClassName('cabinet');
                if (c.length > 0) {
                    setInnerHtml(Overlay.getContentNode(), c[0].outerHTML);
                }
                Overlay.hideSpinner();
            }
            //select cabinet menu
            for (let menu of document.querySelectorAll('.user__menu-link')) menu.classList.remove('active');
            for (let menu of document.querySelectorAll('.cabinet__menu-link')) menu.classList.remove('active');
            let cabinetPosEl =  document.getElementById('cabinet-position');
            if (cabinetPosEl !== null) {
                let position = cabinetPosEl.dataset.position;
                let menuLink = document.querySelector(".user__menu-link."+position+"-link");
                if (menuLink !== null) menuLink.classList.add('active')
                let cabinetLink = document.querySelector(".cabinet__menu-link."+position+"-link");
                if (cabinetLink !== null) cabinetLink.classList.add('active')
            }
        } catch (e) {
            Overlay.hideSpinner();
            Overlay.hide();
        }
    });

    document.addEventListener('click:cabinet__acc-vrf-link', async e => {
        let settings = document.querySelector('#user-menu-settings');if(settings) settings.click();
    });

    document.addEventListener('click', async e => {
        if (e.processed !== undefined && e.processed !== null && e.processed === true) return;
        for (let el of document.querySelectorAll('.click-shown')) {
            if (e.processed !== undefined && e.processed !== null && (typeof e.processed === 'string' || e.processed instanceof String)) {
                if (el.classList.contains(e.processed) || el.closest('.' + e.processed) !== null) continue;
            }
            el.classList.remove('click-shown');
        }
    });

    document.addEventListener('click:notifications', async e => {
        let notifications = e.detail.target;
        notifications.classList.toggle('click-shown');
        e.detail.originalEvent.processed = 'notifications';
    });

})();

