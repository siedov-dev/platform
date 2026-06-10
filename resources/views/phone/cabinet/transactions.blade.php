@php
    $hist = [];
    $deposits = \App\ApplicationForPayment::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('date', '>', now()->subDays(30)->format('Y-m-d H:i:s'))
            ->whereIn('confirmed', [\App\ApplicationForPayment::STATUS_PENDING,
            \App\ApplicationForPayment::STATUS_CANCELED,
            \App\ApplicationForPayment::STATUS_AUTO_PAY_PENDING,
            \App\ApplicationForPayment::STATUS_AUTO_PAY_PROCESS,
            \App\ApplicationForPayment::STATUS_AUTO_PAY_ERROR])
            ->get();
    $payouts = \App\PayoutTransaction::where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->where('date', '>', now()->subDays(30)->format('Y-m-d H:i:s'))
            ->whereIn('status_id', array_merge(\App\PayoutTransaction::getIncomingStatuses(), \App\PayoutTransaction::getOutgoingStatuses()))
            ->get();
    foreach ($deposits as $dep) {
        $currency = \App\UserWallet::currencyName($dep->currency);

        if ($dep->confirmed === \App\ApplicationForPayment::STATUS_DONE) {
            $status = '<span class="text-green">'.e(\App\ApplicationForPayment::getStatusName($dep->confirmed)).'</span>';
        } else if ($dep->confirmed === \App\ApplicationForPayment::STATUS_REFUSED || $dep->confirmed === \App\ApplicationForPayment::STATUS_CANCELED) {
            $status = '<span class="text-red">'.e(\App\ApplicationForPayment::getStatusName($dep->confirmed)).'</span>';
        } else {
            $status = '<span class="text-orange">'.e(\App\ApplicationForPayment::getStatusName($dep->confirmed)).'</span>';
        }

        $d['id'] = $dep->id;
        $d['date'] = \Carbon\Carbon::parse($dep->date)->format('d.m H:i');
        $d['dt'] = $dep->date;
        $d['operation'] = __('cabinet.payout');
        $d['amount'] = \App\Helpers\UI::renderMoney($dep->money, $currency);
        $d['status'] = $status;
        $d['is_pending'] = $dep->confirmed == \App\ApplicationForPayment::STATUS_PENDING;

        $hist[] = $d;
    }

    foreach ($payouts as $dep) {
        $currency = $dep->currency;

        $d['id'] = $dep->id;
        $d['date'] = \Carbon\Carbon::parse($dep->date)->format('d.m H:i');
        $d['dt'] = $dep->date;
        $d['operation'] = in_array($dep->status_id, \App\PayoutTransaction::getIncomingStatuses()) ? __('cabinet.deposit') : __('cabinet.payout');
        $d['amount'] = \App\Helpers\UI::renderMoney($dep->sum, $currency);
        $d['status'] = '<span class="text-green">' . __('cabinet.success') . '</span>';
        $d['is_pending'] = false;

        $hist[] = $d;

        usort($hist, fn($a, $b) => $a['dt'] < $b['dt']);
    }
@endphp
<div id="cabinet-position" data-position="transactions"></div>

@if (count($hist) === 0)

    <div class="no-records">
        <div class="no-records__title">{{ __('cabinet.noTransactionsTitle') }}</div>
        <div class="no-records__hint">{{ __('cabinet.noTransactionsHint') }}</div>
    </div>

@else

    <table class="table cabinet__transactions">
        <thead>
        <tr>
            <th>{{ __('cabinet.date') }}</th>
            <th>{{ __('cabinet.operation') }}</th>
            <th>{{ __('cabinet.sum') }}</th>
            <th class="table__cell-right">{{ __('cabinet.status') }}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($hist as $h)
            <tr>
                <td class="table__date">{{ $h['date'] }}</td>
                <td>
                    <div class="table__text_white">{!! $h['operation'] !!}</div>
                </td>
                <td>
                    <div class="table__money">
                        {!! $h['amount'] !!}
                    </div>
                </td>
                <td class="table__cell-right">
                    <div class="flex-column">
                        {!! $h['status'] !!}
                        <div style="width: 100%; align-self: center">
                            @if ($h['is_pending'])
                                <a href="{{ route('cabinet.transactions.cancel') }}" class="cancel-payout-button"
                                   data-id="{{ $h['id'] }}"
                                   data-confirm="{{__('cabinet.cancelPaymentQ')}}"><span class="text-red">{{ __('cabinet.cancel') }}</span></a>
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>


@endif


