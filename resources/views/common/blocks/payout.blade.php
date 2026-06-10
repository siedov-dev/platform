@php
    $user = \App\MUser::getAuth();
    $amount = $user?->getWallet()?->getBalance()?? 0;
        $user_currencies = auth()->user()?->getActiveCurrencies();
//        /** @var \App\Wallet[] $user_wallets */
//        $user_wallets = \App\MUser::getAuth()?->getActiveWallets(false, true)?? [];
//        $user_wallets_c = \App\MUser::getAuth()?->getActiveWallets(true)?? [];
        $active_currency = \App\MUser::getAuth()->getCurrencyName();

    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';
    $currencyLabel = $currencyCode;

        $money_k = 1;
        $min_payin = 100;
        $min_payout = 100;
        $max_payout = 0;

    $amount = auth()->check() ? auth()->user()->getWallet()->getBalance() : 0;
    $max_payout = $amount;
    $has_bonus = \App\Models\Bonuses\KUserBonus::userHasCancelableBonuses($user);
//    /** @var \App\Models\Bonuses\DepositBonusInterface $deposit_bonus */
//    $deposit_bonus = \App\MUser::getAuth()->getFirstDepositBonus();
    $payout_available = true;

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

$groups = [];
      $system_group = null;
      if ($group_id === null) {
          $groups = \App\Models\PaymentSystems::getPayoutGroups(\App\UserWallet::getCurrencyCode($currencyCode));
          $system_group = \App\Models\PaymentSystemGroup::NoGroup;
      } else {
          $system_group = \App\Models\PaymentSystemGroup::from($group_id);
      }

@endphp
<div class="user-wallet__pad relative  @if($group_id !== null) hidden @endif" id="user-wallet-pad">
    <div class="user-wallet__close-btn ml-1" onclick="document.dispatchEvent(new CustomEvent('hideWallet'))">
        <div>&times;</div>
    </div>
    <div class="user-wallet__header">
    </div>

    <div class="user-wallet__buttons mt-2 mb-1">
        <div onclick="document.dispatchEvent(new CustomEvent('showDeposit'))" class="deposit-btn">
            <img src="/img/ui/icons/deposit-icon.png">
            <div>{{__('wallet.deposit')}}</div>
        </div>
        <div onclick="document.dispatchEvent(new CustomEvent('showPayout'))" class="withraw-btn active">
            <img src="/img/ui/icons/payout-icon.png">
            <div>{{__('wallet.withdraw')}}</div>
        </div>
        <a href="{{route('slc.cashback', [], false)}}" class="flex disabled-btn" data-ajax="modal"
           data-popup-type="bottom-popup" onclick="wallet.hide()">
            <img src="/img/ui/icons/cashback-icon.png">
            <div>{{__('wallet.buy')}}</div>
        </a>
    </div>

</div>

<div class="wallet @if($group_id !== null) group-selected @endif">
    @if($group_id !== null)
        <div class="wallet-pad mb-05" id="current-group">
            <div class="flex jc-sb p-1">
                <div class="flex ai-center" onclick="wallet.showPayout()">
                    <div class="arrow-left grey mr-05"></div>
                    <div class="text-subtitle">{{__('wallet.back')}}</div>
                </div>
                <div class="flex ai-center label">
                    <img
                        src="/img/payment-systems/group/{{ \App\Models\PaymentSystemGroup::from($group_id)->name }}.png"
                        class="wallet__payment-logo mr-05 ">
                    <div
                        class="text-subtitle">{{__('wallet.group-'.\App\Models\PaymentSystemGroup::from($group_id)->name)}}</div>
                </div>
            </div>
        </div>
    @endif

    {{--    <div id="payout-wrap">--}}
    <div class="wallet__sub-title flex column p-1" @if(!$has_bonus) style="display: none;" @endif>
        <span class="text-red">{{__('bonuses.payout_warn')}}</span>
        <button class="button button_danger text-red mt-1"
                onclick="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='' ">{{__('wallet.continue')}}</button>
    </div>

    <div @if($has_bonus) style="display: none;" @else style="height: 100%" @endif>
        @if($user->isBlocked())
            <div class="payout-locked">
                <img src="/img/payment-systems/no-pay.png" class="pr-05">
                <div>{{ __('wallet.user-blocked') }}</div>
            </div>
            <div class="chat-panel">
                <div class="items">
                    <div class="flex column ai-center" onclick="wallet.hide();">
                        <div class="chat-item chat-menu-btn" onclick="chatMenuClick(1);">
                            <img class="chat-icon mb-1" src="/img/support/chat_b.png">
                        </div>
                        <div>{{__('chat-panel.chat1')}}</div>
                    </div>
                    <div class="flex column ai-center">
                        <button class="chat-item action-user-call-request button-shade" onclick="wallet.hide();">
                            <img src="/img/ui/icons/call_request/call.svg" alt="">
                        </button>
                        <div>{{__('user-calls.title')}}</div>
                    </div>
                </div>
            </div>
        @else

        <form action="{{ route('payout', [], false) }}" method="POST" id="wallet__payout">
            @csrf
            <label class="input__wrap_modal out-amount-input relative mb-1">
                <div class="flex jc-sb">
                    <div class="input__label_modal">{{ __('cabinet.sum') }}:</div>
                    <div class="input__label_modal">{{ __('wallet.balance') }}:
                        <span class="amount-int">{{ explode('.', $amount)[0] }}.</span>
                        <span class="amount-rest">{{ str_pad(explode('.', $amount)[1]??'00', 2, '0') }}</span>
                        <span class="">{{$currencyLabel}}</span>
                    </div>
                </div>
                <input id="wallet__out-amount" type="number" step="any" class="input__field_modal" placeholder="0"
                       name="sum" style="padding-right: 2rem;" required>
                <button class="max-amount-btn">{{__('wallet.maximum')}}</button>
                <div class="wallet__fast-sums hidden">
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__out-amount').value = {{$deposit_min[$currencyCode][0]??$deposit_min['RUB'][0]}}; document.getElementById('wallet__out-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][0]??$deposit_min['RUB'][0]}}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__out-amount').value = {{$deposit_min[$currencyCode][1]??$deposit_min['RUB'][1]}}; document.getElementById('wallet__out-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][1]??$deposit_min['RUB'][1]}}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__out-amount').value = {{$deposit_min[$currencyCode][2]??$deposit_min['RUB'][2]}}; document.getElementById('wallet__out-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][2]??$deposit_min['RUB'][2]}}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__out-amount').value = {{$deposit_min[$currencyCode][3]??$deposit_min['RUB'][3]}}; document.getElementById('wallet__out-amount').dispatchEvent(new Event('change'));">{{$deposit_min[$currencyCode][3]??$deposit_min['RUB'][3]}}</div>
                </div>
            </label>
                @if($user->getWallet()->getLockedMoney() > 0)
                @php
                    $payout_available = false;
                @endphp
                <div class="payout-locked">
                    <img src="/img/payment-systems/no-pay.png" class="pr-05">
                    <div>{{ __('wallet.payoutRequirements', ['n' => auth()->user()->getWallet()->getLockedMoney() . ' ' . auth()->user()->getWallet()->getCurrencyName()]) }}
                    </div>
                </div>
            @elseif(!$user->hasAnyVerifiedContact())
                @php
                    $payout_available = false;
                @endphp
                <div>
                    <div class="wallet__no-payout-title text-title">{{__('wallet.payoutConfirmContact')}}</div>
                    <div class="payout-locked" onclick="wallet.hide(); cabinetShowBlock('settings');">
                        <img src="/img/payment-systems/verify-icon.png" class="pr-05">
                        <div>{{__('wallet.payoutConfirm')}}</div>
                        <div class="flex">
                            <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user-menu__icon">
                        </div>
                    </div>
                </div>
            @endif

            <div class="wallet__payment-systems mt-1 @if(!$payout_available) disabled @endif">
                @foreach($groups as $group)
                    <div class="wallet__paysystem wallet_group" data-group="{{$group['value']}}">
                        <div class="wallet__payment-system">
                            <img src="/img/payment-systems/group/{{ $group['name'] }}.png"
                                 class="wallet__payment-logo" alt="">
                        </div>
                        <div class="flex column jc-center ai-center w-100 mt-05">
                            <div class="wallet__paysystem_limits"><span
                                    class="min-sum">{{floor($group['min_amount'])}}</span> - <span
                                    class="max-sum">{{floor($group['max_amount'])}}</span></div>
                            <div class="wallet__paysystem_commission">{{__('wallet.fee')}}: 0%</div>
                        </div>
                    </div>
                @endforeach
                @foreach(\App\Models\PaymentSystems::getPayoutSystemsForCurrency(\App\MUser::getAuth()->getCurrency(), false, $system_group) as $paymentSystem)
                    <label class="wallet__paysystem"
                           @if($paymentSystem->isDisabled()) style="opacity: 0.3; pointer-events: none;" @endif
                           @if($paymentSystem->isTestMode()) style="border: rgba(255,209,37,0.73) 1px dashed" @endif
                           @if($paymentSystem->isBlocked())onclick="Spinner.show('.wallet');Spinner.hide('.wallet', '{{$paymentSystem->getBlockReason()}}', null, 3000);"
                           title="{{$paymentSystem->getBlockReason()}}"@endif>
                        <div class="wallet__payment-system"
                             @if($paymentSystem->isBlocked()) style="opacity: 0.3; pointer-events: none;" @endif>
                            <img src="{{ $paymentSystem->image_url }}" class="wallet__payment-logo mt-05">
                            <input class="payin-system-logo" type="radio" name="payment-method"
                                   value="{{$paymentSystem->system_id.'/'.$paymentSystem->method_alias.'/'.$paymentSystem->id}}">
                        </div>
                        {{--                    <div class="wallet__paysystem_name text-title">{!!$paymentSystem->name!!}</div>--}}
                        <div class="flex column jc-center ai-center w-100 mt-05">
                            <div class="wallet__paysystem_limits">{{__('wallet.from')}}&nbsp;<span
                                    class="min-sum">{{floor($paymentSystem->min_sum)}}</span> <span
                                    class="max-sum hidden">{{floor($paymentSystem->max_sum)}}</span></div>
                            <div class="wallet__paysystem_commission">{{__('wallet.fee')}}: 0%</div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div id="wallet-payout-fields">
                <div class="additional-req">
                </div>

                <div class="flex column w-100 mt-1">
                    <button type="button" class="wallet__button mt-1"
                            onclick="Modal.showBottom(document.getElementById('payout-confirm-modal')); return false;">{{ __('wallet.makePayout') }}</button>
                    <button type="submit" id="payout-submit-btn"
                            class="wallet__button mt-1 hidden">{{ __('wallet.makePayout') }}</button>
                    <button type="button" id="payout-back-button"
                            class="wallet__button_text w-100">{{ __('wallet.change_method') }}</button>
                </div>
            </div>
        </form>
        @endif
    </div>
</div>

<div class="redirect_form">
</div>
{{--</div>--}}


<div id="payout-confirm-modal" class="payout-confirm-modal flex column ai-center pad no-click-hide"
     style="display: none">
    <img src="/img/ui/icons/atm-cash-icon.svg" alt="ATM cash">
    <p class="modal-title mt-2">{{__('wallet.confirm_payout_title')}}</p>
    <div class="pad-surface p-1 flex column ai-center mt-1">
        <p class="mt-1 mb-1">{{__('wallet.ach_ad')}}</p>
        <a class="menu__item hidden-menu__first-link achievements content-link mb-1 w-100"
           href="{{route('slc.achievements', [], false)}}" onclick="wallet.hide()">
            <span class="wrap-img"><img src="/images/achievements-icon.png" alt=""></span>
            {{__('menu.achievements')}}
        </a>
    </div>
    <div class="flex jc-sb mt-1 w-100">
        <button class="button button_grey grow mr-1"
                onclick="Modal.hide(this); document.getElementById('payout-submit-btn').click()">{{__('wallet.payout_btn')}}</button>
        <a href="{{route('landing', [], false)}}" class="button button_green grow"
           onclick="Modal.hide(this)">{{__('wallet.play')}}</a>
    </div>
</div>

<div id="payout-success-modal" class="payout-success-modal flex column ai-center pad no-click-hide"
     style="display: none">
    <img src="/img/ui/icons/atm-cash-icon.svg" alt="ATM cash">
    <p class="modal-title mt-2">{{__('wallet.app_payout_success_t')}}</p>
    <p class="modal-title" id="payout-success-amount"></p>
    <p class="modal-title">{{__('wallet.app_payout_success_b')}}</p>
    <div class="pad-surface p-1 flex column ai-center mt-1">
        <p class="mt-1 mb-1">{{__('wallet.payout_transaction_title')}}</p>
        <a href="{{ route('cabinet.transactions', [], false) }}"
           class="cabinet__menu-link transactions-link hidden-menu__first-link"
           onclick="wallet.hide(); Modal.hide(this);">
            <div class="cabinet__menu-icon-wrap">
                <img src="/img/icons/menu/stats.png">
            </div>
            {{ __('userMenu.transactionsHistory') }}
        </a>
    </div>
    <div class="flex jc-sb mt-1 w-100">
        <a href="{{ route('cabinet.transactions.cancel') }}" class="button button_green cancel-payout-button grow"
           data-id="" data-confirm="{{__('cabinet.cancelPaymentQ')}}">
            {{ __('wallet.cancel_payout') }}
        </a>
    </div>
</div>

<script>
    (() => {
        let payoutForm = document.getElementById('wallet__payout');
        let backButton = document.getElementById('deposit-back-button');
        let outBackButton = document.getElementById('payout-back-button');
        let maxButton = document.querySelector('.max-amount-btn');
        let maxPayout = {{$max_payout}};

        if (maxButton !== null) {
            maxButton.addEventListener('click', function (e) {
                e.preventDefault();
                if (document.getElementById('max-payout-value') !== null) {
                    let newMax = parseFloat(document.getElementById('max-payout-value').innerText);
                    if (newMax < maxPayout) maxPayout = newMax;
                }
                this.parentElement.querySelector('.input__field_modal').value = maxPayout;
                this.parentElement.querySelector('.input__field_modal').dispatchEvent(new Event('input'));
            })
        }

        let amountInput = payoutForm.querySelector('#wallet__out-amount');

        amountInput.addEventListener('input', (e) => {
            highlightAmount(amountInput.value);
            recalc_crypto()
        });

        amountInput.addEventListener('change', (e) => {
            checkSumLimits();
            recalc_crypto()
        });

        if (outBackButton !== null) {
            outBackButton.addEventListener('click', () => {

                payoutForm.classList.remove('additional-requisites');

                let systems = document.querySelector('.wallet .wallet__payment-systems');
                systems.classList.remove('system-selected');
                document.querySelector('.wallet .wallet__payment-systems .wallet__paysystem.active').classList.remove('active');

                let active = payoutForm.querySelector('.wallet__payment-system_active');
                if (active !== null) active.classList.remove('wallet__payment-system_active');

                let radioButtons = payoutForm.querySelectorAll('input[type="radio"]');
                for (let i = 0; i < radioButtons.length; i++) {
                    if (radioButtons[i].checked) {
                        radioButtons[i].checked = false;
                        return;
                    }
                }
            });
        }

        let payoutBtns = document.querySelectorAll('.wallet__paysystem');

        let payoutGroups = document.querySelectorAll('.wallet_group');

        for (let group of payoutGroups) {
            group.addEventListener('click', (e) => {
                wallet.showPayout(group.dataset.group);
            });
        }

        async function payoutHandler(e) {
            try {
                let t = e.target;
                let system = t.closest('.wallet__paysystem');
                system.classList.add('active');
                system.closest('.wallet__payment-systems').classList.add('system-selected');

                // await Spinner.show('.wallet');
                let method = this.querySelector("input").value;
                let data = {
                    'method': method
                };

                checkSumLimits();

                // payoutForm.querySelector('#amount-label').innerText = amountInput.value;

                payoutForm.classList.add('additional-requisites');

                let res = await network.post('{{action('WalletController@getPayoutRequisites')}}', data);
                payoutForm.querySelector('.additional-req').innerHTML = await res.text();
                runScripts(payoutForm.querySelector('.additional-req'));

                let fields = payoutForm.querySelector('#wallet-payout-fields');
                let tiles = payoutForm.querySelector('.wallet__payment-systems_inr');
                let title = payoutForm.querySelector('.wallet__sub-title');

                recalc_crypto()
                select_crypto_wallet()

                title.style.display = 'none';
                // fields.style.display = 'block';

            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
            }
        }


        function recalc_crypto() {
            let cryptoForm = document.getElementById('wallet_payout_s')
            let cryptoFormCalc = document.getElementById('crypto-wallet-amount')

            if (cryptoForm) {
                let cryptoFormCalc = document.getElementById('crypto-wallet-amount')
                cryptoFormCalc.value = (amountInput.value * cryptoForm.getAttribute('data-course')).toFixed(4);
            }
        }

        function select_crypto_wallet() {
            let cryptoForm = document.getElementById('wallet_payout_s')
            let cryptoFormSelect = document.getElementById('crypto-wallet-select-value')
            let cryptoFormInput = document.getElementById('crypto-wallet-input-value')
            let cryptoWalletAutoSave = document.getElementById('crypto-wallet-select-auto-save')

            if (cryptoForm && cryptoFormSelect && cryptoFormSelect.value === '') {
                cryptoFormSelect.style.display = 'none'
                cryptoFormSelect.style.display = 'cardNumberReserv'
                cryptoFormInput.style.display = 'block'
                cryptoFormInput.name = 'cardNumber'
                cryptoWalletAutoSave.style.display = 'block'
            }
        }

        payoutForm.addEventListener('change', function (event) {
            if (event.target.id === 'crypto-wallet-select-value') {
                select_crypto_wallet()
            }
        });

        for (let btn of payoutBtns) {
            btn.addEventListener('change', payoutHandler);
        }

        payoutForm.addEventListener('submit', async e => {
            e.preventDefault();
            let data = new FormData(payoutForm);
            let paymentMethod = data.get('payment-method');
            let requisites = data.get('cardNumber');
            let sum = data.get('sum');

            await Spinner.show('.wallet');
            await delay(150);

            if (paymentMethod === null) {
                Spinner.hide('.wallet', __('chosePayMethod'));
                return;
            }

            if (sum === '') {
                Spinner.hide('.wallet', __('choseAmount'));
                return;
            }

            try {
                let res = await network.post(payoutForm.getAttribute('action'), data);
                let successMsg = await res.text();
                let successForm = document.getElementById('payout-success-modal');
                let respData = JSON.parse(successMsg);
                successForm.querySelector('#payout-success-amount').innerText = respData.amount + " " + respData.curr;
                successForm.querySelector('.cancel-payout-button').dataset.id = respData.tr_id;
                Spinner.hide('.wallet');
                wallet.hide();
                Modal.showBottom(successForm);
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg, null, 3000);
            }
        });

        function checkSumLimits() {
            // payoutForm.querySelector('#amount-label').innerText = amountInput.value;

            let activeSystem = document.querySelector('.wallet__payment-systems .wallet__paysystem.active');
            if (activeSystem !== null) {
                const minSum = parseFloat(activeSystem.querySelector('.min-sum').innerText);
                const maxSum = parseFloat(activeSystem.querySelector('.max-sum').innerText);
                if (parseFloat(amountInput.value) < minSum) amountInput.value = minSum;
                if (parseFloat(amountInput.value) > maxSum) amountInput.value = maxSum;
            }
            // payoutForm.querySelector('#amount-label').innerText = amountInput.value;
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

    })();
</script>
