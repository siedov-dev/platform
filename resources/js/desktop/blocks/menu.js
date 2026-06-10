(() => {
    let content = document.getElementById('layout-content');
    let anim = new Anim(500);


    document.addEventListener('click:menu__item', async e => {
        if (e.detail.target.classList.contains('no-ajax')) return;
        e.detail.originalEvent.preventDefault();

        showPreloader(0, 3, false);

        let t = e.detail.target;
        let url = t.getAttribute('href');

        await Overlay.hide();
        scrollTop();
        Spinner.show('#layout-content-wrap');

        let promise = anim.start(k => content.style.opacity = (1 - k*0.9).toFixed(2));

        try {
            let headerEl = document.getElementById('header');
            if (headerEl !== null) {
                headerEl.classList.remove('sport');
                headerEl.classList.remove('tv-bet');
            }
            document.querySelector('body').classList.remove('sport');
            document.querySelector('body').classList.remove('tv-bet');

            let res = await network.get(url);
            let text = await res.text();
            history.pushState({}, t.textContent, url);
            await Promise.all([promise]);
            setInnerHtml(content, text);
        } catch (errorMsg) {

        }

        Spinner.hide('#layout-content-wrap');
        completePreloader();

        anim.start(k => content.style.opacity = k.toFixed(2));
    });

})();


document.addEventListener('showNavigation', () => {
    let menuDialog = document.querySelector('.header-modals-wrapper .navigation-menu');
    if (menuDialog !== null) menuDialog.classList.add('shown');
    document.body.classList.add('_lock');
});

document.addEventListener('showGamesMenu', () => {
    document.dispatchEvent(new Event('updateGamesMenu'))
    let menuDialog = document.querySelector('.header-modals-wrapper .games-menu');
    if (menuDialog !== null) menuDialog.classList.add('shown');
    document.body.classList.add('_lock');
});

document.addEventListener('showSupportMenu', async () => {
    let menuDialog = document.querySelector('.header-modals-wrapper .support-menu');
    try {
        let resp = await network.get("/callRequest");
        menuDialog.querySelector('.user-call-content').innerHTML = await resp.text();
    } catch (e) {
        console.log(e.toString());
    }
    if (menuDialog !== null) menuDialog.classList.add('shown');
    document.body.classList.add('_lock');
});

document.addEventListener('click:action-user-call-request', async (e) => {
    let t = e.detail.target;
    let menuDialog = document.querySelector('.header-modals-wrapper .support-menu');
    try {
        let resp = await network.post("/callRequest/makeRequest");
        menuDialog.querySelector('.user-call-content').innerHTML = await resp.text();
    } catch (e) {
        console.log(e.toString());
    }
    if (menuDialog !== null) menuDialog.classList.add('shown');
    document.body.classList.add('_lock');
});

document.addEventListener('showSearchMenu', () => {
    let menuDialog = document.querySelector('.header-modals-wrapper .search-menu');
    if (menuDialog !== null) {
        menuDialog.classList.add('shown');
        if (menuDialog.querySelector('.games').children.length === 0) document.dispatchEvent(new CustomEvent("games:searchGame"));
        document.body.classList.add('_lock');
    }
});

document.addEventListener('click:header-menu__close-button', (e) => {
    let t = e.detail.target;
    let menuDialog = t.closest('.bottom-modal');
    if (menuDialog !== null) menuDialog.classList.remove('shown');
    document.body.classList.remove('_lock');
});

document.addEventListener('click:header-modal', (e) => {
    let t = e.detail.originalEvent.target;
    if (t.closest('.no-click-hide') === null) {
        let wrap = t.closest('.header-modal');
        if (wrap !== null) {
            wrap.classList.remove('shown');
            document.body.classList.remove('_lock');
        }
    }
    // document.querySelectorAll('.header-modals-wrapper .header-modal').forEach(el => el.classList.remove('shown'));
});


document.addEventListener('updateGamesMenu', async (e) => {
    if (refreshingGamesMenu === true) return;
    refreshingGamesMenu = true;
    try {
        let resp = await network.post('/menu/updateGames');
        let data = await resp.json();
        let bonusButton = document.querySelector('.header-modals__games-nav .menu__item.bonus-games');
        let tournamentButton = document.querySelector('.header-modals__games-nav .menu__item.tournament-games');
        let newGamesButton = document.querySelector('.header-modals__games-nav .menu__item.new-games');

        if (data['active_bonus']) {
            bonusButton.classList.add('notify');
        } else {
            bonusButton.classList.remove('notify');
        }

        if (data['active_tournaments']) {
            tournamentButton.classList.add('notify');
        } else {
            tournamentButton.classList.remove('notify');
        }

        if (data['new_games']) {
            newGamesButton.classList.add('notify');
        } else {
            newGamesButton.classList.remove('notify');
        }
    } catch (e) {
        console.log(e.toString());
    }

    refreshingGamesMenu = false;
});

document.addEventListener('DOMContentLoaded', () => {
        document.dispatchEvent(new Event('updateGamesMenu'));
});
setInterval(() => {
    document.dispatchEvent(new Event('updateGamesMenu'));
}, 10000);
