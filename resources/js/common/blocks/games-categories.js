(() => {

    document.addEventListener('click:lnd-categories__item', async e => {
        let t = e.detail.target;
        let url = t.dataset.url;

        let active = t.parentNode.querySelector('.lnd-categories__item_active');
        if (active !== null) active.classList.remove('lnd-categories__item_active');
        t.classList.add('lnd-categories__item_active');

        try {
            let res = await network.get(url);
            document.querySelector('#landing-games').innerHTML = await res.text();
            let searchedGames = document.querySelector('.landing-search #games__items');
            if (searchedGames !== null) {
                for (let el of searchedGames.querySelectorAll('*')) {
                    el.remove();
                }
            }
            let moreGames = document.querySelector('.landing-search #games__show-more-wrap');
            if (moreGames !== null) {
                for (let el of moreGames.querySelectorAll('*')) {
                    el.remove();
                }
            }
            scrollToElement('.games-content');
        } catch (errorMsg) {
            console.error(errorMsg);
        }
    });

    let loading = false;

    document.addEventListener('click:lnd__show-more', async e => {
        if (loading) return;
        loading = true;
        let btn = e.detail.target;

        let active = document.querySelector('.lnd-categories__item_active');
        if (active === null) return;
        let url = active.dataset.url;

        try {
            let res = await network.get(url + '&page=' + (parseInt(btn.dataset.page) + 1));
            if (res.ok) {
                let html = await res.text();
                let div = document.createElement('div');
                div.innerHTML = html;

                let items = div.querySelector('.games-slider__games');
                if (items !== null) {
                    let i = btn.parentNode.parentNode.parentNode.querySelector('.games-slider__games');
                    i.innerHTML = i.innerHTML + items.innerHTML;
                }

                let b = div.querySelector('.show-more-wrap');
                if (b !== null) {
                    btn.parentNode.parentNode.innerHTML = b.innerHTML;
                }
            }
        } catch (errorMsg) {
            console.error(errorMsg);
        }
        loading = false;
    });

})();



