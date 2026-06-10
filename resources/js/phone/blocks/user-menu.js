(() => {
    let counter = 0;
    let taskId = 0;

    document.addEventListener('click:user-menu__link', async e => {
        e.detail.originalEvent.preventDefault();

        let myTaskId = counter++;
        taskId = myTaskId;

        let t = e.detail.target;
        let url = t.getAttribute('href');

        let cabinetContent = Overlay.getContentNode().querySelector('.cabinet__content');
        let cabinetContentWrap = Overlay.getContentNode().querySelector('.cabinet__content-wrap');


        Overlay.setTitle(t.textContent);

        // Hide menu.
        document.dispatchEvent(new CustomEvent('hideRightSidebar'));

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
            for (let menu of document.querySelectorAll('.user-menu__link')) menu.classList.remove('active');
            let cabinetPosEl =  document.getElementById('cabinet-position');
            if (cabinetPosEl !== null) {
                let position = cabinetPosEl.dataset.position;
                let menuLink = document.querySelector(".user-menu__link."+position+"-link");
                if (menuLink !== null) menuLink.classList.add('active')
            }
        } catch (e) {
            Overlay.hideSpinner();
            Overlay.hide();
        }


    });

    document.addEventListener('click:user-cabinet__link', async e => {
        e.detail.originalEvent.preventDefault();

        let myTaskId = counter++;
        taskId = myTaskId;

        let t = e.detail.target;
        let url = t.getAttribute('href');

        let cabinetContent = Overlay.getContentNode().querySelector('.cabinet__content');
        let cabinetContentWrap = Overlay.getContentNode().querySelector('.cabinet__content-wrap');


        Overlay.setTitle(t.dataset.title);

        // Hide menu.
        document.dispatchEvent(new CustomEvent('hideRightSidebar'));

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
            for (let menu of document.querySelectorAll('.user-menu__link')) menu.classList.remove('active');
            let cabinetPosEl =  document.getElementById('cabinet-position');
            if (cabinetPosEl !== null) {
                let position = cabinetPosEl.dataset.position;
                let menuLink = document.querySelector(".user-menu__link."+position+"-link");
                if (menuLink !== null) menuLink.classList.add('active')
            }
        } catch (e) {
            Overlay.hideSpinner();
            Overlay.hide();
        }

    });

    document.addEventListener('click:cabinet__acc-vrf-link', async e => {
        let settings = document.querySelector('#user-menu-settings');if(settings) settings.click();
    });

})();
