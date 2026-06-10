(() => {
    let easing = t => t<.5 ? 2*t*t : -1+(4-2*t)*t;


    document.addEventListener('showNotifications', show);
    document.addEventListener('hideNotifications', hide);


    function show() {
        let button = document.getElementById('ntf-button');
        let dropdown = document.getElementById('ntf-dropdown');
        let backdrop = document.getElementById('ntf-backdrop');

        if (button.classList.contains('bottom-menu__link_enabled')) return;

        button.classList.add('bottom-menu__link_enabled');

        backdrop.style.opacity = '0';
        backdrop.style.display = 'block';

        dropdown.style.opacity = '0';
        dropdown.style.bottom = 72 + 'px';
        dropdown.style.display = 'flex';

        let start = Date.now();
        let duration = 250;

        document.body.style.overflowY = 'hidden';

        let f = () => {
            let k = (Date.now() - start) / duration;
            if (k > 1) k = 1;
            k = easing(k);

            backdrop.style.opacity = k.toFixed(2);
            dropdown.style.opacity = k.toFixed(2);
            dropdown.style.bottom = 72 + (32 * k) + 'px';

            if (k < 1) {
                requestAnimationFrame(f);
            }
        }
        requestAnimationFrame(f);
    }


    function hide() {
        let button = document.getElementById('ntf-button');
        let dropdown = document.getElementById('ntf-dropdown');
        let backdrop = document.getElementById('ntf-backdrop');

        if (!button.classList.contains('bottom-menu__link_enabled')) return;

        button.classList.remove('bottom-menu__link_enabled');

        let start = Date.now();
        let duration = 250;

        let header = document.getElementById('header');
        if (header !== null) header.style.zIndex = '49';

        let f = () => {
            let k = (Date.now() - start) / duration;
            if (k > 1) k = 1;
            k = easing(k);

            backdrop.style.opacity = (1 - k).toFixed(2);
            dropdown.style.opacity = (1 - k).toFixed(2);
            dropdown.style.bottom = 104 - (32 * k) + 'px';

            if (k < 1) {
                requestAnimationFrame(f);
            } else {
                dropdown.style.display = 'none';
                backdrop.style.display = 'none';
                document.body.style.overflowY = null;
            }
        }
        requestAnimationFrame(f);
    }

})();
