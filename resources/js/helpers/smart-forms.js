(() => {
    document.addEventListener('submit', async e => {
        // Don't use magic if fetch is not supported by browser.
        if (!window.fetch) return;

        let form = e.target;
        let target = form.getAttribute('data-ajax');
        let url = form.getAttribute('action');
        let method = form.getAttribute('method').toUpperCase();

        if (target === '' || target === null) return;
        e.preventDefault();

        let targetQr = target;
        let popupType = form.getAttribute('data-popup-type');
        let addClasses = form.getAttribute('data-content-class');
        let addModalClasses = form.getAttribute('data-modal-class');
        target = document.querySelector(target);

        let params = {
            method: method,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Redirect-To': window.location.href
            }
        };

        let formData = new FormData(form);

        if (method === 'POST') {
            params.body = formData;
        } else if (method === 'GET') {
            let queryString = new URLSearchParams(formData).toString();
            let urlQuery = url.split('?')[1];
            url = url.split('?')[0] + '?' + queryString;
            if (typeof urlQuery === 'string' && urlQuery.length > 0) {
                url = url + '&' + urlQuery;
            }
        }

        let errors = form.getElementsByClassName('errors');
        errors = errors.length > 0 ? errors[0] : null;

        let res;
        let data;
        try {
            if (errors !== null) errors.style.height = 0;
            res = await fetch(url, params);
            if (res.redirected && targetQr === "#layout-content"){
                history.pushState({}, document.head.getElementsByTagName('title')[0].textContent, res.url);
            }

            // if (res.redirected) {
            //     let event = new CustomEvent('redirect', {"cancelable": true});
            //     form.dispatchEvent(event);
            //     if (!event.defaultPrevented) {
            //         window.location = res.url;
            //     }
            // } else
            if (!res.ok || res.status === 400 || res.status === 500) {

                if (res.status === 400 || res.status === 500) {
                    data = await res.text();
                } else {
                    data = await res.json();
                }

                let event = new CustomEvent('error', {"cancelable": true, detail: data });
                form.dispatchEvent(event);

                if (!event.defaultPrevented && typeof data === 'object') {
                    let inputs = form.getElementsByTagName('input');
                    let textAreas = form.getElementsByTagName('textarea');
                    let selects = form.getElementsByTagName('selects');



                    if (errors !== null) {
                        errors.innerHTML = '';
                    }

                    let err = null;

                    let len = 0;
                    for (let p in data.errors) {
                        len++;
                        if (errors !== null) {
                            err = document.createElement('div');
                            err.textContent = data.errors[p][0];
                            errors.append(err);
                        }

                        for (let i = 0; i < inputs.length; i++) {
                            if (inputs[i].getAttribute('name') === p) {
                                inputs[i].classList.add('has-error');
                                break;
                            }
                        }
                        for (let i = 0; i < textAreas.length; i++) {
                            if (inputs[i].getAttribute('name') === p) {
                                inputs[i].classList.add('has-error');
                                break;
                            }
                        }
                        for (let i = 0; i < selects.length; i++) {
                            if (inputs[i].getAttribute('name') === p) {
                                inputs[i].classList.add('has-error');
                                break;
                            }
                        }
                    }

                    if (errors !== null) {
                        setTimeout(() => {
                            errors.style.height = errors.scrollHeight + 'px';
                        }, 25);
                    }
                }

                // Set content.
            } else {
                let content = await res.text();

                let event = new CustomEvent('success', {cancelable: true, detail: content});
                form.dispatchEvent(event);

                if (targetQr.toLowerCase() !== "silent") {
                    if (targetQr.toLowerCase() === 'modal') {
                        let modal;
                        if (popupType === 'bottom-popup' && window.PopupModal.showContent !== undefined) {
                            modal = PopupModal.showContent(content);
                        } else {
                            modal = Modal.show(content, !(popupType === 'slc-popup'));
                        }
                        if (modal !== null) {
                            let slcPopup = modal.querySelector('.popup');
                            if (slcPopup !== null) {
                                modal.querySelector('.popup').style.visibility = "visible";
                                modal.querySelector('.popup').style.opacity = "1";
                            }

                            if (addModalClasses !== null) {
                                for (let cls of addModalClasses.split(" ")) {
                                    modal.querySelector('.modal').classList.add(cls);
                                }
                            }

                            let content = modal.querySelector('.content');
                            if (content !== null && addClasses !== null) {
                                for (let cls of addClasses.split(" ")) {
                                    content.classList.add(cls);
                                }
                            }
                        }
                    } else if (!event.defaultPrevented) {
                        if (targetQr === "#layout-content") {
                            showPreloader(0, 3, false);
                        }
                        if (window.setInnerHtml !== undefined) {
                            setInnerHtml(target, content);
                        } else {
                            target.innerHTML = content;
                        }
                        if (targetQr === "#layout-content") {
                            completePreloader();
                        }
                    }
                }
            }
        } catch (e) {
            console.error(e);
            let event = new CustomEvent('error', {"cancelable": true, detail: await res.text() });
            form.dispatchEvent(event);
        }
    });
})();
