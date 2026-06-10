@if(count($ref_users) === 0)
    <div class="no-records">
        <div class="no-records__title">{{ __('referral.noRefsAtTheMoment') }}</div>
        <div class="no-records__hint">{{ __('referral.hereRefsWillBeDisplayed') }}</div>
    </div>
@else
    <table class="table">
        <thead>
        <tr>
            <th class="custom-table__th w-5">{{ __('referral.date') }}</th>
            <th class="custom-table__th w-8">{{ __('referral.user') }}</th>
            <th class="custom-table__th">{{ __('referral.moneySpent') }}</th>
            <th class="custom-table__th w-3">{{ __('referral.profit') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($ref_users as $user)
            <tr>
                <td class="table__date">{{ \Illuminate\Support\Carbon::parse($user['reg_date'])->format('d/m/Y') }}</td>
                <td>
                    <div class="flex ai-center table__text_white">
                        <img src="/img/ui/test-avatar.png" class="table__icon">
                        {{ empty($user['login']) ? 'Anonymous ' : \App\MUser::hideLogin($user['login']) }}
                    </div>
                </td>
                <td>
                    <div class="flex ai-center table__money">
                        <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($user['profit_currency'])) }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($user['money_spent']) !!}
                    </div>
                </td>
                <td>
                    <div class="flex ai-center table__money">
                        <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($user['profit_currency'])) }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($user['profit']) !!}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="ref-users__pagination">
        {!! $users_paginator->render('pagination::bootstrap-4') !!}
    </div>
@endif
