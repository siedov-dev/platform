(() => {


    document.addEventListener('swipeLeft:main-slider__slide', e => {
        let t = e.detail.target;
        slideLeft(t.parentNode.parentNode);
        resetAutoSlide();
    });

    document.addEventListener('swipeRight:main-slider__slide', e => {
        let t = e.detail.target;
        slideRight(t.parentNode.parentNode);
        resetAutoSlide();
    });

    function slideLeft(wrap) {
        if (wrap === null) return;
        let active = wrap.querySelector('.main-slider__slide_active');
        let prev = wrap.querySelector('.main-slider__slide_prev');
        let next = wrap.querySelector('.main-slider__slide_next');

        prev.classList.remove('main-slider__slide_prev');

        active.classList.remove('main-slider__slide_active');
        active.classList.add('main-slider__slide_prev');

        next.classList.remove('main-slider__slide_next');
        next.classList.add('main-slider__slide_active');

        // Find next right slide.
        let n = next.nextSibling;
        while (n !== null) {
            if (n.classList !== undefined && n.classList.contains('main-slider__slide')) {
                break;
            }
            n = n.nextSibling;
        }
        if (n === null) n = wrap.getElementsByClassName('main-slider__slide')[0];
        n.classList.add('main-slider__slide_next');

        updateSliderDots();

        let activeBtn = wrap.querySelector('.main-slider__button_active');
        if (activeBtn === null) return;
        activeBtn.classList.remove('main-slider__button_active');
        do {
            activeBtn = activeBtn.nextSibling;
        } while (activeBtn !== null && activeBtn.nodeType === Node.TEXT_NODE);

        if (activeBtn === null) {
            wrap.querySelector('.main-slider__button').classList.add('main-slider__button_active')
        } else {
            activeBtn.classList.add('main-slider__button_active');
        }
    }


    function slideRight(wrap) {
        if (wrap === null) return;
        let active = wrap.querySelector('.main-slider__slide_active');
        let prev = wrap.querySelector('.main-slider__slide_prev');
        let next = wrap.querySelector('.main-slider__slide_next');

        prev.classList.remove('main-slider__slide_prev');
        prev.classList.add('main-slider__slide_active');

        active.classList.remove('main-slider__slide_active');
        active.classList.add('main-slider__slide_next');

        next.classList.remove('main-slider__slide_next');

        // Find prev left slide.
        let n = prev.previousSibling;
        while (n !== null) {
            if (n.classList !== undefined && n.classList.contains('main-slider__slide')) {
                break;
            }
            n = n.previousSibling;
        }
        if (n === null) {
            let slides = wrap.getElementsByClassName('main-slider__slide');
            n = slides[slides.length - 1];
        }
        n.classList.add('main-slider__slide_prev');

        updateSliderDots();

        let activeBtn = wrap.querySelector('.main-slider__button_active');
        if (activeBtn === null) return;
        activeBtn.classList.remove('main-slider__button_active');
        do {
            activeBtn = activeBtn.previousSibling;
        } while (activeBtn !== null && activeBtn.nodeType === Node.TEXT_NODE);

        if (activeBtn === null) {
            let buttons = wrap.getElementsByClassName('main-slider__button');
            if (buttons.length > 0) buttons[buttons.length - 1].classList.add('main-slider__button_active');
        } else {
            activeBtn.classList.add('main-slider__button_active');
        }
    }

    function updateSliderDots()
    {
        let dots = document.querySelector('.main-slider__dots');
        let slides = document.querySelector('.main-slider__slides');
        let activeSlide = document.querySelector('.main-slider__slides .main-slider__slide_active');
        if (dots !== null) {
            dots.querySelectorAll('.main-slider__dot').forEach(dot => dot.classList.remove('active'));
            let indexOfActive = Array.from(slides.children).indexOf(activeSlide) + 1;
            dots.querySelector(`.main-slider__dot:nth-child(${indexOfActive})`).classList.add('active');
        }
    }

    function resetAutoSlide() {
        if (window.mainSliderInterval) clearInterval(window.mainSliderInterval);
        window.mainSliderInterval = setInterval(() => {
            slideLeft(document.querySelector('.main-slider'));
        }, 5000)
    }

    window.mainSliderInterval = setInterval(() => {
        slideLeft(document.querySelector('.main-slider'));
    }, 5000)

})();
