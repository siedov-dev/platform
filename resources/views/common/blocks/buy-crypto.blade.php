@php
    //    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'RUB';
        $currencyCode = "USDT";
        $currencyLabel = $currencyCode;
        $currencyId = \App\UserWallet::CURRENCY_USDT;

        $money_k = 1;
        $min_payin = 100;

        $amount = auth()->check() ? auth()->user()->getWallet(\App\UserWallet::CURRENCY_USDT)->getBalanceStr() : 0;
        $has_bonus = \App\Models\Bonuses\KUserBonus::userHasCancelableBonuses($user);
        /** @var \App\Models\Bonuses\DepositBonusInterface $deposit_bonus */
        $deposit_bonus = \App\MUser::getAuth()->getFirstDepositBonus();
        $minDeposit = 10;
@endphp
<div class="wallet">
    <div class="wallet__title text-title">{{ __('wallet.buy') }} {{ $currencyCode }}</div>
    <form action="{{ route('deposit', [], false) }}" method="POST" id="wallet__deposit">
        @csrf
        <div class="user-wallet__pad">
            <label class="input__wrap_modal dep-amount-input">
                <span class="input__label_modal">{{ __('wallet.chooseDepositSum') }}:</span>
                <input type="number" class="input__field_modal mt-1" id="wallet__dep-amount" placeholder="0"
                       name="amount">
            </label>

            <div class="wallet__fast-sums">
                <div class="wallet__fast-sum"
                     onclick="document.getElementById('wallet__dep-amount').value = {{$minDeposit}}; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('input'));">{{$minDeposit}}</div>
                <div class="wallet__fast-sum"
                     onclick="document.getElementById('wallet__dep-amount').value = {{5 * $minDeposit}}; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('input'));">{{5 * $minDeposit}}</div>
                <div class="wallet__fast-sum"
                     onclick="document.getElementById('wallet__dep-amount').value = {{10 * $minDeposit}}; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('input'));">{{10 * $minDeposit}}</div>
                <div class="wallet__fast-sum"
                     onclick="document.getElementById('wallet__dep-amount').value = {{50 * $minDeposit}}; document.getElementById('wallet__dep-amount').dispatchEvent(new Event('input'));">{{50 * $minDeposit}}</div>
            </div>

            <div class="flex column deposit-amount-label mt-1 mb-1">
                <label class="text-subtitle text-small">{{__('wallet.depositAmount')}}</label>
                <div class="amount flex ai-center text-title mt-05">
                    <div class="currency-icon currency-icon_{{ strtolower($currencyLabel) }}"></div>
                    <div class="ml-05 text-bold" id="amount-label"></div>
                    <div class="ml-05 text-bold">{{ $currencyCode }}</div>
                </div>
            </div>

            <div class="wallet__sub-title text-subtitle">{{ __('wallet.choosePaymentMethod') }}:</div>
        </div>

        <div class="wallet__payment-systems_inr">
            @foreach(\App\Models\PaymentSystems::getBuyCryptoForCurrency($currencyId) as $paymentSystem)
                <label class="wallet__paysystem"
                       @if($paymentSystem->isDisabled()) style="opacity: 0.3; pointer-events: none;" @endif
                       @if($paymentSystem->isTestMode()) style="border: rgba(255,209,37,0.73) 1px solid" @endif
                       @if($paymentSystem->isBlocked())onclick="Spinner.show('.wallet');Spinner.hide('.wallet', '{{$paymentSystem->getBlockReason()}}', null, 3000);"
                       title="{{$paymentSystem->getBlockReason()}}"@endif>
                    <div class="wallet__payment-system"
                         @if($paymentSystem->isBlocked()) style="opacity: 0.3; pointer-events: none;" @endif>
                        <img src="{{ $paymentSystem->image_url }}" class="wallet__payment-logo">
                        <input class="payin-system-logo" type="radio" name="payment-method"
                               value="{{$paymentSystem->system_id.'/'.$paymentSystem->method_alias}}">
                    </div>
                    <div class="wallet__paysystem_name text-title">{!!$paymentSystem->name!!}</div>
                    <div class="flex">
                        <div class="wallet__paysystem_limits"><span
                                    class="min-sum">{{\App\Helpers\UI::moneyStr($paymentSystem->min_sum)}}</span> -
                            <span
                                    class="max-sum">{{\App\Helpers\UI::moneyStr($paymentSystem->max_sum)}}</span> {{mb_strtolower($currencyCode)}}
                        </div>
                        <div class="ml-1 wallet__paysystem_commission">Commission: {{$paymentSystem->tax}}%</div>
                    </div>
                </label>
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

            <div class="flex column w-100 mt-1">
                <button type="submit" class="wallet__button w-100">{{ __('wallet.processPayment') }}</button>
                <button type="button" id="deposit-back-button"
                        class="wallet__button_text w-100">{{ __('wallet.change_method') }}</button>
            </div>

        </div>

    </form>

    <div class="redirect_form">
    </div>
</div>

<script>
    (() => {
        let depositForm = document.getElementById('wallet__deposit');
        let backButton = document.getElementById('deposit-back-button');
        let outBackButton = document.getElementById('payout-back-button');
        let maxButton = document.querySelector('.max-amount-btn');
        let amountInput = depositForm.querySelector('#wallet__dep-amount');

        amountInput.addEventListener('input', (e) => {
            depositForm.querySelector('#amount-label').innerText = amountInput.value;
            document.querySelectorAll('.wallet__paysystem_limits .min-sum')
                .forEach(el => {
                    if (parseFloat(el.innerText) > parseFloat(amountInput.value)) {
                        el.classList.add('text-red')
                    } else {
                        el.classList.remove('text-red')
                    }
                })
        });

        backButton.addEventListener('click', () => {
            let fields = depositForm.querySelector('.deposit__fields');
            let tiles = depositForm.querySelector('.wallet__payment-systems_inr');
            let title = depositForm.querySelector('.wallet__sub-title');

            depositForm.classList.remove('additional-requisites');

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

        let payinBtns = document.querySelectorAll('.payin-system-logo');

        async function depositHandler() {
            try {
                await Spinner.show('.wallet');

                let minDep = parseFloat(this.parentElement.parentElement.querySelector('.min-sum').innerText);
                if (amountInput.value === "" || parseFloat(amountInput.value) < minDep) {
                    amountInput.value = minDep;
                }
                depositForm.querySelector('#amount-label').innerText = amountInput.value;

                depositForm.classList.add('additional-requisites');

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

                Modal.updatePosition(document.querySelector('.modal-wrap.shown'))

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

    })();
</script>
