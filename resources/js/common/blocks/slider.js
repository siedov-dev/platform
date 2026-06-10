(() => {

    let gap = 16;
    let ready = true;


    document.addEventListener('swipeLeft:slider', e => {
        slideRight(e.detail.target);
    });

    document.addEventListener('swipeRight:slider', e => {
        slideLeft(e.detail.target);
    });


    document.addEventListener('click:slider__prev', e => {
        let button = e.detail.target;
        let itemsWrap = document.querySelector(button.dataset.target);
        if (itemsWrap === null) return;
        if (button.classList.contains('slider__disabled')) return;
        slideLeft(itemsWrap);
    });


    document.addEventListener('click:slider__next', e => {
        let button = e.detail.target;
        let itemsWrap = document.querySelector(button.dataset.target);
        if (itemsWrap === null) return;
        if (button.classList.contains('slider__disabled')) return;
        slideRight(itemsWrap);
    });


    async function slideLeft(itemsWrap) {
        if (!ready) return;

        let prevButton = document.querySelector('.slider__prev[data-target="#' + itemsWrap.id + '"]');
        let nextButton = document.querySelector('.slider__next[data-target="#' + itemsWrap.id + '"]');

        let items = itemsWrap.getElementsByClassName('slider__item');
        if (items.length === 0) return;

        let w = items[0].offsetWidth;

        if (items.scrollLeft === 0) {
            ready = true;
            return;
        }

        ready = false;
        let anim = new Anim(250);
        let left = itemsWrap.scrollLeft;

        if (nextButton !== null) nextButton.classList.remove('slider__disabled');
        if (itemsWrap.scrollLeft - w - gap === 0) prevButton.classList.add('slider__disabled');

        await anim.start(k => itemsWrap.scrollLeft = left - (w + gap) * k);
        ready = true;
    }


    async function slideRight(itemsWrap) {
        if (!ready) return;

        let prevButton = document.querySelector('.slider__prev[data-target="#' + itemsWrap.id + '"]');
        let nextButton = document.querySelector('.slider__next[data-target="#' + itemsWrap.id + '"]');

        if (prevButton !== null) prevButton.classList.remove('slider__disabled');

        let items = itemsWrap.getElementsByClassName('slider__item');
        if (items.length === 0) return;

        let w = items[0].offsetWidth;

        ready = false;

        let anim = new Anim(250);
        let left = itemsWrap.scrollLeft;


        if (prevButton !== null) prevButton.classList.remove('slider__disabled');
        if (itemsWrap.scrollLeft + itemsWrap.offsetWidth + w + gap >= itemsWrap.scrollWidth) {
            nextButton.classList.add('slider__disabled');
        }
        await anim.start(k => itemsWrap.scrollLeft = left + (w + gap) * k);
        ready = true;
    }
})();
