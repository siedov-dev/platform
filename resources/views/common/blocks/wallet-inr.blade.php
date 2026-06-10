@php
    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';
    $currencyLabel = $currencyCode;

        $money_k = 1;
        $min_payin = 100;
        $min_payout = 100;
        $max_payout = 0;

    $amount = auth()->check() ? auth()->user()->getWallet()->getBalanceStr() : 0;
    $max_payout = $amount;
    $has_bonus = \App\Models\Bonuses\KUserBonus::userHasCancelableBonuses($user);
    /** @var \App\Models\Bonuses\DepositBonusInterface $deposit_bonus */
    $deposit_bonus = \App\MUser::getAuth()->getFirstDepositBonus();
@endphp
<div class="wallet">
    <div class="wallet__title">{{ __('wallet.wallet') }} ({{ $currencyCode }})</div>
    <div class="wallet-money-available">
        <div>{{__('wallet.sum_available')}}:</div>
        <div class="wallet-amount">
            <span class="amount-int">{{ explode('.', $amount)[0] }}.</span>
            <span class="amount-rest">{{ explode('.', $amount)[1]??'00' }}</span>
            &nbsp;
            <span class="currency-icon currency-icon_{{mb_strtolower($currencyCode)}}"></span>
        </div>
    </div>

    <div class="tabs login-form__tabs">
        <div class="tabs__tab tabs__tab_active" data-target="#wallet__deposit">{{ __('wallet.deposit') }}</div>
        <div class="tabs__tab" data-target="#payout-wrap">{{ __('wallet.payout') }}</div>
    </div>


    <!-- Deposit -->
    <form action="{{ route('deposit', [], false) }}" method="POST" id="wallet__deposit">
        @csrf
        <div class="wallet__sub-title">{{ __('wallet.choosePaymentMethod') }}:</div>


        <div class="wallet__payment-systems_inr">
            @foreach(\App\Models\PaymentSystems::getPayinSystemsForCurrency(auth()->user()->currency) as $paymentSystem)
                <div class="wallet__paysystem"
                     @if($paymentSystem->isDisabled()) style="opacity: 0.3; pointer-events: none;" @endif
                     @if($paymentSystem->isTestMode()) style="background-color: rgba(255,209,37,0.73); border: rgba(255,209,37,0.73) 1px solid"
                     @endif
                     @if($paymentSystem->isBlocked())onclick="Spinner.show('.wallet');Spinner.hide('.wallet', '{{$paymentSystem->getBlockReason()}}', null, 3000);"
                     title="{{$paymentSystem->getBlockReason()}}"@endif>
                    <label class="wallet__payment-system"
                           @if($paymentSystem->isBlocked()) style="opacity: 0.3; pointer-events: none;" @endif>
                        <img src="{{ $paymentSystem->image_url }}" class="wallet__payment-logo">
                        <input class="payin-system-logo" type="radio" name="payment-method"
                               value="{{$paymentSystem->system_id.'/'.$paymentSystem->method_alias}}">
                    </label>
                    <div class="wallet__paysystem_name">{!!$paymentSystem->name!!}</div>
                    <div class="wallet__paysystem_limits">{{$paymentSystem->min_sum}}
                        - {{$paymentSystem->max_sum}} {{mb_strtolower($currencyCode)}}</div>
                    <div class="wallet__paysystem_commission">Commission: {{$paymentSystem->tax}}%</div>
                </div>
            @endforeach
        </div>

        <div class="deposit__fields" style="display: none">
            <div class="additional-payin-req">
            </div>
            @if($deposit_bonus !== null)
                <div class="input__label_modal mt-2">{{__('wallet.choose_bonus')}}</div>
                <div class="deposit-bonuses mb-2">
                    <label class="deposit-bonus selected">
                        <input type="radio" name="bonus"
                               value="{{$deposit_bonus->getBonusSystemId().'|'.$deposit_bonus->getId().'|'.$deposit_bonus->getRequiredSum()}}"
                               checked onchange="
                        for(let el of this.parentElement.parentElement.querySelectorAll('.deposit-bonus')){el.classList.remove('selected');}
                        this.parentElement.classList.add('selected');
                        this.closest('#wallet__deposit').querySelector('input[name=amount]').value={{$deposit_bonus->getRequiredSum()}};"
                               style="display: none">
                        <img src="/img/icons/gift.png">
                        <div class="bonus-name">{{$deposit_bonus->getName()}}</div>
                        <div class="deposit-sum">+{{$deposit_bonus->getRequiredSum()}}
                            &nbsp;{{$deposit_bonus->getCurrencyName()}}</div>
                        <div class="cabinet__bonus-info-btn" data-id="{{ $deposit_bonus->id }}"
                             @if($deposit_bonus->getDescriptionName() !== null) data-bonus-id="{{$deposit_bonus->getDescriptionName()}}" @endif>
                            <img src="/img/icons/info.png">
                        </div>
                    </label>
                    <label class="deposit-bonus">
                        <input type="radio" name="bonus" value=""
                               onchange="for(let el of this.parentElement.parentElement.querySelectorAll('.deposit-bonus')){el.classList.remove('selected');} this.parentElement.classList.add('selected');"
                               style="display: none">
                        <img src="/img/icons/no-bonus.png">
                        <div class="bonus-name">{{__('wallet.without_bonus')}}</div>
                    </label>
                </div>
                {{--                <div class="text-red wallet__sub-title">{{__('bonus.you_have_active_bonus')}} "{{$deposit_bonus->getName()}}".--}}
                {{--                    {{__('bonus.min_dep_to_activate')}}: {{$deposit_bonus->getActivateDepositAmount()}} {{$deposit_bonus->getCurrencyName()}}--}}
                {{--                </div>--}}
            @endif

            <div class="flex column w-100">
                <button type="submit" class="wallet__button w-100">{{ __('wallet.processPayment') }}</button>
                <button type="button" id="deposit-back-button"
                        class="wallet__button_text w-100">{{ __('wallet.change_method') }}</button>
            </div>

        </div>

    </form>


    <div id="payout-wrap" style="display: none">
        @if(auth()->user()->getWallet()->getLockedMoney() > 0)
            <div id="wallet__payout">
                <div class="wallet__no-payout-title">{{ __('wallet.payoutNotAvailable') }}</div>
                <div
                        class="wallet__no-payout-desc">{{ __('wallet.payoutRequirements', ['n' => auth()->user()->getWallet()->getLockedMoney() . ' ' . auth()->user()->getWallet()->getCurrencyName()]) }}
                </div>
                {{--            <div class="wallet__no-payout-providers">--}}
                {{--                @php--}}
                {{--                    $providers = \App\Http\Controllers\EmulatorController::getPayoutUnlockProviders();--}}
                {{--                @endphp--}}
                {{--                @foreach($providers as $provider)--}}
                {{--                    <a href="#" class="wallet__no-payout-provider">--}}
                {{--                        <img src="/img/providers/{{$provider['id']}}.png" class="wallet__no-payout-provider-img">--}}
                {{--                    </a>--}}
                {{--                @endforeach--}}
                {{--            </div>--}}
            </div>
        @elseif(auth()->user()->requiresVerification())
            <div id="wallet__payout">
                <div class="wallet__no-payout-title">{{__('wallet.payoutVerification')}}</div>
                @php
                    $data = \App\MUser::getAuth()->personalData();
                    if ($data !== null && $data->getStatus() === \App\Models\PersonalData::STATUS_DECLINED){
                        $message = $data->getMessage();
                    } else {
                        $message = empty(\App\MUser::getAuth()->verification_msg)? '' : \App\MUser::getAuth()->verification_msg;
                    }
                @endphp

                <div class="wallet__verification-required">{{ $message }}</div>

                <div class="wallet__verification-required">
                    <ul>
                        <li>{{__('wallet.payoutVerificationLink')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr1')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr2')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr3')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr4')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr5')}}</li>
                    </ul>
                    @if(\App\Helpers\Device::detect() === \App\Helpers\Device::PHONE)
                        <a href="/cabinet/settings" class="user-menu__link" onclick="Modal.hide('.wallet')"
                           style="background-color: #0B0D31">
                            <div class="user-menu__icon-wrap">
                                <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user-menu__icon"
                                     id="user-menu-settings">
                            </div>
                            {{ __('userMenu.settings') }}
                        </a>
                    @else
                        <a href="{{ route('cabinet.settings', [], false) }}" class="user__menu-link"
                           onclick="Modal.hide('.wallet')" style="background-color: #0B0D31">
                            <div class="user__menu-icon-wrap">
                                <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user__menu-icon">
                            </div>
                            {{ __('userMenu.settings') }}
                        </a>
                    @endif
                </div>
            </div>
        @elseif(auth()->user()->currency != \App\UserWallet::CURRENCY_INR && !auth()->user()->isVerified())
            <div id="wallet__payout">
                <div class="wallet__no-payout-title">{{__('wallet.payoutVerification')}}</div>
                <div class="wallet__verification-required">
                    <ul>
                        <li>{{__('wallet.payoutVerificationLink')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr1')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr2')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr3')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr4')}}</li>
                        <li>{{__('wallet.payoutVerificationDescr5')}}</li>
                    </ul>
                    @if(\App\Helpers\Device::detect() === \App\Helpers\Device::PHONE)
                        <a href="/cabinet/settings" class="user-menu__link" onclick="Modal.hide('.wallet')">
                            <div class="user-menu__icon-wrap">
                                <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user-menu__icon"
                                     id="user-menu-settings">
                            </div>
                            {{ __('userMenu.settings') }}
                        </a>
                    @else
                        <a href="{{ route('cabinet.settings', [], false) }}" class="user__menu-link"
                           onclick="Modal.hide('.wallet')">
                            <div class="user__menu-icon-wrap">
                                <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user__menu-icon">
                            </div>
                            {{ __('userMenu.settings') }}
                        </a>
                    @endif
                </div>
            </div>
        @else



            <div class="wallet__sub-title flex column" @if(!$has_bonus) style="display: none;" @endif>
                <span class="text-red">{{__('bonuses.payout_warn')}}</span>
                <button class="modal__button_outlined text-red mt-1"
                        onclick="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='' ">{{__('wallet.continue')}}</button>
            </div>
            <div @if($has_bonus) style="display: none;" @endif>
                <form action="{{ route('payout', [], false) }}" method="POST" id="wallet__payout">
                    @csrf
                    <div class="wallet__sub-title">{{ __('wallet.choosePaymentMethod') }}</div>
                    <div class="wallet__payment-systems_inr">
                        @foreach(\App\Models\PaymentSystems::getPayoutSystemsForCurrency(auth()->user()->currency) as $paymentSystem)
                            <div class="wallet__paysystem"
                                 @if($paymentSystem->isDisabled()) style="opacity: 0.3; pointer-events: none;" @endif
                                 @if($paymentSystem->isTestMode()) style="background-color: rgba(255,209,37,0.73); border: rgba(255,209,37,0.73) 1px solid"
                                 @endif
                                 @if($paymentSystem->isBlocked())onclick="Spinner.show('.wallet');Spinner.hide('.wallet', '{{$paymentSystem->getBlockReason()}}', null, 3000);"
                                 title="{{$paymentSystem->getBlockReason()}}"@endif>
                                <label class="wallet__payment-system"
                                       @if($paymentSystem->isBlocked()) style="opacity: 0.3; pointer-events: none;"
                                       @endif onclick="document.getElementById('wallet-payout-fields').style.display = 'block'">
                                    <img src="{{ $paymentSystem->image_url }}" class="wallet__payment-logo">
                                    <input class="payout-system-logo" type="radio" name="payment-method"
                                           value="{{$paymentSystem->system_id.'/'.$paymentSystem->method_alias}}">
                                </label>
                                <div class="wallet__paysystem_name">{!!$paymentSystem->name!!}</div>
                                <div class="wallet__paysystem_limits">{{$paymentSystem->min_sum}}
                                    - {{$paymentSystem->max_sum}} {{mb_strtolower($currencyCode)}}</div>
                                <div class="wallet__paysystem_commission">Commission: {{$paymentSystem->tax}}%</div>
                            </div>
                        @endforeach
                    </div>

                    <div id="wallet-payout-fields" style="display: none">
                        <label class="input__wrap_modal" style="position: relative">
                            <span class="input__label_modal">{{ __('wallet.choosePayoutSum') }}:</span>
                            <input type="number" step="any" class="input__field_modal" placeholder="1000" name="sum"
                                   style="padding-right: 2rem;">
                            <button class="max-amount-btn">{{__('wallet.maximum')}}</button>
                        </label>

                        <div class="additional-req">
                        </div>

                        <div class="flex jc-end">
                            <button type="button" id="payout-back-button"
                                    class="wallet__button_outlined mt-1 mr-2">{{ __('wallet.back') }}</button>
                            <button type="submit" class="wallet__button mt-1">{{ __('wallet.makePayout') }}</button>
                        </div>
                    </div>
                </form>
            </div>
    </div>
    @endif

    <div class="redirect_form">
    </div>
</div>

<script>
    (() => {
        let depositForm = document.getElementById('wallet__deposit');
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
            })
        }

        backButton.addEventListener('click', () => {
            let fields = depositForm.querySelector('.deposit__fields');
            let tiles = depositForm.querySelector('.wallet__payment-systems_inr');
            let title = depositForm.querySelector('.wallet__sub-title');

            tiles.style.display = 'grid';
            title.style.display = 'block';
            fields.style.display = 'none';

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

        if (outBackButton !== null) {
            outBackButton.addEventListener('click', () => {
                let fields = payoutForm.querySelector('#wallet-payout-fields');
                let tiles = payoutForm.querySelector('.wallet__payment-systems_inr');
                let title = payoutForm.querySelector('.wallet__sub-title');

                tiles.style.display = 'grid';
                title.style.display = 'block';
                fields.style.display = 'none';

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

        let payoutBtns = document.querySelectorAll('.payout-system-logo');

        async function payoutHandler(e) {
            try {
                await Spinner.show('.wallet');
                let method = this.value;
                let data = {
                    'method': method
                };
                let res = await network.post('{{action('WalletController@getPayoutRequisites')}}', data);
                payoutForm.querySelector('.additional-req').innerHTML = await res.text();
                Spinner.hide('.wallet');

                let fields = payoutForm.querySelector('#wallet-payout-fields');
                let tiles = payoutForm.querySelector('.wallet__payment-systems_inr');
                let title = payoutForm.querySelector('.wallet__sub-title');

                tiles.style.display = 'none';
                title.style.display = 'none';
                fields.style.display = 'block';

            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
            }
        }

        for (let btn of payoutBtns) {
            btn.addEventListener('change', payoutHandler);
        }

        let payinBtns = document.querySelectorAll('.payin-system-logo');

        async function depositHandler(e) {
            try {
                await Spinner.show('.wallet');
                let method = this.value;
                let data = {
                    'method': method
                };
                let res = await network.post('{{action('WalletController@getPayinRequisites')}}', data);
                depositForm.querySelector('.additional-payin-req').innerHTML = await res.text();
                Spinner.hide('.wallet');

                let fields = depositForm.querySelector('.deposit__fields');
                let tiles = depositForm.querySelector('.wallet__payment-systems_inr');
                let title = depositForm.querySelector('.wallet__sub-title');

                tiles.style.display = 'none';
                title.style.display = 'none';
                fields.style.display = 'block';

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
                if ("redirect_form" in json_resp) {
                    let redir_form = document.querySelector('.wallet .redirect_form');
                    if (redir_form) {
                        redir_form.innerHTML = json_resp.redirect_form;
                        redir_form.querySelector('form').submit();
                    }
                } else if ("modal_data" in json_resp) {
                    Modal.hide('.wallet');
                    Modal.show(json_resp.modal_data)
                } else if ("url" in json_resp) {
                    if ("in_frame" in json_resp) {
                        Modal.hide('.wallet');
                        Modal.show('<iframe src="' + json_resp.url + '" style="width: 90vw; height: 90vh; background: white; max-width: 600px;"></iframe>')
                    } else {
                        window.location = json_resp.url;
                    }
                } else {
                    Spinner.hide('.wallet', "Request error", null, 3000);
                }
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg, null, 3000);
            }
        });


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


            // if (requisites === null || requisites.length < 6) {
            //     Spinner.hide('.wallet', __('enterRequisites);
            //     return;
            // }

            try {
                let res = await network.post(payoutForm.getAttribute('action'), data);
                let successMsg = await res.text();
                Spinner.hide('.wallet', successMsg, null, 3000);
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg, null, 3000);
            }
        });
    })();
</script>
