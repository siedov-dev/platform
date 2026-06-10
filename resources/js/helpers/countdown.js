(() => {
    document.addEventListener('DOMContentLoaded', update_timers);
    setInterval(update_timers, 1000);
    let time_correction = 0;
    //timezone correction
    if (time_correction === 0) {
        //iphone safari compatible
        time_correction = window.local_load_time - new Date(window.server_time.replace(/ /g, "T")).getTime();
        time_correction = Math.floor(time_correction / 1000);
    }

    function update_timers() {
        let update_elems = document.getElementsByClassName('time-estimated');
        for (let el of update_elems) {
            // let target_time = el.dataset.target_time;
            let target_time = el.getAttribute('data-target_time');
            let divider = el.getAttribute('data-divider');
            el.innerText = get_time_diff(target_time, el, true, null, divider);
        }

        let update_blocks = document.getElementsByClassName('update-timer-block');
        for (let el of update_blocks) {
            let target_time = el.getAttribute('data-target_time');
            let hours_only = el.getAttribute('data-hours-only');
            get_time_diff(target_time, el, true, el, null, hours_only === 'true');
        }

        let update_progress = document.getElementsByClassName('time-progress-bar');
        for (let el of update_progress) {

            let unix = Math.round(+new Date() / 1000) - time_correction;
            let prc = 100;
            let start_time = el.getAttribute('data-min');
            let end_time = el.getAttribute('data-max');
            if ((end_time - start_time) > 0) {
                let max = end_time - start_time;
                let curr = unix - start_time;
                prc = Math.floor(curr / max * 100)
            }
            el.style.width = prc + "%";
        }
    }

    function get_time_diff(date_time, el = null, estimated_only = true, update_block = null, divider = null, hours_only = false) {
        let lang = document.querySelector('html').getAttribute('lang');
        let dSymbol = 'd';
        let hSymbol = 'h';
        let mSymbol = 'm';
        let sSymbol = 's';

        if (lang === 'ru') {
            dSymbol = 'д';
            hSymbol = 'ч';
            mSymbol = 'м';
            sSymbol = 'с';
        }

        if (lang === 'ua') {
            dSymbol = 'д';
            hSymbol = 'г';
            mSymbol = 'х';
            sSymbol = 'с';
        }

        if (divider !== null) {
            dSymbol = '';
            hSymbol = '';
            mSymbol = '';
            sSymbol = '';
        }

        let datetime = new Date(date_time.replace(/ /g, "T")).getTime();
        let now = new Date().getTime() - (time_correction * 1000);

        if (isNaN(datetime)) {
            return "";
        }

        let milisec_diff;

        if (now > datetime) {
            if (estimated_only) {
                if (update_block) {
                    let day_els = update_block.getElementsByClassName('timer-days-left');
                    if (day_els[0] != null) {
                        day_els[0].innerText = "--";
                    }
                    let hour_els = update_block.getElementsByClassName('timer-hours-left');
                    if (hour_els[0]) {
                        hour_els[0].innerText = "--";
                    }
                    let min_els = update_block.getElementsByClassName('timer-minutes-left');
                    if (min_els[0] != null) {
                        min_els[0].innerText = "--";
                    }
                    let sec_els = update_block.getElementsByClassName('timer-seconds-left');
                    if (sec_els[0] != null) {
                        sec_els[0].innerText = "--";
                    }
                }
                return "-"
            }
        }
        milisec_diff = datetime - now;
        // send timeout event
        if (el.hasAttribute("data-timeout-event")) {
            if (milisec_diff < 999 && milisec_diff > 0) {
                if (el !== null) {
                    setTimeout(() => {
                        el.dispatchEvent(new CustomEvent('timer-timeout', {bubbles: true}));
                    }, 1000)
                }
            }
        }
        const mins_ms = 1000 * 60;
        const hours_ms = mins_ms * 60;
        const days_ms = hours_ms * 24;

        let days = 0;
        if (!hours_only) {
            days = Math.floor(milisec_diff / days_ms);
            milisec_diff -= days * days_ms;
        }
        let hours = Math.floor(milisec_diff / hours_ms);
        milisec_diff -= hours * hours_ms;
        let minutes = Math.floor(milisec_diff / mins_ms);
        milisec_diff -= minutes * mins_ms;
        let seconds = Math.floor(milisec_diff / 1000);
        milisec_diff = new Date(milisec_diff);

        let resp = "";
        let separator = " ";
        if (divider !== null) separator = divider;
        let showSymbols = true;
        if (el.hasAttribute("data-separator")) separator = el.getAttribute("data-separator");
        if (el.hasAttribute("data-nosymbols")) showSymbols = false;

        if (showSymbols) {
            resp = (days ? days + dSymbol + separator : "") + leadZero(hours) + hSymbol + separator + leadZero(minutes) + mSymbol + separator + leadZero(seconds) + sSymbol;
        } else {
            resp = (days ? leadZero(days) + separator : "") + leadZero(hours) + separator + leadZero(minutes) + separator + leadZero(seconds) + "";
        }

        if (update_block) {
            let day_els = update_block.getElementsByClassName('timer-days-left');
            if (day_els[0] != null) {
                day_els[0].innerText = leadZero(days);
                if (day_els[0].hasAttribute('data-hide-on-zero') && days === 0) {
                    day_els[0].classList.add('hidden');
                }
                if (day_els[0].hasAttribute('data-symbol')) {
                    day_els[0].innerText += day_els[0].getAttribute('data-symbol');
                }
            }
            let hour_els = update_block.getElementsByClassName('timer-hours-left');
            if (hour_els[0]) {
                hour_els[0].innerText = leadZero(hours);
                if (hour_els[0].hasAttribute('data-hide-on-zero') && days === 0) {
                    hour_els[0].classList.add('hidden');
                }
                if (hour_els[0].hasAttribute('data-symbol')) {
                    hour_els[0].innerText += hour_els[0].getAttribute('data-symbol');
                }
            }
            let min_els = update_block.getElementsByClassName('timer-minutes-left');
            if (min_els[0] != null) {
                min_els[0].innerText = leadZero(minutes);
                if (min_els[0].hasAttribute('data-hide-on-zero') && days === 0) {
                    min_els[0].classList.add('hidden');
                }
                if (min_els[0].hasAttribute('data-symbol')) {
                    min_els[0].innerText += min_els[0].getAttribute('data-symbol');
                }
            }
            let sec_els = update_block.getElementsByClassName('timer-seconds-left');
            if (sec_els[0] != null) {
                sec_els[0].innerText = leadZero(seconds);
                if (sec_els[0].hasAttribute('data-hide-on-zero') && days === 0) {
                    sec_els[0].classList.add('hidden');
                }
                if (sec_els[0].hasAttribute('data-symbol')) {
                    sec_els[0].innerText += sec_els[0].getAttribute('data-symbol');
                }
            }
        }

        return resp;
    }

    function leadZero(n) {
        return (n < 10 ? '0' : '') + n;
    }
})();
