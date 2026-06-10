<div id="cashback-shoot" class="cashback-shoot no-click-hide relative">
    <div class="bg-image">
    </div>
    <div class="info-flag">
        <div class="flex column cashback-info">
            <div class="relative">
                <a href="{{route('slc.page-info', ['page' => 'current-cashback'], false)}}" class="info-icon popup-link" data-ajax="modal" style=""></a>
            </div>
            <h5 class="mt-1">{{__('cashback-plus.current-cashback')}}</h5>
            <p>{{$m_user->getRank()->getWeeklyCashBackPrc()}}% + {{ number_format($m_user->getRank()->getWeeklyCashBackPrc() *  $m_user->getValue('cashback_plus', 0) / 100, 1, '.', '')}}%</p>
        </div>
    </div>
    <div class="health-bar" data-state="{{$state}}">
        <img src="/img/cashback/health0.png">
        <img src="/img/cashback/health1.png">
        <img src="/img/cashback/health2.png">
    </div>
    <div class="title flex">
        <h1 class="mt-1 ml-1 relative">{{__('cashback-plus.title')}}
            <a href="{{route('slc.page-info', ['page' => 'cashback-plus'], false)}}" class="info-icon popup-link" data-ajax="modal" style="top:0; right: -2rem;"></a>
        </h1>
    </div>
    <div class="animation-scene">
        <lottie-player id="cannon" mode="normal" src="/img/cashback/anims/Cannon.json"></lottie-player>
        <lottie-player id="black_ball" class="ball" mode="normal" src="/img/cashback/anims/BlackBall.json"></lottie-player>
        <lottie-player id="gold_ball" class="ball" mode="normal" src="/img/cashback/anims/GoldBall.json"></lottie-player>
        <lottie-player id="barrel-start" class="barrel" mode="normal" src="{{$barrel_state}}"></lottie-player>
        <lottie-player id="barrel" class="barrel hidden" mode="normal" src=""></lottie-player>
        <div class="cannonball golden_ball">
            <img src="/img/cashback/GoldCannonball.svg">
            <div class="ball-amount gold_balls_left">{{$gold_balls}}</div>
        </div>
        <div class="cannonball black_ball">
            <img src="/img/cashback/Cannonball.svg">
            <div class="ball-amount black_balls_left">{{$black_balls}}</div>
        </div>
    </div>
    <div class="win-text invisible">{{__('cashback-plus.win')}}</div>
    <div class="win-item invisible">
        <div class="shine-item">
            <div class="slc__popup-icon">
                <img src="/img/cashback/cashback_plus_piastres/cb_10.svg" alt="win-item">
            </div>
            <img class="shine-img" src="/img/achievements/item_light.png">
        </div>
    </div>

    <div class="bottom-controls invisible">
        <div class="button button_orange continue">
            {{__('cashback-plus.continue')}}
        </div>
        <a class="content-link button button_shop button_left-icon ml-2" onclick="Modal.hide(this.closest('.modal'));" href="{{route('slc.user-items', [], false)}}">
            <img src="/images/shop-icon.png" alt="">{{__('menu.inventory')}}
        </a>
    </div>
</div>
<script>
    (() => {
        let inProgress = false;
        let isSuccess = false;
        let cashbackWin = false;
        let currentBarrel = "";
        let bstate = 0;
        let winText = document.querySelector("#cashback-shoot .win-text");
        let winItem = document.querySelector("#cashback-shoot .win-item");
        let bottomControls = document.querySelector("#cashback-shoot .bottom-controls");
        let btnContinue = document.querySelector("#cashback-shoot .bottom-controls .continue");

        let cannon = document.getElementById("cannon");

        let BlackBallAnim = document.getElementById("black_ball");
        let GoldBallAnim = document.getElementById("gold_ball");

        let blackBall = document.querySelector(".animation-scene .black_ball");
        let goldBall = document.querySelector(".animation-scene .golden_ball");

        let barrelStart = document.getElementById("barrel-start");
        let barrel = document.getElementById("barrel");

        btnContinue.addEventListener('click', () => {
            barrel.classList.add('hidden');
            barrelStart.classList.remove('hidden');
            resetAnims();
            winText.classList.add('invisible');
            winItem.classList.add('invisible');
            bottomControls.classList.add('invisible');
        });

        cannon.addEventListener("complete", () => {
                barrelStart.classList.add('hidden');
                barrel.classList.remove('hidden');
                barrel.play();
                barrelStart.load(currentBarrel);
                document.querySelector('.cashback-shoot .health-bar').dataset.state = bstate;
        });

        barrel.addEventListener("complete", () => {
            if (cashbackWin) {
                winText.classList.remove('invisible');
                winItem.classList.remove('invisible');
                bottomControls.classList.remove('invisible');
            } else {
                barrel.classList.add('hidden');
                barrelStart.classList.remove('hidden');
            }
            inProgress = false;
        });

        blackBall.addEventListener('click',  async () => {
            shot("black")
        });

        goldBall.addEventListener('click', () => {
            shot("gold")
        });

        async function shot(ball) {
            if (inProgress) return;
            inProgress = true;
            resetAnims();

            let resp;
            //get server data
            try {
                resp = await network.post('{{route('slc.cashback.shoot', [], false)}}', {ball_type: ball});
            } catch (e) {
                inProgress = false;
                return;
            }
            let data = await resp.json();
            // console.log('resp:', data);

            if (data['error'] !== undefined) {
                shortMessage.show(data['error']);
                inProgress = false;
                return;
            }
            document.querySelector('.animation-scene .gold_balls_left').innerText = data['gold_balls_left'];
            document.querySelector('.animation-scene .black_balls_left').innerText = data['black_balls_left'];

            currentBarrel = data['barrel1'];
            barrel.load(data['barrel2']);
            isSuccess = data['success'];
            cashbackWin = data['won'];
            bstate = data['state'];

            if (ball === 'gold') {
                GoldBallAnim.play();
            } else {
                BlackBallAnim.play();
            }
            await cannon.play();

            winText.innerText = data['win_text'];
            winItem.querySelector('.slc__popup-icon img').src = data['win_image'];
        }

        function resetAnims() {
            barrel.stop()
            barrelStart.stop()
            BlackBallAnim.stop();
            GoldBallAnim.stop();
            cannon.stop();
        }

    })()
</script>
