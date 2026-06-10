(() => {

    let gap = 16;

    let ready = true;

    document.addEventListener('click:games-slider__arrow-left', async e => {
        if (!ready) return;
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.parentNode.parentNode.parentNode.querySelector('.games-slider__games_scrollable');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = (w + gap) * 3;

        if (gamesWrap.scrollLeft === 0) {
            button.classList.add('slider__disabled');
            ready = true;
            return;
        }

        ready = false;
        let anim = new Anim(250);
        let left = gamesWrap.scrollLeft;
        let rightButton = button.parentNode.querySelector('.games-slider__arrow-right');

        if (rightButton !== null) rightButton.classList.remove('slider__disabled');

        if (gamesWrap.scrollLeft - slideWidth <= 0) button.classList.add('slider__disabled');
        await anim.start(k => gamesWrap.scrollLeft = left - (slideWidth * k));
        ready = true;
    });

    document.addEventListener('click:games-slider__arrow-right', async e => {
        if (!ready) return;
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.parentNode.parentNode.parentNode.querySelector('.games-slider__games_scrollable');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = (w + gap) * 3;

        ready = false;

        let anim = new Anim(250);
        let left = gamesWrap.scrollLeft;
        let leftButton = button.parentNode.querySelector('.games-slider__arrow-left');

        if (leftButton !== null) leftButton.classList.remove('slider__disabled');
        if (gamesWrap.scrollLeft + gamesWrap.offsetWidth + slideWidth >= gamesWrap.scrollWidth) {
            button.classList.add('slider__disabled');
        }
        await anim.start(k => gamesWrap.scrollLeft = left + (slideWidth * k));
        ready = true;
    });

    document.addEventListener('click:games-slider__left', async e => {
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.closest('.games-slider').querySelector('.slider');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = w;

        if (gamesWrap.scrollLeft === 0) {
            button.classList.add('slider__disabled');
            return;
        }

        let left = gamesWrap.scrollLeft;
        let rightButton = button.parentNode.querySelector('.games-slider__right');

        if (rightButton !== null) rightButton.classList.remove('slider__disabled');

        if (gamesWrap.scrollLeft - slideWidth <= slideWidth / 2) button.classList.add('slider__disabled');
        gamesWrap.scrollLeft = left - slideWidth;
    });

    document.addEventListener('click:games-slider__right', async e => {
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.closest('.games-slider').querySelector('.slider');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = w;

        let left = gamesWrap.scrollLeft;
        let leftButton = button.parentElement.querySelector('.games-slider__left');

        if (leftButton !== null) leftButton.classList.remove('slider__disabled');
        if (gamesWrap.scrollLeft + gamesWrap.offsetWidth + slideWidth >= gamesWrap.scrollWidth) {
            button.classList.add('slider__disabled');
        }
        gamesWrap.scrollLeft = left + slideWidth;
    });

    document.addEventListener('click:slider__left', async e => {
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.closest('.games-slider').querySelector('.slider');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = (w);

        if (gamesWrap.scrollLeft === 0) {
            button.classList.add('slider__disabled');
            return;
        }

        let left = gamesWrap.scrollLeft;
        let rightButton = button.parentNode.querySelector('.games-slider__right');

        if (rightButton !== null) rightButton.classList.remove('slider__disabled');

        if (gamesWrap.scrollLeft - slideWidth <= slideWidth / 2) button.classList.add('slider__disabled');
        gamesWrap.scrollLeft = left - slideWidth;
    });

    document.addEventListener('click:slider__right', async e => {
        let button = e.detail.target;

        // Do nothing if button is disabled.
        if (button.classList.contains('games-slider__arrow-disabled')) return;

        // Find games wrap and ensure that it's scrollable.
        let gamesWrap = button.closest('.games-slider').querySelector('.slider');
        if (gamesWrap === null) return;

        let gameItem = gamesWrap.querySelector('.slider__item');

        let w = gameItem.offsetWidth;
        let slideWidth = (w);

        let left = gamesWrap.scrollLeft;
        let leftButton = button.parentElement.querySelector('.games-slider__left');

        if (leftButton !== null) leftButton.classList.remove('slider__disabled');
        if (gamesWrap.scrollLeft + gamesWrap.offsetWidth + slideWidth >= gamesWrap.scrollWidth) {
            button.classList.add('slider__disabled');
        }
        gamesWrap.scrollLeft = left + slideWidth;
    });

})();
