(() => {

    document.addEventListener('click:next-rank-button', (e) => {
        const t = e.detail.target;
        const ranks = t.closest('.ranks');
        const items = ranks.querySelector('.ranks-wrapper');
        const containerRect = items.firstElementChild.getBoundingClientRect();
        items.scrollLeft += containerRect.width;
        checkRanksScroll(items);
    });

    document.addEventListener('click:prev-rank-button', (e) => {
        const t = e.detail.target;
        const ranks = t.closest('.ranks');
        const items = ranks.querySelector('.ranks-wrapper');
        const containerRect = items.firstElementChild.getBoundingClientRect();
        items.scrollLeft -= containerRect.width;
        checkRanksScroll(items);
    });

    document.addEventListener('click:ranks-tab', (e) => {
        setTimeout(() => {
            document.querySelector('.ranks').scrollIntoView({ block: "center", behavior: "smooth" });
            const ranks = document.querySelector('.ranks');
            const selectedRank = ranks.querySelector('.rank-card.selected');
            const currentRank = ranks.querySelector('.rank-card.current');
            selectedRank.classList.remove('selected');
            currentRank.classList.add('selected');
            const wrapper = ranks.querySelector('.ranks-wrapper');
            const containerRect = wrapper.getBoundingClientRect();
            const elementRect = currentRank.getBoundingClientRect();
            const offset = elementRect.left - containerRect.left;
            const scrollOffset = offset - (containerRect.width / 2) + (elementRect.width / 2);
            wrapper.scrollLeft += scrollOffset;
            checkRanksControls();
            if (currentRank.classList.contains('rank-1')) {
                wrapper.scrollLeft = 0;
                setTimeout(()=> {
                    wrapper.scrollLeft = 0;
                }, 500);
            }
        }, 100);
    });

    document.addEventListener('click:next-reward-button', (e) => {
        const t = e.detail.target;
        const rewards = t.closest('.rewards');
        const items = rewards.querySelector('.items');
        const containerRect = items.getBoundingClientRect();
        items.scrollLeft += containerRect.width / 2;
    });

    document.addEventListener('click:prev-reward-button', (e) => {
        const t = e.detail.target;
        const rewards = t.closest('.rewards');
        const items = rewards.querySelector('.items');
        const containerRect = items.getBoundingClientRect();
        items.scrollLeft -= containerRect.width / 2;
    });

    function checkRanksControls() {
        const selected = document.querySelector('.ranks .rank-card.selected');
        if (selected.classList.contains('rank-1')) {
            document.querySelector('.ranks .prev-rank-button').classList.add('invisible');
        } else if(selected.classList.contains('rank-9')) {
            document.querySelector('.ranks .next-rank-button').classList.add('invisible');
        } else {
            document.querySelector('.ranks .prev-rank-button').classList.remove('invisible');
            document.querySelector('.ranks .next-rank-button').classList.remove('invisible');
        }

    }
})();

function checkRewardsScroll(s) {
    if (s.scrollLeft > s.scrollLeftMax - 20) {
        s.parentElement.querySelector('.next-reward-button').classList.add('invisible');
    } else if (s.scrollLeft < 20) {
        s.parentElement.querySelector('.prev-reward-button').classList.add('invisible');
    } else {
        s.parentElement.querySelector('.next-reward-button').classList.remove('invisible');
        s.parentElement.querySelector('.prev-reward-button').classList.remove('invisible');
    }
}

function checkRanksScroll(s) {
    if (s.scrollLeft > s.scrollLeftMax - 20) {
        s.parentElement.querySelector('.next-rank-button').classList.add('invisible');
    } else if (s.scrollLeft < 20) {
        s.parentElement.querySelector('.prev-rank-button').classList.add('invisible');
    } else {
        s.parentElement.querySelector('.next-rank-button').classList.remove('invisible');
        s.parentElement.querySelector('.prev-rank-button').classList.remove('invisible');
    }
}
