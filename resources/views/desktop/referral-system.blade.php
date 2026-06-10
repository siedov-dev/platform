<div class="ref-system">

    <div class="ref-system__header">
        <div class="width-limiter" style="display: flex; flex-direction: column;">

            <div class="ref-system__slogan">
                <p>{{ __('referralSystem.slogan_1') }}</p>
                <p>{{ __('referralSystem.slogan_3') }}</p>
            </div>

            <div class="ref-system__sub-slogan">{{ __('referralSystem.subSlogan') }}</div>


            @if(auth()->check())
                <div class="ref-system__start-btn" onclick="document.querySelector('.user__menu-link__referral').click()">{{ __('referralSystem.startNow') }}</div>
            @else
                <a href="{{ route('register.form', [], false) }}" data-ajax="modal" class="ref-system__start-btn">{{ __('referralSystem.startNow') }}</a>
            @endif

            <div class="ref-system__steps">
                <div class="ref-system__step">
                    <div class="ref-system__step-icon">
                        <p>1</p>
                    </div>
                    <div class="ref-system__step-text">
                        @if(auth()->check())
                            <div class="ref-system__step-link" onclick="document.querySelector('.user__menu-link__referral').click()">{{ __('referralSystem.startNow') }}</div>
                        @else
                            <a href="{{ route('register.form', [], false) }}" data-ajax="modal" class="ref-system__step-link">{{ __('referralSystem.registerNow') }}</a>
                        @endif
                        {{ __('referralSystem.andTakeLink') }}
                    </div>
                </div>

                <div class="ref-system__step">
                    <div class="ref-system__step-icon">
                        <p>2</p>
                    </div>
                    <div class="ref-system__step-text">
                        {{ __('referralSystem.inviteYourFriends') }}
                    </div>
                </div>

                <div class="ref-system__step">
                    <div class="ref-system__step-icon">
                        <p>3</p>
                    </div>
                    <div class="ref-system__step-text">
                        {{ __('referralSystem.earnMoney') }}
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="ref-system__info">
        <div class="width-limiter" style="display: flex; flex-direction: column;">

            <div class="ref-system__slogan">
                <p>{{ __('referralSystem.yourBenefit') }}</p>
            </div>

{{--                <div class="ref-system__info-title">{{ __('referralSystem.howMuchYouCanEarn') }}</div>--}}


            <div class="ref-system__benefits">
                <div class="ref-system__benefit_big">
                    <div class="ref-system__benefit-uah" id="ref-system__uah-bg">$</div>
                    <div class="ref-system__benefit-title" id="ref-system__uah-value">{{__('referralSystem.additionalPayments')}}</div>
                    <div class="ref-system__benefit-text">{{ __('referralSystem.additionalPaymentsText') }}</div>
                </div>
{{--                <script>--}}
{{--                    (() => {--}}
{{--                        let bg = document.getElementById('ref-system__uah-bg');--}}
{{--                        let value = document.getElementById('ref-system__uah-value');--}}

{{--                        let backgrounds = ["INR", 'UAH', "RUB"];--}}
{{--                        let values = ["10 inr", '5 uah', '10 rub'];--}}
{{--                        let i = 0;--}}

{{--                        setInterval(() => {--}}
{{--                            bg.textContent = backgrounds[i];--}}
{{--                            value.textContent = values[i];--}}
{{--                            i++;--}}
{{--                            if (i >= values.length) i = 0;--}}
{{--                        }, 2000);--}}
{{--                    })();--}}
{{--                </script>--}}
                <div class="ref-system__benefit_small">
                    <div class="ref-system__benefit-title">15%</div>
                    <div class="ref-system__benefit-text">{{ __('referralSystem.amountOfReferralsSpent') }}</div>
                    <img src="/img/referral/coins_bg_1.png" class="ref-system__benefit-image-1">
                </div>
                <div class="ref-system__benefit_small">
                    <div class="ref-system__benefit-title">{{ __('referralSystem.all') }}</div>
                    <div class="ref-system__benefit-text">{{ __('referralSystem.allPayouts') }}</div>
                    <img src="/img/referral/coins_bg_2.png" class="ref-system__benefit-image-2">
                </div>
            </div>

            <div class="ref-system__details">
                <div class="ref-system__step-icon">
                    <p>!</p>
                </div>
                <div class="ref-system__details-text">
                    {{ __('referralSystem.Details') }}
                </div>
                <div class="ref-system__side-circle">
                    <img src="/img/referral/sidecircle.svg">
                </div>
            </div>
        </div>
    </div>



{{--    <div class="ref-system__why">--}}
{{--        <div class="width-limiter" style="display: flex; flex-direction: column;">--}}

{{--            <div class="ref-system__info-title">{{ __('referralSystem.whyProfitable') }}</div>--}}


{{--            <div class="ref-system__features">--}}
{{--                <div class="ref-system__feature">--}}
{{--                    <img src="/img/referral/benefit_1.svg" class="ref-system__feature-image">--}}
{{--                    <div class="ref-system__feature-text">{{ __('referralSystem.payoutGuarantee') }}</div>--}}
{{--                </div>--}}
{{--                <div class="ref-system__feature">--}}
{{--                    <img src="/img/referral/benefit_2.svg" class="ref-system__feature-image">--}}
{{--                    <div class="ref-system__feature-text">{{ __('referralSystem.fastRegistration') }}</div>--}}
{{--                </div>--}}
{{--                <div class="ref-system__feature">--}}
{{--                    <img src="/img/referral/benefit_3.svg" class="ref-system__feature-image">--}}
{{--                    <div class="ref-system__feature-text">{{ __('referralSystem.nicePercents') }}</div>--}}
{{--                </div>--}}
{{--                <div class="ref-system__feature">--}}
{{--                    <img src="/img/referral/benefit_4.svg" class="ref-system__feature-image">--}}
{{--                    <div class="ref-system__feature-text">{{ __('referralSystem.growingProfit') }}</div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}



    <div class="ref-system__top mt-2">
        <div class="width-limiter">
            <div class="ref-system__slogan">
                <p>{{ __('referralSystem.topPartners') }}</p>
            </div>
{{--            <div class="ref-system__info-title">{{ __('referralSystem.topPartners') }}</div>--}}

{{--            <img src="/img/referral/glow_left.png" class="ref-system__glow-left">--}}
{{--            <img src="/img/referral/glow_right.png" class="ref-system__glow-right">--}}

            <table class="table mt-4" data-order='[[ 0, "asc" ]]' data-page-length='10'>
                <thead>
                    <tr>
                        <th>{{ __('referralSystem.place') }}</th>
                        <th>{{ __('referralSystem.partner') }}</th>
                        <th>{{ __('referralSystem.registrations') }}</th>
{{--                        <th>{{ __('referralSystem.deposit') }}</th>--}}
                        <th>{{ __('referralSystem.profit') }}</th>
                    </tr>
                </thead>
                @php
                    $position = ($data->currentPage() - 1) * $data->perPage();
                @endphp
                <tbody>
                @foreach($data as $d)
                    @php
//                    $currency = App\UserWallet::currencyName(empty($d->currency) ? \App\UserWallet::CURRENCY_RUB : $d->currency);
                    $position++;
                    @endphp
                    <tr>
                        <td class="table__rating-index">{{$position}}</td>
                        <td class="table__text_white">
                            <div class="flex ai-center">
                                <img src="/img/ui/icons/avatar.png" class="table__image">
                                <span class="table__text_white">{{\App\MUser::hideLogin(empty($d->login) ? 'Anonymus' : $d->login)}}</span>
                            </div>
                        </td>
                        <td class="table__text_white">{{ $d->referrals?? 0}}</td>
{{--                        <td class="table__text_white">--}}
{{--                            <div class="table__money">--}}
{{--                                <div class="currency-icon currency-icon_{{ strtolower($currency) }} table__icon"></div>--}}
{{--                                {!! \App\Helpers\UI::renderMoney(empty($d->amount) ? 0 : $d->amount, $currency) !!}--}}
{{--                            </div>--}}
{{--                        </td>--}}
                        <td class="table__text_white">
                            <div class="table__money">
                                <div class="currency-icon currency-icon_{{ strtolower($currency_name) }} table__icon"></div>
                                {!! \App\Helpers\UI::renderMoney(number_format(\App\Helpers\Currency::convert($d->profit, 'RUB', $currency_name), 2, '.', '')) !!}
                            </div>
{{--                            <div class="table__money">--}}
{{--                                <div class="currency-icon currency-icon_{{ strtolower($currency) }} table__icon"></div>--}}
{{--                                {!! \App\Helpers\UI::renderMoney(number_format(\App\Helpers\Currency::convert($d->profit, 'RUB', $currency_name), 2, '.', '')) !!}--}}
{{--                            </div>--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $data->render('pagination::bootstrap-4') }}
            <div class="ref-system__side-circle">
                <img src="/img/referral/sidecircle.svg">
            </div>
            <div class="ref-system__bottom-padding"></div>
        </div>
    </div>
</div>


