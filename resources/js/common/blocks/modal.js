(() => {

    let ti = null;
    let minWidth = 1250;
    let margin = 16;

    function disableScroll(el) {
        if (el === document.body && Overlay.isShown()) return;

        let scrollWidth = window.innerWidth - el.offsetWidth;
        el.style.overflowY = 'hidden';
        el.style.paddingRight = scrollWidth + 'px';
        el.classList.add('no-scroll');

        if (el === document.body) {
            let header = document.getElementById('header');
            let overlay = document.getElementById('layout-overlay');
            if (header !== null) {
                header.style.paddingRight = scrollWidth + 'px';
            }
            if (overlay !== null) {
                overlay.style.paddingRight =  scrollWidth + 'px';
            }
        }
    }

    function enableScroll(el, delay) {
        if (delay === undefined) delay = 500;
        setTimeout(() => {
            el.style.overflowY = null;
            el.style.paddingRight = '0';
            if (el === document.body) {
                let header = document.getElementById('header');
                let overlay = document.getElementById('layout-overlay');
                if (header !== null) {
                    header.style.paddingRight = '0';
                }
                if (overlay !== null) {
                    overlay.style.paddingRight = '0';
                }
            }
        }, delay);
    }

    async function closeButtonHandler() {
        let modalParams = this.wrap.querySelector('.modal-params');

        let anim = new Anim(250);
        await anim.start(k => {
            this.wrap.style.opacity = 1 - k;
        });

        let content = this.wrap.content._content;
        if (content !== undefined && content._oldParent !== undefined) {
            content.parentNode.removeChild(content);
            content.style.display = 'none';
            content._oldParent.appendChild(content);
        }

        if (modalParams !== null && modalParams.hasAttribute('data-refresh_on_close')) {
            reloadContent();
        }

        this.wrap.classList.remove('shown');
        document.body.removeChild(this.wrap);

        let allModals = document.body.getElementsByClassName('modal-wrap');
        if (allModals.length > 0) {
            enableScroll(allModals[allModals.length - 1], 0);
        } else {
            enableScroll(document.body, 0);
        }
        if (this.closeCallback !== undefined && this.closeCallback !== null) {
            this.closeCallback();
        }

        this.wrap.querySelectorAll('form').forEach(f => { f.dispatchEvent(new CustomEvent('form-closed'))})
    }

    function createModal(closeCallback) {
        let wrap = document.createElement('div');
        wrap.className = 'modal-wrap';

        let veil = document.createElement('div');
        veil.className = 'veil';

        let modal = document.createElement('div');
        wrap.modal = modal;
        modal.className = 'modal';

        let closeButton = document.createElement('div');
        closeButton.className = 'close-button';
        closeButton.innerHTML = "&times;";
        closeButton.wrap = wrap;
        wrap.closeButton = closeButton;
        closeButton.closeCallback = closeCallback
        closeButton.onclick = closeButtonHandler;

        let content = document.createElement('div');
        content.className = 'content';
        wrap.content = content;

        wrap.appendChild(veil);
        wrap.appendChild(modal);
        modal.appendChild(closeButton);
        modal.appendChild(content);

        let allModals = document.body.getElementsByClassName('modal-wrap');
        if (allModals.length > 0) {
            disableScroll(allModals[allModals.length - 1]);
        } else {
            disableScroll(document.body);
        }

        // Set zIndex to move it to the top.
        wrap.style.zIndex = (allModals.length + 1000).toString();

        document.body.appendChild(wrap);
        return wrap;
    }




    window.Modal = {
        show(content, showCloseButton = true, closeCallback = null) {
            let modal = createModal(closeCallback);
            if (showCloseButton === false) {
                modal.closeButton.style.display = 'none';
            }
            if (content instanceof HTMLElement) {
                if (content.parentNode !== null) {
                    content._oldParent = content.parentNode;
                    content.parentNode.removeChild(content);
                }
                modal.content._content = content;
                modal.content.appendChild(content);
                content.style.display = null;
            } else if (window.setInnerHtml !== undefined) {
                setInnerHtml(modal.content, content);
            } else {
                modal.content.innerHTML = content;
            }

            modal.classList.add('shown');
            let anim = new Anim(250);
            anim.start(k => {
                modal.style.opacity = k;
            });

            this.updatePosition(modal);
            return modal;
        },

        updatePosition(modalWrap) {
            let rect = modalWrap.modal.getBoundingClientRect();
            if (window.innerHeight - (rect.top + rect.height) < rect.top) {
                if (window.innerHeight < rect.height - (margin * 2)) {
                    modalWrap.modal.style.marginTop = margin + 'px';
                } else {
                    let m = (window.innerHeight - rect.height) / 2;
                    modalWrap.modal.style.marginTop = (m > margin ? m : margin) + 'px';
                }
            }
        },

        alert(msg) {
            Modal.show(`<div class="layout-message">
                    <div class="layout-message__text">${msg}</div>
                    <div class="layout-message__button" onclick="Modal.hide(this)">OK</div>
                </div>`);
        },

        hide(el) {
            if (typeof el === 'string') el = document.querySelector(el);
            if (el === null) return;
            if (el.closest('.popup-modal') !== null) {
                window.PopupModal.hide();
                return;
            }
            let wrap = el.closest('.modal-wrap');
            // while (wrap !== null && !wrap.classList.contains('modal-wrap') && wrap.parentNode !== document.body) {
            //     wrap = wrap.parentNode;
            // }
            if (wrap !== null) {
                wrap.closeButton.click();
            }
        },

        hideClosingButton() {
            document.querySelector('.modal .close-button').style.display = 'none';
        },

        showBottom(content) {
            if (window.PopupModal.showContent !== undefined) {
                return PopupModal.showContent(content);
            }
            return Modal.show(content);
        },
};

    // Handle "esc" key press.
    document.addEventListener('keydown', function(e) {
        if ((e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27)) {
            //promo form
            let closePromo = document.querySelector('.cabinet__code-form .cabinet__code-form-close');
            if (closePromo !== null) {
                if (closePromo.parentElement.parentElement.style.opacity == 1) {
                    closePromo.click();
                    return;
                }
            }

            //modals
            let closeButtons = document.querySelectorAll('.modal-wrap .close-button');
            if (closeButtons.length > 0) {
                closeButtons[closeButtons.length - 1].click();
                return;
            }

            //user menu
            let closeUserMenu = document.getElementById('layout-overlay-close-button');
            if (closeUserMenu !== null) closeUserMenu.click();
        }
    });
})();
