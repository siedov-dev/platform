@php
    $currencyCode = auth()->check() ? auth()->user()->getCurrencyName() : 'UAH';
    $currencyLabel = $currencyCode;// === 'RUB' ? 'Руб' : 'Грн';


        $money_k = 1;
        $min_payin = 100;
        $min_payout = 100;

        if(auth()->check()){
            $min_payin = auth()->user()->getWallet()->getMinPayinSum();
            $min_payout = auth()->user()->getWallet()->getMinPayoutSum();

            $params = \App\Models\BonusCurrencyConfig::getBonusConfig(auth()->user()->currency);
            if($params === null) {
                \Illuminate\Support\Facades\Log::channel('stack')->warning('bonus currency params not found! Currency:'.$user->currency);
            } else {
                $money_k = $params->money_k;
            }
        }
    $minDeposit = $min_payin;
    $minPayout = $min_payout;
@endphp

<div class="wallet">
    <div class="wallet__title">{{ __('wallet.wallet') }} ({{ auth()->user()->getWallet()->getCurrencyName() }})</div>

    <div class="tabs login-form__tabs">
        <div class="tabs__tab tabs__tab_active" data-target="#wallet__deposit">{{ __('wallet.deposit') }}</div>
        <div class="tabs__tab" data-target="#wallet__payout">{{ __('wallet.payout') }}</div>
    </div>


    <!-- Deposit -->
    <form action="{{ route('deposit', [], false) }}" method="POST" id="wallet__deposit">
        @csrf
        <div class="wallet__sub-title">{{ __('wallet.choosePaymentMethod') }}:</div>

        @if (auth()->user()->currency == \App\UserWallet::CURRENCY_RUB || auth()->user()->currency == \App\UserWallet::CURRENCY_UAH)
            <div class="wallet__payment-systems_inr">
                @foreach(\App\Payout\Payin\Payin::getAllPayinSystems() as $paymentSystem)
                    @php($payin = new App\Payin\Payin(auth()->user()))
                    @if(isset($payin->getPayinSystem($paymentSystem)->getPayMethods()[auth()->user()->currency]))
                        @foreach($payin->getPayinSystem($paymentSystem)->getPayMethods()[auth()->user()->currency] as $method => $img)
                            @php($id = str_replace('.', '_', $paymentSystem.'/'.$method))
                            <div class="wallet__paysystem">
                                <label class="wallet__payment-system"
                                       onclick="document.getElementById('deposit-fields').style.display = 'block'">
                                    <img src="{{ $img }}" class="wallet__payment-logo">
                                    <input type="radio" name="payment-method" value="{{$paymentSystem.'/'.$method}}">
                                </label>

                                <div class="wallet__paysystem_name">{{ config('payment_systems.'.$id.'.name', $id) }}</div>
                                <div class="wallet__paysystem_limits">{{ config('payment_systems.'.$id.'.limits', '10 - 10000')}}</div>
                                <div class="wallet__paysystem_commission">
                                    Comission: {{ config('payment_systems.'.$id.'.tax', '0')}}%
                                </div>
                            </div>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @else
            <div class="wallet__payment-systems">
                @foreach(App\Payin\Payin::getAllPayinSystems() as $paymentSystem)
                    @php($payin = new App\Payin\Payin(auth()->user()))
                    @if(isset($payin->getPayinSystem($paymentSystem)->getPayMethods()[auth()->user()->currency]))
                        @foreach($payin->getPayinSystem($paymentSystem)->getPayMethods()[auth()->user()->currency] as $method => $img)
                            <label class="wallet__payment-system"
                                   onclick="document.getElementById('deposit-fields').style.display = 'block'">
                                <img src="{{ $img }}" class="wallet__payment-logo">
                                <input type="radio" name="payment-method" value="{{$paymentSystem.'/'.$method}}">
                            </label>
                        @endforeach
                    @endif
                @endforeach
            </div>
        @endif

        <div id="deposit-fields" style="display: none">
            <label id="login-form-email" class="input__wrap_modal">
                <span class="input__label_modal">{{ __('wallet.chooseDepositSum') }}:</span>
                <input type="number" min="{{ 100 * $money_k }}" class="input__field_modal" id="wallet__dep-amount"
                       placeholder="1000" name="amount">
            </label>

            <div class="wallet__fast-sums">
                @if (in_array(auth()->user()->getWallet()->getCurrencyName(), ['RUB', 'INR']))
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{500 * $money_k}}">{{500 * $money_k}} {{ $currencyLabel }}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{1000 * $money_k}}">{{1000 * $money_k}} {{ $currencyLabel }}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{1500 * $money_k}}">{{1500 * $money_k}} {{ $currencyLabel }}</div>
                @else
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{100 * $money_k}}">{{100 * $money_k}} {{ $currencyLabel }}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{500 * $money_k}}">{{500 * $money_k}} {{ $currencyLabel }}</div>
                    <div class="wallet__fast-sum"
                         onclick="document.getElementById('wallet__dep-amount').value = {{1000 * $money_k}}">{{1000 * $money_k}} {{ $currencyLabel }}</div>
                @endif
            </div>

            <div class="wallet__hint">{{ __('wallet.fee') }}: 0%</div>

            <button type="submit" class="wallet__button">{{ __('wallet.processPayment') }}</button>
        </div>
    </form>


    @if(auth()->user()->getWallet()->getLockedMoney() > 0)
        <div id="wallet__payout" style="display: none">
            <div class="wallet__no-payout-title">{{ __('wallet.payoutNotAvailable') }}</div>
            <div class="wallet__no-payout-desc">{{ __('wallet.payoutRequirements', ['n' => auth()->user()->getWallet()->getLockedMoney() . ' ' . auth()->user()->getWallet()->getCurrencyName()]) }}
            </div>
            <div class="wallet__no-payout-providers">
                @php($providers = \App\Http\Controllers\EmulatorController::getPayoutUnlockProviders())
                @foreach($providers as $provider)
                    <a href="#" class="wallet__no-payout-provider">
                        <img src="/img/providers/{{$provider['id']}}.png" class="wallet__no-payout-provider-img">
                    </a>
                @endforeach
            </div>
        </div>
    @elseif(!auth()->user()->isVerified())
        <div id="wallet__payout" style="display: none">
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

        <form action="{{ route('payout', [], false) }}" method="POST" id="wallet__payout" style="display: none">
            @csrf
            <div class="wallet__sub-title">{{ __('wallet.choosePaymentMethod') }}</div>
            <div class="wallet__payment-systems">
                    <?php
                    $hints = [
                        'Settle.RUB' => __('wallet.cardNumber'),
                        'Visa RUB' => __('wallet.cardNumber'),
                        'piastrix_rub' => __('wallet.walletNumberPiastrix'),
                        'FK.fkrub' => __('wallet.walletNumberFK'),
                        'FK.qiwi' => __('wallet.walletNumberQiwi'),
                        'FK.155' => __('wallet.walletNumberQiwi'),
                        'FK.ym' => __('wallet.walletNumberYandex'),
                        'FK.visarub' => __('wallet.cardNumber'),
                        'FK.tele2' => __('wallet.cardNumber'),
                        'FK.bl' => __('wallet.cardNumber'),
                        'FK.mts' => __('wallet.cardNumber'),
                        'FK.megafon' => __('wallet.cardNumber'),
                        'FK.advcash' => __('wallet.cardNumber'),
                        'Brpay.cardrub' => __('wallet.cardNumber'),
                        'Brpay.carduah' => __('wallet.cardNumber'),
                        'Swift.cardvisa' => __('wallet.cardNumber'),
                        'IKassa.cardvisa' => __('wallet.cardNumber'),
                    ];
                    ?>
                @foreach(App\Payout\Payout::getAllPayoutSystems() as $paymentSystem)
                    @php($payout = new App\Payout\Payout())
                    @if(isset($payout->getPayoutSystem($paymentSystem)->getPayMethods()[auth()->user()->currency]))
                        @foreach($payout->getPayoutSystem($paymentSystem)->getPayMethods()[auth()->user()->currency] as $method => $img)
                            <label class="wallet__payment-system"
                                   data-hint="{{ isset($hints[$method]) ? $hints[$method] : reset($hints) }}"
                                   onclick="document.getElementById('payout-fields').style.display = 'block'">
                                <img src="{{ $img }}" class="wallet__payment-logo">
                                <input type="radio" name="payment-method" value="{{$paymentSystem.'/'.$method}}">
                            </label>
                        @endforeach
                    @endif
                @endforeach
            </div>

            <div id="payout-fields" style="display: none">
                <label class="input__wrap_modal">
                    <span class="input__label_modal">{{ __('wallet.choosePayoutSum') }}:</span>
                    <input type="number" min="100" class="input__field_modal" placeholder="1000" name="sum">
                </label>

                <label class="input__wrap_modal mt-2">
                    <span class="input__label_modal wallet__requisites-label">{{ __('wallet.requisites') }}:</span>
                    <input type="text" class="input__field_modal" placeholder="Введите реквизиты..." name="cardNumber">
                </label>

                <button type="submit" class="wallet__button mt-2">{{ __('wallet.makePayout') }}</button>
            </div>
        </form>
    @endif

    <div class="redirect_form">
    </div>
</div>

<script>
    (() => {
        let depositForm = document.getElementById('wallet__deposit');
        let payoutForm = document.getElementById('wallet__payout');

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
            /* else if (parseInt(amount) < {{ $minDeposit }}) {
                Spinner.hide('.wallet', 'Сумма депозита должна быть не менее {{ $minDeposit }} руб.');
                return;
            }*/

            try {
                let res = await network.post(depositForm.getAttribute('action'), data);
                let json_resp = await res.json();
                if ("redirect_form" in json_resp) {
                    let redir_form = document.querySelector('.wallet .redirect_form');
                    if (redir_form) {
                        redir_form.innerHTML = json_resp.redirect_form;
                        redir_form.querySelector('form').submit();
                    }
                } else if ("url" in json_resp) {
                    window.location = json_resp.url;
                } else {
                    Spinner.hide('.wallet', "Request error");
                }
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
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
            /*else if (parseInt(sum) < {{ $minPayout }}) {
                Spinner.hide('.wallet', 'Сумма вывода должна быть не менее {{ $minPayout }} руб.');
                return;
            }*/


            if (requisites === null || requisites.length < 6) {
                Spinner.hide('.wallet', __('enterRequisites'));
                return;
            }

            try {
                let res = await network.post(payoutForm.getAttribute('action'), data);
                let successMsg = await res.text();
                Spinner.hide('.wallet', successMsg, null, 3000);
            } catch (errorMsg) {
                Spinner.hide('.wallet', errorMsg);
            }
        });
    })();
</script>
