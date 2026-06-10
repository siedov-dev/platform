@if(count($ref_history) === 0)
    <div class="no-records">
        <div class="no-records__title">{{ __('referral.noRefsAtTheMoment') }}</div>
        <div class="no-records__hint">{{ __('referral.hereRefsWillBeDisplayed') }}</div>
    </div>
@else
    <table class="table">
        <thead>
        <tr>
            <th class="custom-table__th w-5">{{ __('referral.date') }}
                <div class="custom-table__sort-arrows custom-table__sort-arrows_default"></div>
            </th>
            <th class="custom-table__th w-8">{{ __('referral.user') }}</th>
            <th class="custom-table__th">{{ __('referral.profit') }}
                <div class="custom-table__sort-arrows custom-table__sort-arrows_default"></div>
            </th>
            <th class="custom-table__th w-3">{{ __('referral.payout') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($ref_history as $h_item)
            @php
                $event = \App\Services\Referrals\ReferralEventType::from($h_item['type']);
            @endphp
            <tr>
                <td class="table__date">{{ \Illuminate\Support\Carbon::parse($h_item['created_at'])->format('d/m/Y') }}</td>
                <td>
                    <div class="flex ai-center table__text_white">
                        <img src="/img/ui/test-avatar.png" class="table__icon">
                        @if($event === \App\Services\Referrals\ReferralEventType::ReferralMoneyTransferred || $event === \App\Services\Referrals\ReferralEventType::AccumulationMoneyTransferred)
                            {{ $ref_event_users[$h_item['user_id']] }}
                        @else
                            {{ empty($ref_event_users[$h_item['user_id']]) ? 'Anonymous ' : \App\MUser::hideLogin($ref_event_users[$h_item['user_id']]) }}
                        @endif
                    </div>
                </td>
                <td>
                    <div class="flex ai-center table__money">
                        <div class="currency-icon currency-icon_{{ strtolower(\App\UserWallet::currencyName($h_item['currency']?? $user_currency)) }} table__icon"></div>
                        @if($event === \App\Services\Referrals\ReferralEventType::ReferralMoneyTransferred || $event === \App\Services\Referrals\ReferralEventType::AccumulationMoneyTransferred)
                            {!! \App\Helpers\UI::renderMoney($h_item['amount']) !!}
                        @else
                            {!! \App\Helpers\UI::renderMoney($h_item['profit']) !!}
                        @endif
                    </div>
                </td>
                <td>
                    <div class="table__text_white">
                        {{ $event->getName() }}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="ref-history__pagination">
        {!! $events_paginator->render('pagination::bootstrap-4') !!}
    </div>
@endif
