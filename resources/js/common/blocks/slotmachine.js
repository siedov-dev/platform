window.spinning = false;

(() => {
    let stopPosition = [-1, -1, -1];
    let currentShifts = [0, 0, 0];
    let spinN = 1;

    async function spin(drum) {
        let slots = document.querySelectorAll(`.slot-machine-popup__elements .drum-${drum} .slot-machine-popup__column`);

        const spinTime = 100; // minimum time of spinning animation
        const startTime = Date.now(); // Time when spinning starts
        let currentStep = 0;
        let currentShift = currentShifts[drum];
        const speedCoef = 1; // Speed of spinning
        let lastTimestamp = 0;

        // Animation loop
        function animate(timestamp) {
            const deltaTime = timestamp - lastTimestamp;
            lastTimestamp = timestamp;
            let currentIndex = -1;
            const currentTime = Date.now();
            const timeElapsed = currentTime - startTime;

            // Update position of each slot
            slots.forEach((slot, index) => {
                let currIdx = (index + currentShift) % slots.length;
                let currPos = (currIdx - 1.5) * slot.clientHeight + (slot.clientHeight * currentStep / 100);
                slot.style.top = `${currPos}px`;
                if (timeElapsed >= spinTime && currentStep === 0 && currIdx === 2) {
                    currentIndex = index;
                }
            });

            if (timeElapsed >= spinTime && currentIndex === stopPosition[drum]) {
                if (drum === 0 || stopPosition[drum - 1] === -1) {
                    // Stop spinning, show final result
                    stopPosition[drum] = -1;
                    return;
                }
            } else {
                if (stopPosition[drum] === -2) stopPosition[drum] = -3;
            }

            currentStep+= deltaTime * speedCoef;
            if (currentStep >= 100) {
                currentStep = 0;
                currentShift++;
                currentShifts[drum] = currentShift;
            }
            requestAnimationFrame(animate);
        }
        requestAnimationFrame(animate)
    }

    async function waitToStart() {
        while (stopPosition[0] === -2 || stopPosition[1] === -2 || stopPosition[2] === -2) await delay(100);
    }

    async function waitToStop() {
        while (stopPosition[2] !== -1) await delay(100);
    }

    async function startSpin()
    {
        if (spinning === true) return;
        spinning = true;
        let balanceEl = document.querySelector('#slot-machine .slc-balance .page__head-wrap-price p').firstChild;
        let jackpotEl = document.querySelector('#slot-machine p.slc-jackpot');
        let jackpotWinEl = document.querySelector('#slot-machine .jackpot');
        let currentBalance = parseInt(balanceEl.textContent);
        if (currentBalance <= 0) {
            let errorEl = document.querySelector('#slot-machine .error');
            errorEl.classList.remove('invisible');
            errorEl.classList.add('shown');
            await delay(2000);
            errorEl.classList.add('invisible');
            errorEl.classList.remove('shown');
            spinning = false;
            return;
        }
        balanceEl.textContent = (currentBalance - 1).toString();

        stopPosition[0] = -2;
        stopPosition[1] = -2;
        stopPosition[2] = -2;

        spin(0);
        await delay(100);
        spin(1);
        await delay(100);
        spin(2);

        let result = await network.post('/slotmachine/spin', {spinN: spinN});
        let data = await result.json();

        await waitToStart();

        stopPosition[0] = data[0];
        stopPosition[1] = data[1];
        stopPosition[2] = data[2];

        let win = data['win'];
        let balance = data['balance'];
        let jackpot = data['jackpot'];
        let bet = data['bet'];
        let isJackpot = data['is_jackpot'];
        let winWrap = document.querySelector('#slot-machine .page__head-block-wrap-price.win');
        winWrap.querySelector('p').firstChild.textContent = "+" + win.toString();

        await waitToStop();
        spinN++;

        if (win > 0 && !isJackpot) {
            winWrap.classList.add('shown')
            await delay(1000);
        }
        if (isJackpot) {
            await delay(200);
            jackpotWinEl.querySelector('.amount').innerText = win.toString();
            jackpotWinEl.classList.remove('invisible');
            jackpotWinEl.classList.add('shown');
            await delay(3000);
            jackpotWinEl.classList.add('invisible');
            jackpotWinEl.classList.remove('shown');
        }
        balanceEl.textContent = balance;
        jackpotEl.textContent = jackpot.toString() + ' SLC';
        spinning = false;
        winWrap.classList.remove('shown');
        return balance >= bet;
    }

document.addEventListener('click:slc-slotmachine-spin', async (e) => {
    if (spinning === true) return;
    e.detail.target.classList.add('hidden');
    await startSpin();
    e.detail.target.classList.remove('hidden');
})

})();
