@php
    /** @var \App\MUser $user */
    $user = auth()->user();
@endphp
<div id="cabinet-position" data-position="partnership"></div>
<div class="cabinet__prtn">
    <div class="cabinet__pad">
        <div class="flex column jc-sb">

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
{{--                <div class="cabinet__prtn-ref">--}}
{{--                    <div class="cabinet__prtn-ref-title">{{ __('referral.myReferralBonusLink') }}:</div>--}}
{{--                    <div class="cabinet__prtn-ref-link"><span id="cabinet__prtn-b-url">{{ request()->getSchemeAndHttpHost()}}?uref={{$referral->getBonusRefCode()}}</span>--}}
{{--                        <div class="cabinet__prtn-ref-copy" onclick="copyCode('#cabinet__prtn-b-url'); Toast.show('{{ __('cabinet.refCodeCopied') }}')">--}}
{{--                            <img src="/img/ui/icons/cabinet/copy.svg">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
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

    <!-- Money -->
    <div class="cabinet__money">
        <div class="cabinet__money_real cabinet__pad">
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
            <div class="cabinet__pad-title">{{ __('referral.bonusMoney') }}:</div>

            <div class="cabinet__money-wallets">
                @foreach($user->getActiveCurrencies() as $currency_code)
                    <div class="cabinet__money-wallet">
                        <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower(\App\UserWallet::currencyName($currency_code))}}"></div>
                        {!! \App\Helpers\UI::renderMoney(auth()->user()->getWallet($currency_code)->getBonusMoney(), \App\UserWallet::currencyName($currency_code)) !!}
                    </div>
                @endforeach
            </div>

{{--            <button type="button" class="cabinet__prtn-submit-btn" data-url="{{ action('ReferralController@transferBonusMoney', [], false) }}">{{ __('referral.withdrawMoney') }}</button>--}}
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

{{--    <!-- Referrals history -->--}}
{{--    <div id="cabinet__ref-history" style="width: 100%;overflow-x: auto;">--}}
{{--        @if(count($ref_history) === 0)--}}
{{--            <div class="no-records">--}}
{{--                <div class="no-records__title">{{ __('referral.noRefsAtTheMoment') }}</div>--}}
{{--                <div class="no-records__hint">{{ __('referral.hereRefsWillBeDisplayed') }}</div>--}}
{{--            </div>--}}
{{--        @else--}}
{{--            <table class="table">--}}
{{--                <thead>--}}
{{--                <tr>--}}
{{--                    <th class="custom-table__th w-5">{{ __('referral.date') }}--}}
{{--                        <div class="custom-table__sort-arrows custom-table__sort-arrows_default"></div>--}}
{{--                    </th>--}}
{{--                    <th class="custom-table__th w-8">{{ __('referral.user') }}</th>--}}
{{--                    <th class="custom-table__th">{{ __('referral.date') }}--}}
{{--                        <div class="custom-table__sort-arrows custom-table__sort-arrows_default"></div>--}}
{{--                    </th>--}}
{{--                    <th class="custom-table__th w-3">{{ __('referral.payout') }}</th>--}}
{{--                </tr>--}}
{{--                </thead>--}}

{{--                <tbody>--}}
{{--                @foreach($ref_history as $h_item)--}}
{{--                    <tr>--}}
{{--                        <td class="table__date">{{ \Illuminate\Support\Carbon::parse($h_item->created_at)->format('d/m/Y') }}</td>--}}
{{--                        <td>--}}
{{--                            <div class="flex ai-center table__text_white">--}}
{{--                                <img src="/img/ui/test-avatar.png" class="table__icon">--}}
{{--                                {{ empty($h_item->login) ? 'Anonymous ' : \App\MUser::hideLogin($h_item->login) }}--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="flex ai-center table__money">--}}
{{--                                <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($h_item->currency?? \Illuminate\Support\Facades\Auth::user()->currency)) }} table__icon"></div>--}}
{{--                                {!! \App\Helpers\UI::renderMoney($h_item->profit) !!}--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="table__text_white">--}}
{{--                                @if($h_item->type == \App\Models\UserReferrals\ReferralEvents::TYPE_REGISTER) {{ __('referral.registration') }} @endif--}}
{{--                                @if($h_item->type == \App\Models\UserReferrals\ReferralEvents::TYPE_MONEY_SPENT) {{ __('referral.moneySpent') }} @endif--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                @endforeach--}}
{{--                </tbody>--}}
{{--            </table>--}}
{{--        @endif--}}
{{--    </div>--}}


{{--    <!-- Referrals list -->--}}
{{--    <div id="cabinet__ref-list" style="display: none;width: 100%;overflow-x: auto;">--}}
{{--        @if(count($ref_users) === 0)--}}
{{--            <div class="no-records">--}}
{{--                <div class="no-records__title">{{ __('referral.noRefsAtTheMoment') }}</div>--}}
{{--                <div class="no-records__hint">{{ __('referral.hereRefsWillBeDisplayed') }}</div>--}}
{{--            </div>--}}
{{--        @else--}}
{{--            <table class="table">--}}
{{--                <thead>--}}
{{--                <tr>--}}
{{--                    <th class="custom-table__th w-5">{{ __('referral.date') }}</th>--}}
{{--                    <th class="custom-table__th w-8">{{ __('referral.user') }}</th>--}}
{{--                    <th class="custom-table__th">{{ __('referral.moneySpent') }}</th>--}}
{{--                    <th class="custom-table__th w-3">{{ __('referral.profit') }}</th>--}}
{{--                </tr>--}}
{{--                </thead>--}}

{{--                <tbody>--}}
{{--                @foreach($ref_users as $user)--}}
{{--                    <tr>--}}
{{--                        <td class="table__date">{{ \Illuminate\Support\Carbon::parse($user->reg_date)->format('d/m/Y') }}</td>--}}
{{--                        <td>--}}
{{--                            <div class="flex ai-center table__text_white">--}}
{{--                                <img src="/img/ui/test-avatar.png" class="table__icon">--}}
{{--                                {{ empty($user->login) ? 'Anonymous ' : \App\MUser::hideLogin($user->login) }}--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="flex ai-center table__money">--}}
{{--                                <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($user->currency)) }} table__icon"></div>--}}
{{--                                {!! \App\Helpers\UI::renderMoney($user->spent) !!}--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div class="flex ai-center table__money">--}}
{{--                                <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($user->currency)) }} table__icon"></div>--}}
{{--                                {!! \App\Helpers\UI::renderMoney($user->profit) !!}--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                @endforeach--}}
{{--                </tbody>--}}
{{--            </table>--}}
{{--        @endif--}}
{{--    </div>--}}
</div>
