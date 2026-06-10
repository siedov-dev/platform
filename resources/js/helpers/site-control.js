/**
 *
 * @param showUsePromo show have promocode modal
 * @param hideHaveBonuses
 * @returns {Promise<void>}
 */
async function cabinetShowBonuses(showUsePromo = false, hideHaveBonuses = false) {
    let url = "/cabinet/bonuses";
    Overlay.setTitle('Bonuses');

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
        if (hideHaveBonuses) {
            url += '?nomodals=1';
        }
    } else {
        url += '?layout=1';
        if (hideHaveBonuses) {
            url += '&nomodals=1';
        }
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

            //select cabinet menu
            for (let menu of document.querySelectorAll('.user-menu__link')) menu.classList.remove('active');
            let cabinetPosEl =  document.getElementById('cabinet-position');
            if (cabinetPosEl !== null) {
                let position = cabinetPosEl.dataset.position;
                let menuLink = document.querySelector(".user-menu__link."+position+"-link");
                if (menuLink !== null) {
                    menuLink.classList.add('active')
                    Overlay.setTitle(menuLink.textContent);
                }
                menuLink = document.querySelector(".user__menu-link."+position+"-link");
                if (menuLink !== null) {
                    menuLink.classList.add('active')
                    Overlay.setTitle(menuLink.textContent);
                }
                menuLink = document.querySelector(".cabinet__menu-link."+position+"-link");
                if (menuLink !== null) {
                    menuLink.classList.add('active')
                    Overlay.setTitle(menuLink.textContent);
                }
            }

            if (showUsePromo) {
                let form = document.querySelector('.cabinet__code-form-wrap');
                form.style.opacity = '0';
                form.style.display = 'flex';
                form.querySelector('input[type="text"]').value = '';
                form.style.opacity = 1;
            }

            Overlay.hideSpinner();
        }
    } catch (e) {
        Overlay.hideSpinner();
        Overlay.hide();
    }
}

async function cabinetShowBlock(blockName, hideHaveBonuses) {
    const loginButton = document.getElementById('login-button');
    if (loginButton !== null) {
        loginButton.click();
        return;
    }
    let url = "/cabinet/" + blockName;

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
        if (hideHaveBonuses) {
            url += '?nomodals=1';
        }
    } else {
        url += '?layout=1';
        if (hideHaveBonuses) {
            url += '&nomodals=1';
        }
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
        for (let menu of document.querySelectorAll('.user-menu__link')) menu.classList.remove('active');
        let cabinetPosEl =  document.getElementById('cabinet-position');
        if (cabinetPosEl !== null) {
            let position = cabinetPosEl.dataset.position;
            let menuLink = document.querySelector(".user-menu__link."+position+"-link");
            if (menuLink !== null) {
                menuLink.classList.add('active')
                Overlay.setTitle(menuLink.textContent);
            }
            menuLink = document.querySelector(".user__menu-link."+position+"-link");
            if (menuLink !== null) {
                menuLink.classList.add('active')
                Overlay.setTitle(menuLink.textContent);
            }
            menuLink = document.querySelector(".cabinet__menu-link."+position+"-link");
            if (menuLink !== null) {
                menuLink.classList.add('active')
                Overlay.setTitle(menuLink.textContent);
            }
        }
    } catch (e) {
        Overlay.hideSpinner();
        Overlay.hide();
    }
}

async function setLogoRandomColor()
{
    let colorIndex = Math.floor(Math.random() * 10) + 1;
    setLogoColor(colorIndex);
}

async function setLogoColor(colorIndex)
{
    let logoImg = document.querySelector('.logo__image-wrapper img');
    if (logoImg !== null) {
        for (let c = 1; c < 11; c++) logoImg.classList.remove('num-'+c);
        logoImg.classList.add('num-' + colorIndex);
    }
}

document.addEventListener('action', async (e) => {
    const action = e.detail.action;

    // All actions below require auth.
    // Modify if new action doesn't!
    let loginBtn = document.getElementById('login-button');
    if (loginBtn !== null) {
        loginBtn.click();
        return;
    }

    if (action === '/action/deposit') {
        wallet.showDeposit();
    } else if (action === '/action/payout') {
        wallet.showPayout();
    } else if (action === '/action/cashback') {
        let res = await network.get('/cashback');
        let text = await res.text();
        if (window.PopupModal.showContent !== undefined) {
            PopupModal.showContent(text);
        } else {
            Modal.show(text)
        }
    } else if (action === '/action/wheel') {
        let res = await network.get('/wheel');
        let text = await res.text();
        if (window.PopupModal.showContent !== undefined) {
            PopupModal.showContent(text);
        } else {
            Modal.show(text)
        }
    } else if (action === '/action/slotmachine') {
        let res = await network.get('/slotmachine');
        let text = await res.text();
        let modal = Modal.show(text, false)
        let slcPopup = modal.querySelector('.popup');
        if (slcPopup !== null) {
            modal.querySelector('.popup').style.visibility = "visible";
            modal.querySelector('.popup').style.opacity = "1";
        }
    }
});
