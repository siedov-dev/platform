@php
    /** @var \App\MUser $user */
    $user = auth()->user();
    /** @var \App\Models\UserReferrals\UserReferral $referral */
@endphp
<div id="cabinet-position" data-position="partnership"></div>
<div class="cabinet__prtn">
    <div class="cabinet__pad">
        <div class="flex jc-sb">

            <div class="flex column">
                <a href="{{route('slc.page-info', ['page' => 'referral'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
                <div class="cabinet__prtn-ref">
                    <div class="cabinet__prtn-ref-title ml-3">{{ __('referral.myReferralLink') }}:</div>
                    <div class="cabinet__prtn-ref-link"><span id="cabinet__prtn-url">{{ request()->getSchemeAndHttpHost()}}?uref={{$referral['ref_code']}}</span>
                        <div class="cabinet__prtn-ref-copy" onclick="copyCode('#cabinet__prtn-url'); Toast.show('{{ __('cabinet.refCodeCopied') }}')">
                            <img src="/img/ui/icons/cabinet/copy.svg">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cabinet__prtn-digests">
            <div class="cabinet__prtn-digest-item">
                <img src="/img/ui/icons/cabinet/referals.svg" class="cabinet__prtn-digest-icon"/>
                <div class="cabinet__prtn-digest-name">{{ __('referral.referralsTotal') }}:</div>
                <div class="cabinet__prtn-digest-value">{{ $r_users['total_users']?? 0 }}</div>
            </div>

            <div class="cabinet__prtn-digest-item">
                <img src="/img/ui/icons/cabinet/add-referal.svg" class="cabinet__prtn-digest-icon"/>
                <div class="cabinet__prtn-digest-name">{{ __('referral.activeWithDeposit') }}:</div>
                <div class="cabinet__prtn-digest-value">{{ $users_with_deposit }}</div>
            </div>

            <div class="cabinet__prtn-digest-item">
                <img src="/img/ui/icons/cabinet/referal-percent.svg" class="cabinet__prtn-digest-icon"/>
                <div class="cabinet__prtn-digest-name">{{ __('referral.profitPerDay') }}:</div>
                <div class="cabinet__prtn-digest-value">{{ $ref_params['ggr_prc'] }}%</div>
            </div>

            <div class="cabinet__prtn-digest-item">
                <img src="/img/ui/icons/cabinet/referal-percent.svg" class="cabinet__prtn-digest-icon"/>
                <div class="cabinet__prtn-digest-name">{{ __('referral.fd_profit') }}:</div>
                <div class="cabinet__prtn-digest-value">{{ $ref_params['fd_sum'] }} {{$currency}}</div>
            </div>


            @foreach($achievements as $id => $achievement)
                <div class="cabinet__prtn-digest-item">
                    <img src="/img/ui/icons/cabinet/referal-percent.svg" class="cabinet__prtn-digest-icon"/>
                    <div class="cabinet__prtn-digest-name">{{ __('referral.ach_reward') }} "{{$achievement->getTitle()}}":</div>
                    <div class="cabinet__prtn-digest-value">{{ $achievement_rewards[$id]?? 0 }} {{$currency}}</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Money -->
    <div class="cabinet__money">
        <div class="cabinet__money_real cabinet__pad">
            <div class="relative flex jc-fe mr-2">
                <a href="{{route('slc.page-info', ['page' => 'referral-realmoney'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
            </div>
            <div class="cabinet__pad-title">{{ __('referral.realMoney') }}:</div>

            <div class="cabinet__money-wallets">
                @foreach($user->getActiveCurrencies() as $currency_code)
                    @php
                        $currency_name = \App\UserWallet::currencyName($currency_code);
                    @endphp
                    <div class="cabinet__money-wallet">
                        <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower($currency_name)}}"></div>
                        {!! \App\Helpers\UI::renderMoney(auth()->user()->getWallet($currency_code)->getReferralMoney(), $currency_name) !!}
                    </div>
                @endforeach
            </div>

            <button type="button" class="cabinet__prtn-submit-btn" data-url="{{ action('ReferralController@transferMoney', [], false) }}">{{ __('referral.withdrawMoney') }}</button>
        </div>
        <div class="cabinet__money_bonus cabinet__pad">
            <div class="relative flex jc-fe mr-2">
                <a href="{{route('slc.page-info', ['page' => 'referral-ggrmoney'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
            </div>
            <div class="cabinet__pad-title">{{ __('referral.bonusMoney') }}:</div>

            <div class="cabinet__money-wallets">
                @foreach($user->getActiveCurrencies() as $currency_code)
                    @php
                        $currency_name = \App\UserWallet::currencyName($currency_code);
                    @endphp
                    <div class="cabinet__money-wallet">
                        <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower($currency_name)}}"></div>
                        {!! \App\Helpers\UI::renderMoney(auth()->user()->getWallet($currency_code)->getBonusMoney(), $currency_name) !!}
                    </div>
                @endforeach
            </div>

        </div>
    </div>


    <div class="tabs mt-2">
        <div class="tabs__tab tabs__tab_active" data-target="#cabinet__ref-history">{{ __('referral.referralsHistory') }}</div>
        <div class="tabs__tab" data-target="#cabinet__ref-list">{{ __('referral.referralsList') }}</div>
    </div>

    <!-- Referrals history -->
    <div id="cabinet__ref-history" class="scroll-hidden">
        @include('desktop.cabinet.ref-history', ['ref_history' => $ref_history, 'events_paginator' => $events_paginator])
    </div>

    <!-- Referrals list -->
    <div id="cabinet__ref-list" style="display: none" class="scroll-hidden">
        @include('desktop.cabinet.ref-users', ['ref_users' => $ref_users, 'users_paginator' => $users_paginator])
    </div>
</div>
