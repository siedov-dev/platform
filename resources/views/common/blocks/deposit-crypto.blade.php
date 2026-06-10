<div class="user-wallet__pad relative @if($group_id !== null) hidden @endif" id="user-wallet-pad">
    <div class="user-wallet__header">
    </div>

    <div class="user-wallet__buttons mt-2 mb-1">
        <div onclick="wallet.showDeposit({{$start_amount}}, null,
                    document.querySelector('#wallet__deposit #without_bonus') !== null && document.querySelector('#wallet__deposit #without_bonus').checked)"
             class="deposit-btn active">
            <img src="/img/ui/icons/deposit-icon.png" alt="">
            <div>{{__('wallet.deposit')}}</div>
        </div>
        <div onclick="document.dispatchEvent(new CustomEvent('showPayout'))" class="withraw-btn">
            <img src="/img/ui/icons/payout-icon.png" alt="">
            <div>{{__('wallet.withdraw')}}</div>
        </div>
        <a href="{{route('slc.cashback', [], false)}}" class="flex disabled-btn" data-ajax="modal"
           data-popup-type="bottom-popup" onclick="wallet.hide()">
            <img src="/img/ui/icons/cashback-icon.png" alt="">
            <div>{{__('wallet.buy')}}</div>
        </a>
    </div>
</div>

<div class="wallet @if($group_id !== null) group-selected @endif" data-group="4">
    @if($group_id !== null)
        <div class="wallet-pad mb-05" id="current-group">
            <div class="flex jc-sb p-1">
                <div class="flex ai-center cursor-pointer" onclick="wallet.showDeposit({{$start_amount}}, null,
                    document.querySelector('#wallet__deposit #without_bonus') !== null && document.querySelector('#wallet__deposit #without_bonus').checked)">
                    <div class="arrow-left grey mr-05"></div>
                    <div class="text-subtitle">{{__('wallet.back')}}</div>
                </div>
                <div class="flex ai-center">
                    <img
                            src="/img/payment-systems/group/{{ \App\Models\PaymentSystemGroup::from($group_id)->name }}.png"
                            class="wallet__payment-logo mr-05">
                </div>
            </div>
        </div>
    @endif
    <div class="wallet-pad mb-05 hidden" id="current-system">
        <div class="flex jc-sb p-1">
            <div class="flex ai-center" id="deposit-back-button">
                <div class="arrow-left grey mr-05"></div>
                <div class="text-subtitle">{{__('wallet.back')}}</div>
            </div>
            <div class="flex ai-center label">
                <img src="" class="wallet__payment-logo mr-05 ">
                <div class="text-subtitle system-name"></div>
            </div>
        </div>
    </div>

    <form action="{{ route('deposit', [], false) }}" method="POST" id="wallet__deposit" class="additional-requisites">
        @csrf
        @if($dep_bonus)
            <input type="hidden" name="bonus_id" value="{{$dep_bonus->getId()}}">
            <div class="dep-bonus-info mb-1">
                <div class="flex jc-sb ai-center">
                    <div class="title">Бонус на депозит:</div>
                    <div class="flex ai-center">
                        <span class="switcher-label mr-05">Без бонуса</span>
                        <label class="switch big-switch green-switch"
                               onclick="if (this.firstElementChild.checked) {this.closest('.dep-bonus-info').querySelector('.dep-bonus-wrapper').classList.add('hidden');} else {this.closest('.dep-bonus-info').querySelector('.dep-bonus-wrapper').classList.remove('hidden');}">
                            <input type="checkbox" name="without_bonus" @if($without_bonus) checked @endif>
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <a class="dep-bonus-wrapper mt-1 @if($without_bonus) hidden @endif"
                   href="{{route('bonus.popup-info', ['id' => $dep_bonus->getId(), 'sub_id' => $dep_bonus->getSubId()], false)}}"
                   data-ajax="modal" data-popup-type="bottom-popup">
                    <div class="dep-bonus-img">
                        <img src="{{$dep_bonus->getImgUrl()}}" alt="">
                    </div>
                    <div class="dep-bonus-content">
                        <div>
                            <div class="bonus-title">{{ $dep_bonus->getTitle() }}</div>
                            <div class="dep-amount">Депозит
                                от {{ $dep_bonus->getRequiredSum() }} {{$dep_bonus->getCurrencyName()}}</div>
                        </div>
                        <div class="flex">
                            <div class="bonus-timer">
                                <span class="time-estimated" data-target_time="{{$dep_bonus->getExpirationDate()}}"
                                      data-separator=":">{{$dep_bonus->getExpirationDate()}}</span>
                            </div>
                            <div class="info-icon-white ml-05"></div>
                        </div>
                    </div>
                    <div class="check-icon">
                        <img src="/img/ui/icons/check-green2.svg" alt="check">
                    </div>
                </a>
            </div>
        @endif
        <div class="wallet__payment-systems">

            @foreach(\App\Models\PaymentSystems::getPayinSystemsForCurrency(\App\MUser::getAuth()->getCurrency(), false, \App\Models\PaymentSystemGroup::Crypto) as $paymentSystem)
                <label class="wallet__paysystem"
                       @if($paymentSystem->isDisabled()) style="opacity: 0.3; pointer-events: none;" @endif
                       @if($paymentSystem->isTestMode()) style="border: rgba(255,209,37,0.73) 1px dashed" @endif
                       @if($paymentSystem->isBlocked())onclick="Spinner.show('.wallet');Spinner.hide('.wallet', '{{$paymentSystem->getBlockReason()}}', null, 3000);"
                       title="{{$paymentSystem->getBlockReason()}}"@endif>

                    <div class="wallet__payment-system"
                         @if($paymentSystem->isBlocked()) style="opacity: 0.3; pointer-events: none;" @endif>
                        <img src="{{ $paymentSystem->image_url }}" class="wallet__payment-logo">

                        @php
                            $nn = explode(' ', $paymentSystem->name);
                            $name = $nn[0] ?? '';
                            $sub_name = $nn[1] ?? false;
                        @endphp

                        <div class="wallet__paysystem_name text-title">
                            <span>{{ $name }}</span>
                            @if($sub_name)
                                <small>{{ $sub_name }}</small>
                            @endif
                        </div>
                        <input class="payin-system-logo" type="radio" name="payment-method"
                               value="{{ $paymentSystem->system_id . '/' . $paymentSystem->method_alias .'/'. $paymentSystem->id}}">
                    </div>

                    <div class="wallet__paysystem_limits w-100">
                        {{ __('wallet.from') }} <span
                                class="min-sum">{{ floor($paymentSystem->min_sum) }}</span> {{ auth()->user()->getCurrencyName() }}
                    </div>

                </label>
            @endforeach

        </div>
        <div class="wallet__sub-title text-subtitle hidden"></div>

        <div class="wallet__payment-system-calc additional-payin-req mt-1" id="wallet__deposit-calc"></div>

    </form>
</div>

<script>
    (() => {

        let depositForm = document.getElementById('wallet__deposit');
        let payinBtns = document.querySelectorAll('.wallet__paysystem');
        let backButton = document.getElementById('deposit-back-button');

        /*let amountInput = depositForm.getElementById('crypto-wallet-amount-get');

        amountInput.addEventListener('change', (e) => {
            console.log('d')
        });*/

        async function depositHandler(e) {
            try {

                let data = new FormData(depositForm);
                // let paymentMethod = data.get('payment-method');

                let t = e.target;
                let system = t.closest('.wallet__paysystem');
                system.classList.add('active');
                system.closest('.wallet__payment-systems').classList.add('system-selected');

                await Spinner.show('.wallet');

                let groupHeader = document.querySelector('.wallet #current-group');
                if (groupHeader !== null) groupHeader.classList.add('hidden');
                let systemHeader = document.querySelector('.wallet #current-system');
                if (systemHeader !== null) {
                    systemHeader.querySelector('img').src = system.querySelector('img').src;
                    systemHeader.querySelector('.system-name').innerText = system.querySelector('.wallet__paysystem_name').innerText;
                    systemHeader.classList.remove('hidden');
                }

                let method = this.querySelector("input").value;
                let dataF = {
                    'method': method
                };
                let res = await network.post('{{action('WalletController@getPayinRequisites')}}', dataF);
                depositForm.querySelector('.additional-payin-req').innerHTML = await res.text();
                Spinner.hide('.wallet');

                let title = depositForm.querySelector('.wallet__sub-title');
                title.style.display = 'none';

                depositForm.classList.add('additional-requisites');

            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
                console.log(errorMsg);
            }
        }

        for (let btn of payinBtns) {
            btn.addEventListener('change', depositHandler);
        }

        backButton.addEventListener('click', (e) => {
            let groupHeader = document.querySelector('.wallet #current-group');
            if (groupHeader !== null) groupHeader.classList.remove('hidden');
            let systemHeader = document.querySelector('.wallet #current-system');
            if (systemHeader !== null) {
                systemHeader.classList.add('hidden');
            }
            @if($group_id === null)
            document.getElementById("user-wallet-pad").classList.remove('hidden');
            @endif

            let title = depositForm.querySelector('.wallet__sub-title');

            depositForm.classList.remove('additional-requisites');

            title.style.display = 'block';

            let systems = document.querySelector('.wallet .wallet__payment-systems');
            systems.classList.remove('system-selected');
            document.querySelector('.wallet .wallet__payment-systems .wallet__paysystem.active').classList.remove('active');

            let active = depositForm.querySelector('.wallet__payment-system_active');
            if (active !== null) active.classList.remove('wallet__payment-system_active');

            let radioButtons = depositForm.querySelectorAll('input[type="radio"]');
            for (let i = 0; i < radioButtons.length; i++) {
                if (radioButtons[i].checked) {
                    radioButtons[i].checked = false;
                    return;
                }
            }
        });

    })();

</script>
