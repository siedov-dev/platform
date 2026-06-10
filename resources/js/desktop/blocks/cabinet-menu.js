(() => {
    document.addEventListener('click:cabinet__menu-link', async e => {
        e.detail.originalEvent.preventDefault();

        let t = e.detail.target;
        let url = t.getAttribute('href');

        Overlay.setTitle(t.textContent);

        let cabinetContent = Overlay.getContentNode().querySelector('.cabinet__content');
        let cabinetContentWrap = Overlay.getContentNode().querySelector('.cabinet__content-wrap');

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

    document.addEventListener('click:user-menu', async e => {
        let menu = e.detail.target;
        if (e.detail.originalEvent.target.closest('.wallet-selector') === null) {
            if (e.detail.originalEvent.target.closest('.user__menu-links') !== null){
                menu.classList.remove('click-shown');
            } else {
                menu.classList.toggle('click-shown');
            }
        } else {
            menu.classList.add('click-shown');
        }
        e.detail.originalEvent.processed = 'user-menu';
    });

})();
