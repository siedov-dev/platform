document.addEventListener('input', (e) => {
    if (e.target.parentElement.classList.contains('custom-range')) {
        let t = e.target;
        const thumbPrc = 25 / t.clientWidth;
        let val = t.value - t.min;
        let max = t.max - t.min;
        let prc =  (val / max * 100) * (1 - thumbPrc) + (thumbPrc / 2 * 100);
        t.parentElement.style.setProperty('--value', `${prc}%`);
        if (t.parentElement.dataset.targetValue) {
            document.querySelector(t.parentElement.dataset.targetValue).innerText = t.value;
        }
    }
});

document.addEventListener('change', (e) => {
    if (e.target.parentElement.classList.contains('custom-range')) {
        let t = e.target;
        const thumbPrc = 25 / t.clientWidth;
        let val = t.value - t.min;
        let max = t.max - t.min;
        let prc =  (val / max * 100) * (1 - thumbPrc) + (thumbPrc / 2 * 100);
        t.parentElement.style.setProperty('--value', `${prc}%`);
        if (t.parentElement.dataset.targetValue) {
            document.querySelector(t.parentElement.dataset.targetValue).innerText = t.value;
        }
    }
});
