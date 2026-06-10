(() => {

    // Searching
    let task = null;
    let ready = true;
    let searching = false;
    let page = 0;
    let searchPage = 0;
    let lastUrl = document.location.href;
    let cancel = false;

    async function shadeContent() {
        scrollTop();
        Spinner.show('#layout-content-wrap');
        anim.start(k => content.style.opacity = (1 - k * 0.9).toFixed(2));
    }


    async function removeContentShading() {
        Spinner.hide('#layout-content-wrap');
        anim.stop();
        await anim.start(k => content.style.opacity = k);
    }

    async function setLoadingImg() {
        scrollTop();
        // document.getElementById('layout-content').innerHTML = "<div class='loader-img'><img src='/img/loader.svg'></div>";
    }

    document.addEventListener('games:search', () => {
        page = 0;
        getGames();
    });

    document.addEventListener('games:searchWithDelay', () => {
        page = 0;
        cancel = true;
        getGames(100);
    });

    function getGames(delay) {
        if (!ready) return;
        cancel = false;
        if (task !== null) clearTimeout(task);
        if (delay === undefined) delay = 10;

        let form = document.getElementById('games-search-form');

        task = setTimeout(async () => {
            ready = false;

            const formData = new FormData(form);
            formData.append('page', page);

            let search = new URLSearchParams(window.location.search);
            let tId = search.get('t_id');
            if (tId !== '' && tId !== undefined && tId !== null) {
                formData.append('t_id', tId);
            }

            const seenUrl = form.getAttribute('action') + '?' + (new URLSearchParams(formData)).toString();
            formData.append('itemsOnly', '1');
            const url = form.getAttribute('action') + '?' + (new URLSearchParams(formData)).toString();

            try {
                let res = await network.get(url);
                history.pushState({}, document.getElementsByTagName('title')[0].textContent, seenUrl);
                let html = await res.text();
                //if search params has changed during processing, search again
                if (cancel) {
                    ready = true;
                    cancel = false;
                    document.dispatchEvent(new CustomEvent('games:search'));
                    return;
                }
                let wrap = document.getElementById('games__items');
                if (page === 0) {
                    wrap.innerHTML = '';
                }

                let div = document.createElement('div');
                div.innerHTML = html;
                let items = div.getElementsByClassName('game');
                items = Array.prototype.slice.call(items, 0);
                for (let i = 0; i < items.length; i++) {
                    div.removeChild(items[i]);
                    wrap.appendChild(items[i]);
                }

                wrap = document.getElementById('games__show-more-wrap');
                let showMore = div.getElementsByClassName('show-more');
                if (showMore.length > 0) {
                    wrap.innerHTML = showMore[0].outerHTML;
                } else {
                    wrap.innerHTML = '';
                }

                //if search params has changed during processing, search again
                if (cancel) {
                    ready = true;
                    cancel = false;
                    document.dispatchEvent(new CustomEvent('games:search'));
                    return;
                }

                let scripts = div.getElementsByTagName('script');
                for (let n = 0; n < scripts.length; n++) eval(scripts[n].innerHTML)//run script inside div

            } catch (errorMsg) {
                console.error(errorMsg);
            }


            ready = true;
        }, delay);
    }

    document.addEventListener('games:searchGame', () => {
        searchPage = 0;
        searchGames();
    });

    document.addEventListener('games:searchGameNext', () => {
        if (!ready || searching) return;
        searching = true
        searchPage++;
        searchGames();
    });

    document.addEventListener('games:searchCategoryChange', () => {
        page = 0;
        cancel = true;
        categoryChanged(100);
    });

    function categoryChanged(delay = 100) {
        if (!ready) return;
        cancel = false;
        if (task !== null) clearTimeout(task);
        if (delay === undefined) delay = 10;

        let form = document.getElementById('games-search-form');

        task = setTimeout(async () => {
            ready = false;

            const formData = new FormData(form);
            formData.append('page', page);

            // let search = new URLSearchParams(window.location.search);
            // let tId = search.get('t_id');
            // if (tId !== '' && tId !== undefined && tId !== null) {
            //     formData.append('t_id', tId);
            // }

            const seenUrl = form.getAttribute('action') + '?' + (new URLSearchParams(formData)).toString();
            const url = form.getAttribute('action') + '?' + (new URLSearchParams(formData)).toString();

            try {
                let res = await network.get(url);
                history.pushState({}, document.getElementsByTagName('title')[0].textContent, seenUrl);
                let html = await res.text();
                //if search params has changed during processing, search again
                if (cancel) {
                    ready = true;
                    cancel = false;
                    document.dispatchEvent(new CustomEvent('games:searchCategoryChange'));
                    return;
                }
                let wrap = document.getElementById('games__items');
                if (page === 0) {
                    wrap.innerHTML = '';
                }

                let div = document.createElement('div');
                div.innerHTML = html;
                wrap.innerHTML = div.querySelector('#games__items').innerHTML;

                let gamesType = document.querySelector('.games-type');
                if (gamesType !== null) {
                    gamesType.innerHTML = div.querySelector('.games-type').innerHTML;
                }

                wrap = document.getElementById('games__show-more-wrap');
                let showMore = div.getElementsByClassName('show-more');
                if (showMore.length > 0) {
                    wrap.innerHTML = showMore[0].outerHTML;
                } else {
                    wrap.innerHTML = '';
                }

                form.querySelector('.select-providers').innerHTML = div.querySelector('.select-providers').innerHTML;

                //if search params has changed during processing, search again
                if (cancel) {
                    ready = true;
                    cancel = false;
                    document.dispatchEvent(new CustomEvent('games:searchCategoryChange'));
                    return;
                }

                // let scripts = div.getElementsByTagName('script');
                // for (let n = 0; n < scripts.length; n++) eval(scripts[n].innerHTML)//run script inside div

            } catch (errorMsg) {
                console.error(errorMsg);
            }

            ready = true;
        }, delay);
    }

    function searchGames(delay = 100) {
        if (!ready) return;
        if (task !== null) clearTimeout(task);

        let form = document.getElementById('bottom-search-form');

        task = setTimeout(async () => {
            ready = false;
            const formData = new FormData(form);
            formData.append('page', searchPage);

            const url = form.getAttribute('action') + '?' + (new URLSearchParams(formData)).toString();
            let html = '';

            try {
                let res = await network.get(url);
                html = await res.text();
            } catch (errorMsg) {
                console.error(errorMsg);
                return;
            }

            let wrap = form.parentElement.querySelector('.games');
            if (searchPage === 0) {
                wrap.innerHTML = '';
            }

            let div = document.createElement('div');
            div.innerHTML = html;
            let items = div.getElementsByClassName('game');
            items = Array.prototype.slice.call(items, 0);
            for (let i = 0; i < items.length; i++) {
                div.removeChild(items[i]);
                wrap.appendChild(items[i]);
            }

            if (searchPage === 0) wrap.scrollTop = 0;

            let countTitle = wrap.parentElement.querySelector('.games-count');
            let titleData = div.querySelector('.games-count');
            if (titleData !== null) countTitle.innerHTML = titleData.innerHTML;

            ready = true;
            //stop when there`s no more games
            if (html !== "") searching = false;
        }, delay);
    }

    document.addEventListener('showMoreGames', () => {
        let itemsWrap = document.getElementById('games__items');
        if (itemsWrap === null) return;
        let total = itemsWrap.getElementsByClassName('game').length;
        page = Math.ceil(total / 60) + 1;
        getGames();
    });


    let content = document.getElementById('layout-content');
    let anim = new Anim(500);

    let game_started = false;

    // Play games
    document.addEventListener('click:game__button', async e => {
        if (game_started) return;
        game_started = true;
        setTimeout(() => {
            game_started = false
        }, 5000);

        let btn = e.detail.target;
        let name = btn.parentNode.querySelector('.game__name').textContent;
        let url = btn.dataset.url;
        let urlArr = url.split('/');

        // removeContentShading();
        // shadeContent();


        if (urlArr.pop() === 'real') {
            try {
                let query = new URLSearchParams();
                query.append('gameId', urlArr.pop());
                query.append('groupId', urlArr.pop());
                query.append('providerId', urlArr.pop());
                let lang = document.getElementsByTagName('html')[0].getAttribute('lang');

                let res = await network.get('/' + lang + '/balance-mode?' + query.toString());
                let html = await res.text();

                if (html !== '') {
                    Modal.show(html, true, async () => {
                        await removeContentShading();
                    });
                    return;
                }
            } catch (msg) {
                await Spinner.hide('.modal .content', msg);
                Modal.hide('#balance-mode__form');
            }
        }

        if (btn.classList.contains('demo-forbidden')) {
            try {
                let query = new URLSearchParams();
                query.append('gameId', btn.dataset.gameId);
                let lang = document.getElementsByTagName('html')[0].getAttribute('lang');

                let res = await network.get('/' + lang + '/demo-forbidden?' + query.toString());
                let html = await res.text();

                if (html !== '') {
                    Modal.show(html, false, async () => {
                        await removeContentShading();
                    });
                    return;
                }
            } catch (msg) {
                await Spinner.hide('.modal .content', msg);
            }
        }

        await startGame(url, name);
    });


    document.addEventListener('click:balance-mode__button', async e => {
        let btn = e.detail.target;
        e.detail.originalEvent.preventDefault();
        let name = btn.dataset.name;
        let url = btn.getAttribute('href');
        startGame(url, name);
    });


    async function startGame(url, name) {
        setLoadingImg();
        showPreloader(0, 3, false);

        Modal.hide('.modal');
        let res = await network.get(url);

        if (res.redirected) {
            window.location = res.url;
            return;
        }

        let html = await res.text();

        lastUrl = document.location.pathname + window.location.search;
        //console.log('lastUrl', lastUrl);
        //console.log('url', url);
        localStorage.setItem('lastUrl', lastUrl);
        history.pushState({}, name, lastUrl);
        // history.pushState({}, name, url);

        setInnerHtml(content, html);
        Spinner.hide('#layout-content-wrap');
        anim.stop();
        await anim.start(k => content.style.opacity = k);

        // let walletSelector = document.querySelector('.wallet-selector');
        // if (walletSelector !== null) walletSelector.style.display = 'none';
        setTimeout(() => {
            completePreloader();
        }, 500);
    }


    document.addEventListener('click:play__button-close', async e => {
        // showPreloader(0, 3, false);
        let lastUrl = localStorage.getItem('lastUrl');
        // let promise = shadeContent();
        let url = '/' + document.getElementsByTagName('html')[0].getAttribute('lang') + '/games/slots';
        // if (lastUrl !== null && !/^\/(\w{2}\/)?[^\/]*$/.test(lastUrl)) url = lastUrl;
        if (lastUrl !== null && !/^\/(\w{2}\/)?\/.*$/.test(lastUrl)) url = lastUrl;

        loadContent(url);
        // window.location.href = url;

        // history.pushState({}, document.querySelector('title').textContent, url);
        // let res = await network.get(url);
        // let html = await res.text();
        // await Promise.all([promise]);
        // history.pushState({}, name, url);
        // setInnerHtml(content, html);
        // await removeContentShading();
        // setTimeout(() => {
        //     completePreloader();
        // }, 500);
    });


    document.addEventListener('click:providers-list__provider', async e => {
        e.detail.originalEvent.preventDefault();
        let btn = e.detail.target;
        let url = btn.getAttribute('href');

        shadeContent();

        try {
            let res = await network.get(url);
            let html = await res.text();
            setInnerHtml(content, html);
            removeContentShading();
        } catch (errorMsg) {
            console.error(errorMsg);
        }
    });

    document.addEventListener('click:games-slider__all-button', async e => {
        e.detail.originalEvent.preventDefault();
        let btn = e.detail.target;
        let url = btn.getAttribute('href');

        shadeContent();

        try {
            let res = await network.get(url);
            let html = await res.text();
            setInnerHtml(content, html);
            removeContentShading();
        } catch (errorMsg) {
            console.error(errorMsg);
        }
    });


    window.addEventListener('resize', () => {
        let frame = document.getElementById('game-frame');
        if (frame !== null) {
            frame.style.height = Math.round(frame.offsetWidth * 0.75) + 'px';
        }
    });

    let autoMore = document.getElementById("games__auto-show-more");
    if (autoMore !== null) if (isElementCloseToViewportBottom(autoMore)) document.dispatchEvent(new CustomEvent('showMoreGames'));

    document.addEventListener('scroll', (e) => {
        let autoMore = document.getElementById("games__auto-show-more");
        if (autoMore !== null && isElementCloseToViewportBottom(autoMore)) document.dispatchEvent(new CustomEvent('showMoreGames'));

        for (let el of document.querySelectorAll('.click-on-viewport')) {
            if (isElementCloseToViewportBottom(el)) el.click();
        }
    });
})();


document.addEventListener('click:play__fav-button', async e => {
    let t = e.detail.target;
    let url = t.dataset.url;
    let id = t.dataset.id;
    let img = t.querySelector('img');
    img.src = '/img/ui/icons/game/' + (img.src.indexOf('star.svg') >= 0 ? 'star-solid.svg' : 'star.svg');

    try {
        await network.post(url, {id: id});
    } catch (errorMsg) {
        console.error(errorMsg);
    }
});


document.addEventListener('game:bonus_status_pad', async e => {
    console.log(e.detail.payload);
    try {
        let wager = document.getElementById('play__wager');
        if (wager === null) {
            return;
        }
        if (document.getElementById("game-wrapper") === null) {
            return;
        }
        let data;
        try {
            data = JSON.parse(e.detail.payload);
        } catch (e) {
            console.log(e.toString())
            return;
        }
        // if (data.balance !== undefined && data.balance !== null) {
        //     if (!depositShown && lastBalance > threshold && data.balance <= threshold) {
        //         depositShown = true;
        //         wallet.showDeposit();
        //         setTimeout(() => {
        //             depositShown = false
        //         }, 180000)
        //     }
        //     lastBalance = data.balance;
        // }

        if (data.bonus_status == 2) {
            setTimeout(function () {
                document.querySelector('.play__button-close').click();
            }, 1000);
        } else {
            if (data.show_pad !== undefined && data.show_pad !== null && data.show_pad === true) {
                wager.parentElement.classList.add('show-bonus');
                wager.innerHTML = data.progress;
                wager.style = `--progress: ${data.progress_prc}%;`
            } else {
                wager.parentElement.classList.remove('show-bonus');
            }
        }
    } catch (e) {
        console.log(e.toString());
    }
});

document.addEventListener('tournament:table_changed', async e => {
    try {
        let data = JSON.parse(e.detail.payload);
        const table = document.querySelector(`.tournament__play-table[data-tournament-id="${data.tournament_id}"] table tbody`);
        if (table === null) return;
        let winners = data.winners;

        let c = 1;
        let user_position = 0;
        for (const val in winners) {
            const position = val;
            const values = winners[val];
            if (values.user_id == window.userId) user_position = position;
            const row = table.querySelector(`[data-index="${c}"]`);
            if (row.querySelector('td:nth-child(1)').innerText != position ||
                row.querySelector('td:nth-child(2)').innerText != values.login ||
                row.querySelector('td:nth-child(3)').innerText != values.points) {
                row.querySelector('td:nth-child(1)').innerText = position;
                row.querySelector('td:nth-child(2)').innerText = values.login;
                row.querySelector('td:nth-child(3)').innerText = values.points;
                row.classList.add('changed');
                setTimeout(() => {
                    row.classList.remove('changed');
                }, 500);
            }
            c++;
        }
        table.querySelectorAll('tr').forEach(row => {
            row.classList.remove('players_slot')
        })
        if (user_position > 0 && user_position <= 10) {
            table.querySelectorAll('tr')[user_position - 1].classList.add('players_slot');
        }

    } catch (e) {
        console.log(e.toString())
    }
});
