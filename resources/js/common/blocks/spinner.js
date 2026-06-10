window.Spinner = {
    show(target) {
        if (typeof target === 'string') {
            target = document.querySelector(target);
        }
        if (target === null) {
            console.log('spinner target is null');
            return;
        }
        let position = target.style.position;
        if (!['fixed', 'absolute', 'relative'].includes(position)) {
            target.style.position = 'relative';
        }

        let spinner = target.querySelector('.spinner');
        if (spinner !== null) return;

        spinner = document.createElement('div');
        spinner.className = 'spinner';

        let roller = document.createElement('div');
        roller.className = 'roller';
        roller.innerHTML = '<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>';
        spinner.appendChild(roller);

        let msg = document.createElement('div');
        msg.className = 'message';
        spinner.appendChild(msg);

        if (target.offsetHeight > 800 && target.offsetHeight < 1000) {
            roller.style.top = '50%';
            roller.style.marginTop = '-40px';
            msg.style.top = '50%';
            msg.style.marginTop = '-40px';
        }
        if (target.offsetHeight > 700) {
            roller.style.top = '10rem';
            msg.style.top = '10rem';
        }
        target.appendChild(spinner);

        spinner.classList.add('shown')
        let anim = new Anim(250);
        anim.start(k => spinner.style.opacity = k.toFixed(2));
    },
    hide(target, message, callback, timeout = 2000) {
        return new Promise(async resolve => {
            if (typeof target === 'string') {
                target = document.querySelector(target);
            }
            if (target === null) {
                console.log('spinner target is null');
                return;
            }
            let spinner = target.querySelector('.spinner');
            if (spinner === null) {
                if (typeof callback === 'function') callback();
                return;
            }
            let roller = spinner.getElementsByClassName('roller');
            let msg = spinner.getElementsByClassName('message');

            if (message === undefined || roller.length === 0 || msg.length === 0) {
                let anim = new Anim(250);
                await anim.start(k => spinner.style.opacity = (1 - k).toFixed(2));
                spinner.classList.remove('shown')
                setTimeout(function() {
                    if (spinner.parentNode !== null) {
                        spinner.parentNode.removeChild(spinner);
                    }
                    resolve();
                }, 150);
            } else {
                roller = roller[0];
                msg = msg[0];
                msg.textContent = message;
                roller.classList.add('hidden');
                msg.classList.add('shown');
                setTimeout(async function() {
                    msg.classList.remove('shown');
                    let anim = new Anim(250);
                    await anim.start(k => spinner.style.opacity = (1 - k).toFixed(2));
                    spinner.classList.remove('shown')
                    if (typeof callback === 'function') {
                        callback();
                    }
                    if (spinner.parentNode !== null) {
                        spinner.parentNode.removeChild(spinner);
                    }
                    resolve();
                }, timeout);
            }
        });
    },
    prepareToReplace(target) {
        if (typeof target === 'string') {
            target = document.querySelector(target);
        }
        $(target).find('.spinner')
            .css({backgroundColor: '#fff'})
            .find('.lds-roller').fadeOut(250);
    }
};
