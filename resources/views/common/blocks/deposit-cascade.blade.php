@php
    $amount = $user?->getWallet()?->getBalance()?? 0;
    $user_currencies = $user?->getActiveCurrencies();
    $active_currency = \App\MUser::getAuth()->getCurrencyName();

    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';
    $currencyLabel = $currencyCode;

    $money_k = 1;
    $min_payin = 100;

    $deposit_min = [
        'RUB' => [300, 500, 1500, 3000],
        'UAH' => [100, 200, 1000, 2000],
        'INR' => [300, 500, 1500, 3000],
        'USD' => [10, 50, 100, 200],
        'USDT' => [10, 50, 100, 200],
        'EUR' => [11, 50, 100, 200],
        'KZT' => [3000, 5000, 15000, 30000],
        'UZS' => [30000, 50000, 150000, 300000],
    ];
    $deposit_steps = [
        'RUB' => 500,
        'UAH' => 200,
        'INR' => 500,
        'USD' => 10,
        'USDT' => 10,
        'EUR' => 10,
        'KZT' => 5000,
        'UZS' => 50000,
    ];
    $minDeposit = 10;


    $device = \App\Helpers\Device::current();
@endphp

<div class="relative">
    <div class="user-wallet__close-btn ml-1" onclick="wallet.hide();">
        <div>&times;</div>
    </div>
</div>
<div class="user-wallet__pad relative @if($group_id !== null) hidden @endif" id="user-wallet-pad">
    <div class="user-wallet__header">
    </div>

    <div class="user-wallet__buttons mt-2 mb-1">
        <div onclick="wallet.showDeposit({{$start_amount}}, null,
            document.querySelector('#wallet__deposit #without_bonus') !== null && document.querySelector('#wallet__deposit #without_bonus').checked)"
             class="deposit-btn active">
            <img src="/img/ui/icons/deposit-icon.png">
            <div>{{__('wallet.deposit')}}</div>
        </div>
        <div onclick="document.dispatchEvent(new CustomEvent('showPayout'))" class="withraw-btn">
            <img src="/img/ui/icons/payout-icon.png">
            <div>{{__('wallet.withdraw')}}</div>
        </div>
        <a href="{{route('slc.cashback', [], false)}}" class="flex disabled-btn" data-ajax="modal" data-popup-type="bottom-popup" onclick="wallet.hide()">
            <img src="/img/ui/icons/cashback-icon.png">
            <div>{{__('wallet.buy')}}</div>
        </a>
    </div>

</div>

<div class="wallet @if($cascade_id !== null) group-selected @endif">
    @if($cascade_id !== null)
        <div class="wallet-pad mb-05" id="current-group">
            <div class="flex jc-sb p-1">
                <div class="flex ai-center" onclick="wallet.showDeposit({{$start_amount}}, null,
                    document.querySelector('#wallet__deposit #without_bonus') !== null && document.querySelector('#wallet__deposit #without_bonus').checked)">
                    <div class="arrow-left grey mr-05"></div>
                    <div class="text-subtitle">{{__('wallet.back')}}</div>
                </div>
                <div class="flex ai-center label">
                    <img src="/img/payment-systems/group/{{ \App\Models\PaymentSystemGroup::from($group_id)->name }}.png"
                        class="wallet__payment-logo mr-05">
                    <div class="text-subtitle">{{__('wallet.group-'.\App\Models\PaymentSystemGroup::from($group_id)->name)}}</div>
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
    <form action="{{ route('cascade.deposit', [], false) }}" method="POST" id="wallet__deposit">
        @csrf
        @if($dep_bonus)
            <input type="hidden" name="bonus_id" value="{{$dep_bonus->getId()}}">
            <div class="dep-bonus-info mb-1">
                <div class="flex jc-sb ai-center">
                    <div class="title">Бонус на депозит:</div>
                    <div class="flex ai-center">
                        <span class="switcher-label mr-05">Без бонуса</span>
                        <label class="switch big-switch green-switch"
                               onclick="
                               if (this.firstElementChild.checked) {
                                   this.closest('.dep-bonus-info').querySelector('.dep-bonus-wrapper').classList.add('hidden');
                               } else {
                                   this.closest('.dep-bonus-info').querySelector('.dep-bonus-wrapper').classList.remove('hidden');
                               }">
                            <input type="checkbox" name="without_bonus" id="without_bonus"
                                   @if($without_bonus) checked @endif>
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
                            <div class="dep-amount">{{__('bonus.dep-from')}} {{ $dep_bonus->getRequiredSum() }} {{$dep_bonus->getCurrencyName()}}</div>
                        </div>
                        <div class="flex">
                            <div class="bonus-timer">
                                <span class="time-estimated" data-target_time="{{$dep_bonus->getExpirationDate()}}" data-separator=":">
                                    {{$dep_bonus->getExpirationDate()}}
                                </span>
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
        <div class="wallet__payment-systems mb-1">
            @foreach($groups as $group)
                <div class="wallet__paysystem wallet_group" data-amount="{{$start_amount}}" data-group="{{$group['value']}}">
                    <div class="wallet__payment-system">
                        <img src="/img/payment-systems/group/{{ $group['name'] }}.png"
                             class="wallet__payment-logo" alt="">
                    </div>
                    <div class="flex column jc-center ai-center w-100 mt-05">
                        <div class="wallet__paysystem_limits">
                            <span class="min-sum">{{floor($group['min_amount'])}}</span> -
                            <span class="max-sum">{{floor($group['max_amount'])}}</span>
                        </div>
                        <div class="wallet__paysystem_commission">{{__('wallet.fee')}}: 0%</div>
                    </div>
                </div>
            @endforeach
            @foreach($cascades as $cascade)
                <label class="wallet__paysystem">
                    <div class="wallet__payment-system" >
                        <img src="{{ $cascade['img'] }}" class="wallet__payment-logo mt-05">
                        <input class="payin-system-logo" type="radio" name="payment-method" value="{{$cascade['id']}}">
                    </div>
                    <div class="wallet__paysystem_name text-title hidden">{!!$cascade['name']!!}</div>
                    <div class="flex column jc-center ai-center w-100 mt-05">
                        <div class="wallet__paysystem_limits"><span
                                class="min-sum">{{floor($cascade['min_deposit'])}}</span> - <span
                                class="max-sum">{{floor($cascade['max_deposit'])}}</span></div>
                        <div class="wallet__paysystem_commission">{{__('wallet.fee')}}: 0%</div>
                    </div>
                </label>
            @endforeach
            @if($user->isTestUser())
                <div class="wallet__paysystem wallet_group" data-amount="{{$start_amount}}" data-group="{{0}}" style="border: rgba(255,209,37,0.73) 1px dashed">
                    <div class="wallet__payment-system">
                        <img src="/img/payment-systems/group/switch.png"
                             class="wallet__payment-logo" alt="">
                    </div>
                    <div class="flex column jc-center ai-center w-100 mt-05">
                        <div class="wallet__paysystem_commission">Все методы</div>
                    </div>
                </div>
            @endif

        </div>

        <div class="additional-payin-req">
        </div>

        <div class="dep-amount-sums">
            <label class="input__wrap_modal dep-amount-input">
                <div class="flex jc-sb">
                    <div class="input__label_modal">{{ __('cabinet.sum') }}:</div>
                    <div class="input__label_modal min-max"></div>
                </div>
                <input type="number" class="input__field_modal mt-1" id="wallet__dep-amount" placeholder="0"
                       name="amount" value="{{$start_amount}}">
            </label>

            <div class="wallet__fast-sums" data-step-amount="{{$deposit_steps[$currencyCode]??$deposit_min['RUB']}}">
                <div class="wallet__fast-sum" data-amount="{{$deposit_min[$currencyCode][0]??$deposit_min['RUB'][0]}}"
                     onclick="document.getElementById('wallet__dep-amount').value = this.dataset.amount; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][0]??$deposit_min['RUB'][0]}}</div>
                <div class="wallet__fast-sum" data-amount="{{$deposit_min[$currencyCode][1]??$deposit_min['RUB'][1]}}"
                     onclick="document.getElementById('wallet__dep-amount').value = this.dataset.amount; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][1]??$deposit_min['RUB'][1]}}</div>
                <div class="wallet__fast-sum" data-amount="{{$deposit_min[$currencyCode][2]??$deposit_min['RUB'][2]}}"
                     onclick="document.getElementById('wallet__dep-amount').value = this.dataset.amount; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][2]??$deposit_min['RUB'][2]}}</div>
                <div class="wallet__fast-sum" data-amount="{{$deposit_min[$currencyCode][3]??$deposit_min['RUB'][3]}}"
                     onclick="document.getElementById('wallet__dep-amount').value = this.dataset.amount; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][3]??$deposit_min['RUB'][3]}}</div>
            </div>

            <div class="deposit__fields">
                <div class="flex column w-100">
                    <button type="submit" class="wallet__button w-100 disabled"
                            onclick="if(this.classList.contains('disabled')){ shortMessage.show('{{__('wallet.choosePaymentMethod')}}'); return false; }">
                        {{ __('wallet.processPayment') }}
                    </button>
                </div>

            </div>

            <div class="flex column deposit-amount-label">
                <label class="text-subtitle text-small">{{__('wallet.depositAmount')}}</label>
                <div class="amount flex ai-center text-title mt-05">
                    <div class="currency-icon currency-icon_{{ strtolower(auth()->user()?->getCurrencyName()) }}"></div>
                    <div class="ml-05 text-bold" id="amount-label"></div>
                    <div class="ml-05 text-bold">{{ $currencyCode }}</div>
                </div>
            </div>

            <div class="wallet__sub-title text-subtitle hidden"></div>
        </div>


    </form>

</div>
<div class="redirect_form hidden">
</div>
<a class="hidden" href="" target="_blank" id="redirect-dep-link"></a>

<script>
    (() => {

        let depositForm = document.getElementById('wallet__deposit');
        let backButton = document.getElementById('deposit-back-button');
        let outBackButton = document.getElementById('payout-back-button');
        let maxButton = document.querySelector('.max-amount-btn');
        let amountInput = depositForm.querySelector('#wallet__dep-amount');

        amountInput.addEventListener('input', (e) => {
            depositForm.querySelector('#amount-label').innerText = amountInput.value;
            highlightAmount(amountInput.value);
        });

        amountInput.addEventListener('change', (e) => {
            checkSumLimits();
        });

        function checkSumLimits() {
            depositForm.querySelector('#amount-label').innerText = amountInput.value;

            let activeSystem = document.querySelector('.wallet__payment-systems .wallet__paysystem.active');
            if (activeSystem !== null) {
                const minSum = parseFloat(activeSystem.querySelector('.min-sum').innerText);
                const maxSum = parseFloat(activeSystem.querySelector('.max-sum').innerText);
                if (parseFloat(amountInput.value) < minSum) amountInput.value = minSum;
                if (parseFloat(amountInput.value) > maxSum) amountInput.value = maxSum;
            }
            depositForm.querySelector('#amount-label').innerText = amountInput.value;
            highlightAmount(amountInput.value);
        }

        function highlightAmount(amount) {
            document.querySelectorAll('.wallet__paysystem_limits .min-sum')
                .forEach(el => {
                    if (parseFloat(el.innerText) > parseFloat(amount)) {
                        el.classList.add('text-red')
                    } else {
                        el.classList.remove('text-red')
                    }
                });
            document.querySelectorAll('.wallet__paysystem_limits .max-sum')
                .forEach(el => {
                    if (parseFloat(el.innerText) < parseFloat(amount)) {
                        el.classList.add('text-red')
                    } else {
                        el.classList.remove('text-red')
                    }
                });
        }

        backButton.addEventListener('click', (e) => {
            let groupHeader = document.querySelector('.wallet #current-group');
            if (groupHeader !== null) groupHeader.classList.remove('hidden');
            let systemHeader = document.querySelector('.wallet #current-system');
            if (systemHeader !== null) {
                systemHeader.classList.add('hidden');
            }

            document.querySelector('.dep-amount-sums .min-max').innerText = "";
            @if($group_id === null)
            document.getElementById("user-wallet-pad").classList.remove('hidden');
            @endif

            let fields = depositForm.querySelector('.deposit__fields');
            let tiles = depositForm.querySelector('.wallet__payment-systems');
            let title = depositForm.querySelector('.wallet__sub-title');

            depositForm.classList.remove('additional-requisites');

            // tiles.style.display = 'grid';
            title.style.display = 'block';
            fields.querySelector('.wallet__button').classList.add('disabled');

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

        let payinBtns = document.querySelectorAll('.wallet__paysystem');
        let payinGroups = document.querySelectorAll('.wallet_group');

        for (let group of payinGroups) {
            group.addEventListener('click', (e) => {
                const withoutBonus = document.querySelector('#wallet__deposit #without_bonus');
                wallet.showDeposit(parseFloat(group.dataset.amount), group.dataset.group, withoutBonus !== null && withoutBonus.checked);
            });
        }

        async function depositHandler(e) {
            try {
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
                document.getElementById("user-wallet-pad").classList.add('hidden');


                document.querySelector('.dep-amount-sums .min-max').innerText = "Min "
                    + this.querySelector('.min-sum').innerText
                    + "/Max " + this.querySelector('.max-sum').innerText;


                let minDep = parseFloat(this.querySelector('.min-sum').innerText);
                let maxDep = parseFloat(this.querySelector('.max-sum').innerText);


                let fastSums = document.querySelector('.wallet__fast-sums');
                let stepAmount = 0;
                if (fastSums !== null) {
                    stepAmount = parseFloat(fastSums.dataset.stepAmount);
                    fastSums.firstElementChild.dataset.amount = minDep.toString();
                    fastSums.firstElementChild.innerText = minDep.toString();
                    fastSums.children[1].dataset.amount = (minDep + stepAmount).toString();
                    fastSums.children[1].innerText = (minDep + stepAmount).toString();
                    fastSums.children[2].dataset.amount = (minDep + stepAmount * 2).toString();
                    fastSums.children[2].innerText = (minDep + stepAmount * 2).toString();
                    fastSums.lastElementChild.dataset.amount = maxDep.toString();
                    fastSums.lastElementChild.innerText = maxDep.toString();
                }

                if (amountInput.value === "" || parseFloat(amountInput.value) === 0) {
                    amountInput.value = minDep + stepAmount;
                }

                if (amountInput.value === "" || parseFloat(amountInput.value) < minDep) {
                    amountInput.value = minDep;
                }

                highlightAmount(amountInput.value);

                depositForm.querySelector('#amount-label').innerText = amountInput.value;

                let method = this.querySelector("input").value;
                let data = {
                    'method': method
                };
                let res = await network.post('{{route('cascade.requisites', [], false)}}', data);
                let respData = await res.text();
                depositForm.querySelector('.additional-payin-req').innerHTML = respData;
                runScripts(depositForm.querySelector('.additional-payin-req'));
                Spinner.hide('.wallet');

                let fields = depositForm.querySelector('.deposit__fields');
                let tiles = depositForm.querySelector('.wallet__payment-systems');
                let title = depositForm.querySelector('.wallet__sub-title');

                title.style.display = 'none';
                if (res.headers.has("blocked-to")) {
                    depositForm.querySelector('.dep-amount-sums').classList.add('hidden');
                } else {
                    depositForm.querySelector('.dep-amount-sums').classList.remove('hidden');
                }
                fields.querySelector('.wallet__button').classList.remove('disabled');

                depositForm.classList.add('additional-requisites');

            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
            }
        }

        for (let btn of payinBtns) {
            btn.addEventListener('change', depositHandler);
        }

        depositForm.addEventListener('submit', async e => {
            e.preventDefault();
            let data = new FormData(depositForm);
            let paymentMethod = data.get('payment-method');
            let amount = data.get('amount');

            await Spinner.show('.wallet');
            await delay(250);

            if (paymentMethod === null) {
                Spinner.hide('.wallet', __('chosePayMethod'));
                return;
            }

            if (amount === '') {
                Spinner.hide('.wallet', __('choseAmount'));
                return;
            }

            try {
                let res = await network.post(depositForm.getAttribute('action'), data);
                let json_resp = await res.json();
                if ("err" in json_resp) {
                    Spinner.hide('.wallet', json_resp.err, null, 3000);
                    setTimeout(() => {
                        if ("next_sum" in json_resp) {
                            amountInput.value = json_resp.next_sum;
                            depositForm.querySelector('#amount-label').innerText = json_resp.next_sum;
                            amountInput.classList.add('flashed-red');
                        }
                        setTimeout(() => {
                            amountInput.classList.remove('flashed-red');
                        }, 1000)
                    }, 3000)
                    return;
                }
                if ("redirect_form" in json_resp) {
                    let redir_form = document.querySelector('.user-wallet .redirect_form');
                    if (redir_form) {
                        redir_form.innerHTML = json_resp.redirect_form;
                        let form = redir_form.querySelector('form');

                        if (form !== null) {
                            @if($device->browser() !== 'Safari')
                            form.setAttribute("target", "_blank");
                            @endif
                            form.submit();
                        }
                        Spinner.hide('.wallet', "", null, 3000);
                        wallet.hide();
                    } else {
                        Spinner.hide('.wallet', "Request error", null, 3000);
                        return;
                    }
                } else if ("modal_data" in json_resp) {
                    document.dispatchEvent(new CustomEvent("hideWallet"));
                    Modal.show(json_resp.modal_data)
                } else if ("direct" in json_resp) {
                    window.location = json_resp.url;
                } else if ("by_link" in json_resp) {
                    let url = json_resp.url;
                    const a = document.createElement('a');
                    a.href = url;
                    // a.target = '_blank';
                    a.rel = 'opener'; // Явно разрешаем opener, иногда помогает с Referer
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    wallet.hide();
                } else if ("url" in json_resp) {
                    if ("in_frame" in json_resp) {
                        document.dispatchEvent(new CustomEvent("hideWallet"));
                        Modal.show('<iframe src="' + json_resp.url + '" style="width: 90vw; height: 90vh; background: white; max-width: 600px;"></iframe>')
                    } else {
                        //all in new tab
                        let url = json_resp.url;
                        setTimeout(() => {
                            let windowReference = window.open(url, "_blank");
                            if (windowReference !== null) {
                                windowReference.focus();
                            } else {
                                window.location = url;
                            }
                        })
                        Spinner.hide('.wallet', "", null, 3000);
                        wallet.hide();
                    }
                } else {
                    Spinner.hide('.wallet', "Request error", null, 3000);
                }
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg, null, 3000);
            }
        });

    })();
</script>
