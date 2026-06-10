(()=>{
    let wheel_spinning = false;

    async function wheelSpin(e) {
        if (wheel_spinning === true) return;
        wheel_spinning = true;

        let btn = e.detail.target;

        if (btn.classList.contains('disabled')) return;

        let wheelOfFortune = document.getElementById('wheel-of-fortune');
        wheelOfFortune.querySelector('p.text-fs').classList.add('hidden');
        let wheel = wheelOfFortune.querySelector('.wheel-drum');
        // let textFs = wheelOfFortune.querySelector('.text-fs');
        let textSpins = wheelOfFortune.querySelector('.text-spins');
        let btnTakeBonus = wheelOfFortune.querySelector('.get-bonus-btn');
        if (btnTakeBonus !== null) btnTakeBonus.classList.add('disabled');

        let resp;
        //get server data
        try {
            resp = await network.post('/wheel/spin');
        } catch (e) {
            // console.log(e.toString());
            if (btnTakeBonus !== null) btnTakeBonus.classList.remove('disabled');
        }
        let data = await resp.json();
        if (data.error !== undefined) {
            wheel_spinning = false;
            Modal.showBottom(data.modal);
            return;
        }
        let angle = data.angle - (360 * 3 * wheel_spin);
        let title = data.title;

        //spin
        wheel.firstElementChild.style.transform = `rotate(${angle}deg)`;
        wheel.querySelector('#wheelCanvasTexts').style.transform = `rotate(${angle}deg)`;
        wheel_spin++;

        wheelOfFortune.querySelector('.bonus-win-block').classList.add('hidden');

        if (btn.classList.contains('spin_again-bonus-btn')) {
            btn.classList.add('disabled');
        } else {
            btn.classList.add('hidden');
        }
        // textFs.classList.add('hidden');
        textSpins.classList.add('hidden');

        //then show win
        setTimeout(() => {
            wheelOfFortune.querySelector('.wheel-bonus-got .today-won').classList.add('hidden');
            wheelOfFortune.querySelector('.wheel-bonus-got .now-won .text-bigger').innerText = title;
            wheelOfFortune.querySelector('.wheel-bonus-got').classList.remove('hidden');
            wheelOfFortune.querySelector('.wheel-bonus-got .now-won').classList.remove('hidden');
            if (btn.classList.contains('spin_again-bonus-btn')) {
                btn.innerText = btn.innerText.split("(")[0] + " (" + data.respin_cost + " SLC)";
                if (data.can_respin) btn.classList.remove('disabled');
                if (data.respins > 1) btn.classList.add('hidden');
            }
            if (btnTakeBonus !== null) btnTakeBonus.classList.remove('disabled');
            wheel_spinning = false;
            wheelOfFortune.querySelector('.bonus-win-block').classList.remove('hidden');
        }, 4200);
    }

    document.addEventListener('click:btn-wheel-spin', async (e) => {
        await wheelSpin(e);
    });

    document.addEventListener('click:spin_again-bonus-btn', async (e) => {
        await wheelSpin(e);
    });

})()
