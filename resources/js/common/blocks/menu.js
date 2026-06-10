document.addEventListener('ajaxRequest', e => {
    let items = document.getElementsByClassName('menu__item');
    let active = document.querySelector('.menu__item.active');
    if (active !== null) active.classList.remove('active');
    for (let i = 0; i < items.length; i++) {
        if (window.location.href.indexOf(items[i].getAttribute('href')) >= 0) {
            items[i].classList.add('active');
            break;
        }
    }
    let headerSwitch = document.querySelector('.header-switch');
    if (headerSwitch) {
        if (window.location.href.indexOf(headerSwitch.firstElementChild.getAttribute('href')) >= 0) {
            headerSwitch.firstElementChild.classList.add('active');
            headerSwitch.lastElementChild.classList.remove('active');
        } else {
            headerSwitch.firstElementChild.classList.remove('active');
            headerSwitch.lastElementChild.classList.add('active');
        }
    }
});
// hide left menu when click on a menu item or outside the menu
document.addEventListener('click', e => {
    if (e.target.closest('.menu__item') !== null || e.target.closest('.content-link') !== null || e.target.closest('.left-menu-wrapper') === null) {
        let wrap = document.querySelector('.left-menu-wrapper');
        if (wrap !== null) wrap.classList.remove('shown');
    }
});

document.addEventListener('click:theme-switcher', (e) => {
    let switcher = e.detail.target;
    switcher.classList.toggle('dark');
    document.body.classList.toggle('light');

    let isLight = document.body.classList.contains('light');

    setCookie('theme', isLight ? 'light' : 'dark');

    let meta = document.querySelector('meta[name="theme-color"]');
    if (meta !== null) {
        meta.setAttribute('content', isLight ? '#fffff' : '#1A1944');
    }
});

window.refreshingMenu = false;
window.refreshingGamesMenu = false;

document.addEventListener('updateSlcMenu', async (e) => {
    if (refreshingMenu === true) return;
    refreshingMenu = true;
    let resp = await network.post('/menu/update');
    let data = await resp.json();
    let menu_has_new = false;
    document.querySelector('.menu .hidden-menu__first-price p').firstChild.textContent = data['slc_balance'];

    if (data['completed_task'] > 0) {
        document.querySelector('.menu .hidden-menu__first-link.my-tasks .label').classList.remove('hidden');
        document.querySelector('.menu .hidden-menu__first-link.my-tasks .circle-counter').innerText = data['completed_task'];
        document.querySelector('.menu .hidden-menu__first-link.my-tasks .circle-counter').classList.remove('hidden');
        menu_has_new = true;
    } else {
        document.querySelector('.menu .hidden-menu__first-link.my-tasks .label').classList.add('hidden');
        document.querySelector('.menu .hidden-menu__first-link.my-tasks .circle-counter').classList.add('hidden');
    }

    if (data['new_achievement'] > 0 || data['rank_rewards'] > 0) {
        document.querySelector('.menu .hidden-menu__first-link.achievements .label').classList.remove('hidden');
        document.querySelector('.menu .hidden-menu__first-link.achievements .circle-counter').innerText = data['new_achievement'] + data['rank_rewards'];
        document.querySelector('.menu .hidden-menu__first-link.achievements .circle-counter').classList.remove('hidden');
        menu_has_new = true;
    } else {
        document.querySelector('.menu .hidden-menu__first-link.achievements .label').classList.add('hidden');
        document.querySelector('.menu .hidden-menu__first-link.achievements .circle-counter').classList.add('hidden');
    }

    if (data['can_spin'] > 0) {
        document.querySelector('.menu .hidden-menu__first-link.lucky-spin .label').classList.remove('hidden');
        document.querySelector('.menu .hidden-menu__first-link.lucky-spin .circle-counter').innerText = data['can_spin'];
        document.querySelector('.menu .hidden-menu__first-link.lucky-spin .circle-counter').classList.remove('hidden');
        menu_has_new = true;
    } else {
        document.querySelector('.menu .hidden-menu__first-link.lucky-spin .label').classList.add('hidden');
        document.querySelector('.menu .hidden-menu__first-link.lucky-spin .circle-counter').classList.add('hidden');
    }

    if (data['new_shop_items'] > 0) {
        document.querySelector('.menu .hidden-menu__first-link.shop.items .label').classList.remove('hidden');
        document.querySelector('.menu .hidden-menu__first-link.shop.items .circle-counter').innerText = data['new_shop_items'];
        document.querySelector('.menu .hidden-menu__first-link.shop.items .circle-counter').classList.remove('hidden');
        menu_has_new = true;
    } else {
        document.querySelector('.menu .hidden-menu__first-link.shop.items .label').classList.add('hidden');
        document.querySelector('.menu .hidden-menu__first-link.shop.items .circle-counter').classList.add('hidden');
    }

    if (data['new_user_items'] > 0) {
        document.querySelector('.menu .hidden-menu__first-link.shop.user-items .label').classList.remove('hidden');
        document.querySelector('.menu .hidden-menu__first-link.shop.user-items .circle-counter').innerText = data['new_user_items'];
        document.querySelector('.menu .hidden-menu__first-link.shop.user-items .circle-counter').classList.remove('hidden');
        menu_has_new = true;
    } else {
        document.querySelector('.menu .hidden-menu__first-link.shop.user-items .label').classList.add('hidden');
        document.querySelector('.menu .hidden-menu__first-link.shop.user-items .circle-counter').classList.add('hidden');
    }

    let menuBonus = document.querySelector('.menu__item.bonuses-menu-link .has-new');
    let bottomMenuBonus = document.querySelector('.bottom-menu__link.bonuses-menu-link .has-new');

    if (menuBonus) {
        if (data['has_new_bonus']) {
            menu_has_new = true;
            menuBonus.classList.remove('hidden');
        } else {
            menuBonus.classList.add('hidden');
        }
    }
    if (bottomMenuBonus) {
        if (data['has_new_bonus']) {
            bottomMenuBonus.classList.remove('hidden');
        } else {
            bottomMenuBonus.classList.add('hidden');
        }
    }
    if (data['support_notify'] === 1) {
        let supportBtn = document.querySelector('.header__quick-menu-item.support');
        if (supportBtn) {
            supportBtn.classList.add('notify');
            return;
        }
        supportBtn = document.querySelector('.bottom-menu__link.support');
        if (supportBtn) {
            supportBtn.classList.add('notify');
        }
    }

    if (menu_has_new) {
        document.querySelector('#menu-button .has-new').classList.remove('hidden');
    } else {
        document.querySelector('#menu-button .has-new').classList.add('hidden');
    }

    refreshingMenu = false;
});

//update menu on open/close
document.addEventListener('click:menu-button', (e) => {
    document.dispatchEvent(new CustomEvent('updateSlcMenu'));
});

//update menu after load
document.dispatchEvent(new CustomEvent('updateSlcMenu'));

//and update menu every 2 minute
setInterval(() => {
    document.dispatchEvent(new CustomEvent('updateSlcMenu'));
}, 120000);
