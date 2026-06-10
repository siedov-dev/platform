// Sets inner HTML for the element and executes all scripts inside.
function setInnerHtml(elm, html) {
    if (typeof elm === 'string') elm = document.querySelector(elm);
    elm.innerHTML = html;

    // Copy title.
    let title = elm.getElementsByTagName('title');
    if (title.length) {
        document.head.getElementsByTagName('title')[0].textContent = title[0].textContent;
        elm.removeChild(title[0]);
    }

    let metas = elm.getElementsByTagName('meta');
    for (let i = 0; i < metas.length; i++) {
        let m = document.head.querySelector('meta[name="' + metas[i].getAttribute('name') + '"]');
        if (m !== null) document.head.replaceChild(metas[i], m);
        metas[i].parentNode.removeChild(metas[i]);
    }

    runScripts(elm);
    setLogoRandomColor();
}

async function loadToElm(url, elm, method = 'get') {
    try {
        let text = '';
        if (method === 'get') {
            let resp = await network.get(url);
            text = await resp.text()
        } else {
            let resp = await network.post(url);
            text = await resp.text()
        }
        setInnerHtml(elm, text);
    } catch (e) {
        console.log(e);
    }
}


function replaceNode(oldNode, newNode) {
    oldNode.parentNode.replaceChild(newNode, oldNode);
    runScripts(newNode);
}

function runScripts(node) {
    Array.from(node.querySelectorAll("script")).forEach(oldScript => {
        const newScript = document.createElement("script");
        Array.from(oldScript.attributes)
            .forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.appendChild(document.createTextNode(oldScript.innerHTML));
        oldScript.parentNode.replaceChild(newScript, oldScript);
    });
}

function scrollTop() {
    let anim = new Anim(500);
    let s = document.body.scrollTop;
    if (s === 0) s = document.documentElement.scrollTop;
    anim.start(k => {
        if (document.body.scrollTop === 0) {
            document.documentElement.scrollTop = s - (s * k);
        } else {
            document.body.scrollTop = s - (s * k)
        }
    });
}

function scrollToElement(el) {
    if (typeof el === 'string') {
        el = document.querySelector(el);
    }
    let anim = new Anim(500);
    let s = document.body.scrollTop;
    if (s === 0) s = document.documentElement.scrollTop;
    let rect = el.getBoundingClientRect();
    let top = s + rect.top;
    let dist = top - s;
    anim.start(k => {
        document.documentElement.scrollTop = s + (dist * k);
        document.body.scrollTop = s + (dist * k);
    });
}


function getScrollbarWidth() {
    // Creating invisible container
    const outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.overflow = 'scroll'; // forcing scrollbar to appear
    outer.style.msOverflowStyle = 'scrollbar'; // needed for WinJS apps
    document.body.appendChild(outer);

    // Creating inner element and placing it in the container
    const inner = document.createElement('div');
    outer.appendChild(inner);

    // Calculating difference between container's full width and the child width
    const scrollbarWidth = (outer.offsetWidth - inner.offsetWidth);

    // Removing temporary elements from the DOM
    outer.parentNode.removeChild(outer);

    return scrollbarWidth;
}


function toggleFullscreen(elem, manual = false) {
    if (manual !== true && elem.requestFullscreen) {
        elem.requestFullscreen();
    } else if (manual !== true && elem.webkitRequestFullScreen) {
        elem.webkitRequestFullScreen();
    } else {
        setTimeout(function () {
            let webimr = document.querySelector('.webim-root');
            if (webimr) document.querySelector('.webim-root').style.setProperty('display', 'none', 'important');
            let gamesl = document.querySelector('.games-slider');
            if (gamesl) document.querySelector('.games-slider').style.setProperty('display', 'none', 'important');
            let footer = document.querySelector('.footer');
            if (footer) document.querySelector('.footer').style.setProperty('display', 'none', 'important');

            let body = document.querySelector('body');

            // body.style.overflow = "hidden";

            elem.style.position = "fixed";
            elem.style.top = "0";
            elem.style.left = "0";
            // elem.style.bottom = "0";
            // elem.style.right = "0";
            // elem.style.height = "100vh";
            elem.style.setProperty('height', '100dvh', 'important');
            elem.style.setProperty('width', '100dvw', 'important');
            elem.style.border = "none";
            elem.style.zIndex = "600";
            elem.style.borderRadius = "0";
            elem.style.margin = "0";
            elem.style.padding = "0";
            elem.classList.add('fullscreen-el');
            window.iphoneFullscreen = true;
            let game_frame = document.getElementById('game-frame');
            if (game_frame) {
                game_frame.style.setProperty('width', '100%', 'important');
                game_frame.style.setProperty('height', '100%', 'important');
                game_frame.style.borderRadius = '0';
                game_frame.style.padding = '0';
                game_frame.style.margin = '0';
                game_frame.classList.add('fullscreen-el');
            }
            elem.scrollIntoView(false);

        }, 200);
    }
}

//esc key exit fullscreen
document.addEventListener('keydown', function (e) {
    if ((e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27)) {
        setTimeout(function () {

            // console.log(window.iphoneFullscreen);
            // if (!window.iphoneFullscreen) return;
            window.iphoneFullscreen = false;
            let elem = document.getElementById('game-wrapper');
            // console.log(elem);
            if (elem !== null) {
                let gamesl = document.querySelector('.games-slider');
                if (gamesl) document.querySelector('.games-slider').style.setProperty('display', '');
                let footer = document.querySelector('.footer');
                if (footer) document.querySelector('.footer').style.setProperty('display', '');

                let body = document.querySelector('body');
                body.style.overflow = "";

                elem.style.position = "static";
                elem.style.width = "auto";
                elem.style.height = Math.round(elem.parentNode.offsetWidth * 0.5625) + 'px';
                elem.style.top = "";
                elem.style.left = "";
                elem.style.bottom = "";
                elem.style.right = "";
                elem.style.setProperty('width', 'auto');
                elem.style.border = "none";
                elem.style.zIndex = "600";
                elem.style.borderRadius = "1rem";
                elem.style.margin = "0";
                elem.style.padding = "0";
                elem.classList.add('fullscreen-el');

                let game_frame = document.getElementById('game-frame');
                if (game_frame) {
                    game_frame.style.position = "static";
                    game_frame.style.width = "100%";
                    game_frame.style.height = Math.round(elem.parentNode.offsetWidth * 0.5625) + 'px';
                    game_frame.style.border = "none";
                    game_frame.style.zIndex = "600";
                    game_frame.style.borderRadius = "1rem";
                    game_frame.style.marginTop = "1rem";
                    game_frame.style.padding = "0";
                    game_frame.classList.add('fullscreen-el');
                }
            }

        }, 200);
    }
});


function copyCode(target) {
    if (typeof target === 'string') target = document.querySelector(target);
    let sel = document.getSelection();
    sel.removeAllRanges();
    let range = new Range();
    range.selectNode(target);
    sel.addRange(range);
    document.execCommand("copy");
}

/**
 * localization
 */
function __(key) {
    if (localization[key] !== undefined) return localization[key];
    return key;
}

window.addEventListener("orientationchange", function (event) {
    console.log("the orientation of the device is now " + event.target.screen.orientation.angle);
    if (window.iphoneFullscreen === true) {
        setTimeout(function () {
            let webimr = document.querySelector('.webim-root');
            if (webimr) document.querySelector('.webim-root').style.setProperty('display', 'none', 'important');
            let gamesl = document.querySelector('.games-slider');
            if (gamesl) document.querySelector('.games-slider').style.setProperty('display', 'none', 'important');
            let footer = document.querySelector('.footer');
            if (footer) document.querySelector('.footer').style.setProperty('display', 'none', 'important');

            let body = document.querySelector('body');

            // body.style.overflow = "hidden";
            let elem = document.getElementById('game-wrapper');
            elem.style.position = "fixed";
            elem.style.top = "0";
            elem.style.left = "0";
            // elem.style.bottom = "0";
            // elem.style.right = "0";
            // elem.style.height = "100vh";
            elem.style.setProperty('height', '100vh', 'important');
            elem.style.setProperty('width', '100vw', 'important');
            elem.style.border = "none";
            elem.style.zIndex = "600";
            elem.style.borderRadius = "0";
            elem.style.margin = "0";
            elem.style.padding = "0";
            window.iphoneFullscreen = true;
            elem.classList.add('fullscreen-el');

            let game_frame = document.getElementById('game-frame');
            if (game_frame) {
                game_frame.style.setProperty('width', '100%', 'important');
                game_frame.style.setProperty('height', '100%', 'important');
                game_frame.style.borderRadius = '0';
                game_frame.style.padding = '0';
                game_frame.style.margin = '0';
                game_frame.classList.add('fullscreen-el');
            }
            elem.scrollIntoView(false);

        }, 200);

        setTimeout(function () {
            let webimr = document.querySelector('.webim-root');
            if (webimr) document.querySelector('.webim-root').style.setProperty('display', 'none', 'important');
            let gamesl = document.querySelector('.games-slider');
            if (gamesl) document.querySelector('.games-slider').style.setProperty('display', 'none', 'important');
            let footer = document.querySelector('.footer');
            if (footer) document.querySelector('.footer').style.setProperty('display', 'none', 'important');

            let body = document.querySelector('body');

            // body.style.overflow = "hidden";
            let elem = document.getElementById('game-wrapper');
            elem.style.position = "fixed";
            elem.style.top = "0";
            elem.style.left = "0";
            // elem.style.bottom = "0";
            // elem.style.right = "0";
            // elem.style.height = "100vh";
            elem.style.setProperty('height', '100vh', 'important');
            elem.style.setProperty('width', '100vw', 'important');
            elem.style.border = "none";
            elem.style.zIndex = "600";
            elem.style.borderRadius = "0";
            elem.style.margin = "0";
            elem.style.padding = "0";
            window.iphoneFullscreen = true;
            elem.classList.add('fullscreen-el');

            let game_frame = document.getElementById('game-frame');
            if (game_frame) {
                game_frame.style.setProperty('width', '100%', 'important');
                game_frame.style.setProperty('height', '100%', 'important');
                game_frame.style.borderRadius = '0';
                game_frame.style.padding = '0';
                game_frame.style.margin = '0';
                game_frame.classList.add('fullscreen-el');
            }
            elem.scrollIntoView(false);

        }, 500);
    }
});


document.addEventListener('fullscreenchange', (event) => {
    if (document.fullscreenElement) {
        console.log(`Element: ${document.fullscreenElement.id} entered full-screen mode.`);
    } else {
        console.log('Leaving full-screen mode.');
        setTimeout(function () {

            // console.log(window.iphoneFullscreen);
            // if (!window.iphoneFullscreen) return;
            window.iphoneFullscreen = false;
            let elem = document.getElementById('game-wrapper');
            // console.log(elem);
            if (elem !== null) {
                let gamesl = document.querySelector('.games-slider');
                if (gamesl) document.querySelector('.games-slider').style.setProperty('display', '');
                let footer = document.querySelector('.footer');
                if (footer) document.querySelector('.footer').style.setProperty('display', '');

                let body = document.querySelector('body');
                body.style.overflow = "";

                elem.style.position = "static";
                elem.style.width = "auto";
                elem.style.height = Math.round(elem.parentNode.offsetWidth * 0.5625) + 'px';
                elem.style.top = "";
                elem.style.left = "";
                elem.style.bottom = "";
                elem.style.right = "";
                elem.style.setProperty('width', 'auto');
                elem.style.border = "none";
                elem.style.zIndex = "600";
                elem.style.borderRadius = "1rem";
                elem.style.margin = "0";
                elem.style.padding = "0";
                elem.classList.remove('fullscreen-el');

                let game_frame = document.getElementById('game-frame');
                if (game_frame) {
                    game_frame.style.position = "static";
                    game_frame.style.width = "100%";
                    game_frame.style.height = Math.round(elem.parentNode.offsetWidth * 0.5625) + 'px';
                    game_frame.style.border = "none";
                    game_frame.style.zIndex = "600";
                    game_frame.style.borderRadius = "1rem";
                    game_frame.style.marginTop = "1rem";
                    game_frame.style.padding = "0";
                    game_frame.classList.remove('fullscreen-el');
                }
            }

        }, 200);
    }
});

window.addEventListener('popstate', function(event) {
    // console.log('popstate', event);
    event.preventDefault();
    if (window.supportTriggerClick === true) {
        window.supportTriggerClick = false;
        console.log("closing support window");
        return;
    }
    //if user navigates back in game - close game
    if (document.querySelector('.play__button-close') !== null) {
        console.log(document.querySelector('.play__button-close'));
        setTimeout(function () {
            document.querySelector('.play__button-close').click();
        }, 0);
    } else {
        //else reload page
        loadContent(window.location);
        // window.location.reload(true);
    }

    // if (r == true) {
    //     // Call Back button programmatically as per user confirmation.
    //     history.back();
    //     // Uncomment below line to redirect to the previous page instead.
    //     // window.location = document.referrer // Note: IE11 is not supporting this.
    // } else {
    //     // Stay on the current page.
    //     history.pushState(null, null, window.location.pathname);
    // }

    // history.pushState(null, null, window.location.pathname);

}, {
    capture: true,
});

function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

function isElementCloseToViewportBottom(el) {
    const rect = el.getBoundingClientRect();
    const closeInPixels = 1600;
    return (
        rect.bottom - closeInPixels <= (window.innerHeight || document.documentElement.clientHeight)
    );
}

function fdepChipSelect(chip, dep = 0) {
    chip.closest('.popup-dialog').querySelectorAll('.fs-chip').forEach((e) => e.classList.remove('max'));
    chip.classList.add('max');
    if (chip.closest('.popup-dialog').querySelector('.cabinet__bonus-menu-item'))
        chip.closest('.popup-dialog').querySelector('.cabinet__bonus-menu-item').setAttribute('data-deposit', dep);
    if (chip.closest('.popup-dialog').querySelector('.cabinet__bonus-take-btn'))
        chip.closest('.popup-dialog').querySelector('.cabinet__bonus-take-btn').setAttribute('data-deposit', dep);
    if (chip.closest('.popup-dialog').querySelector('.activate-bonus_and_show-bonuses'))
        chip.closest('.popup-dialog').querySelector('.activate-bonus_and_show-bonuses').setAttribute('data-deposit', dep);
    if (chip.closest('.popup-dialog').querySelector('.bonus-btn-main'))
        chip.closest('.popup-dialog').querySelector('.bonus-btn-main').setAttribute('data-deposit', dep);
}


async function reloadContent() {
    const content = document.getElementById('layout-content');

    showPreloader(0, 2, false);

    const url = window.location.href;

    Overlay.hide();
    scrollTop();

    try {
        let res = await network.get(url);
        let text = await res.text();
        setInnerHtml(content, text);
    } catch (errorMsg) {

    }

    completePreloader();
}

async function loadContent(url) {
    const content = document.getElementById('layout-content');

    showPreloader(0, 2, false);

    Overlay.hide();
    scrollTop();

    try {
        let res = await network.get(url);
        let text = await res.text();
        setInnerHtml(content, text);
    } catch (errorMsg) {
        console.log(errorMsg);
    }

    completePreloader();
}

function pageIsInIframe () {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}

window.onmessage = function(event) {
    if (event && (event.data === 'closetFrameInModal')) {
        setTimeout(function () {
            if (document.querySelector('.modal iframe')) {
                Modal.hide(document.querySelector('.modal iframe'));
            }
        }, 100);
    }
};

//
// function awaitRecaptchaLoad() {
//     return new Promise( async (resolve, reject) => {
//         let tries = 0;
//         while (typeof grecaptcha.render !== 'function' && tries < 10) {
//             await delay(200);
//             tries++;
//         }
//         if (typeof grecaptcha.render !== 'function') reject();
//
//         resolve();
//     });
// }
