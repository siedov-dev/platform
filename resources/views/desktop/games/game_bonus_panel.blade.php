<script>
    (() => {
        setTimeout(function () {
            toggleFullscreen(document.getElementById('game-wrapper'), true)
        }, 1000);

        let ti = setInterval(async () => {

            try {
                let res = await network.get('/GetVegasBonus');
                let data = await res.json();
                let wager = document.getElementById('play__wager');
                if (wager === null) {
                    clearInterval(ti);
                } else if (data.status == 2) {
                    if (data.modal !== undefined && data.modal !== null) {
                        clearInterval(ti);
                        Modal.show(data.modal, true, async () => {
                            let modal = document.querySelector('.modal');
                            while (modal !== null) {
                                modal.remove();
                                modal = document.querySelector('.modal');
                            }
                            location.href = '{{route('cabinet.bonuses', [], true)}}';
                        });
                        // modal.closeButton.classList.add('promo-modal__close-button');
                    } else {
                        setTimeout(function () {
                            document.querySelector('.play__button-close').click();
                        }, 500);
                    }
                } else {
                    wager.style.display = 'flex';
                    wager.innerHTML = "<div>"+data.show_status+"</div>";
                }
            } catch (e) {
                clearInterval(ti);
            }

        }, 1000);


    })();
</script>
