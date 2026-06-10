@php
    $api = new \App\GamesProviders\BetProduction\BetProductionApi();
    $active_currency = \App\MUser::getAuth()->currency;
    $user_id = \Illuminate\Support\Facades\Auth::id();
    $error = '';


    $data = [];

    $resp = $api->getPlayersHistory(\Illuminate\Support\Facades\Auth::id().\App\UserWallet::currencyName($active_currency), now()->subDays(60)->timestamp, now()->timestamp);
    if (($resp['success']?? false) === false) {
         $error = $resp['errors'][0]??'неизвестная ошибка';
    } else {
        $qr = collect($resp);
        $qr = $qr->sortBy('created_at', null, true)->take(15);
        $data = $qr->toArray();
    }
@endphp

<div id="cabinet-position" data-position="sport"></div>

@if (count($data) === 0)

    <div class="no-records">
        <div class="no-records__title">{{ __('cabinet.noTransactionsTitle') }}</div>
        <div class="no-records__hint">{{ __('cabinet.noTransactionsHint') }}</div>
    </div>

@else
    <div class="scroll-hidden">
        <table class="table cabinet__transactions">
            <thead>
            <tr>
                <th>{{ __('cabinet.date') }}</th>
                <th>{{ __('cabinet.bet') }}</th>
                <th>{{ __('cabinet.bet') }}/{{ __('cabinet.sum') }}</th>
                <th class="table__cell-right">{{ __('cabinet.status') }}</th>
            </tr>
            </thead>

            <tbody>
            @foreach($data as $tr)
                @php
                    $currency = $tr['currency'];
                    if ($tr['tt'] === \App\GamesProviders\BetProduction\BetProdTransactions::TYPE_WIN) {
                        $status = '<span class="text-green">'.e(\App\Models\BetProdTransactions::getTypeName($tr['tt'])).'</span>';
                    } elseif ($tr['tt'] === \App\Models\BetProdTransactions::TYPE_BET) {
                        $status = '<span class="text-orange">'.e(\App\Models\BetProdTransactions::getTypeName($tr['tt'])).'</span>';
                    } else {
                        $status = '<span class="text-red">'.e(\App\Models\BetProdTransactions::getTypeName($tr['tt'])).'</span>';
                    }
                @endphp
                <tr>
                    <td class="table__date">{{ \Carbon\Carbon::parse($tr['created_at'])->format('d.m.Y') }}</td>
                    <td>
                        <div class="flex column" style="min-width: 10rem;">
                            <div class="table__text_white">{{ $tr['region'] }} {{ $tr['sport'] }}</div>
                            <div class="table__text_white"><span style="white-space: nowrap">{{ $tr['team1'] }}</span> /
                                <span style="white-space: nowrap">{{ $tr['team2'] }}</span></div>
                        </div>
                    </td>
                    <td>
                        <div class="table__money">
                            <div class="currency-icon currency-icon_{{ strtolower($currency) }} table__icon"></div>
                            {!! \App\Helpers\UI::renderMoney($tr['sm'], $currency) !!}
                        </div>
                        <div class="table__text_white ml-2">x{{ $tr['total_odds'] }}</div>
                        <div class="table__money">
                            <div class="currency-icon currency-icon_{{ strtolower($currency) }} table__icon"></div>
                            {!! \App\Helpers\UI::renderMoney($tr['sm'] * $tr['total_odds'], $currency) !!}
                        </div>
                    </td>
                    <td class="table__cell-right">{!! $status !!}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>
@endif
