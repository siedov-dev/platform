@php
    /**
     * @var \Carbon\Carbon $month
     */
        $i = 0;

        if (!empty($month)) {
            $start_date = $month->firstOfMonth()->format('Y-m-d H:i:s');
            $end_date = $month->lastOfMonth()->format('Y-m-d H:i:s');
        } else {
            $start_date = \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', '2000-01-01 00:00:00');
            $end_date = \Illuminate\Support\Carbon::now();
        }

$currency = \App\UserWallet::CURRENCY_EUR;
$user = \App\MUser::getAuth();
if ($user !== null) {
    $currency = $user->getCurrency();
}
$currency_name = mb_strtolower(\App\UserWallet::currencyName($currency));
$courses = \App\Helpers\Currency::getCourses();

$wins = \Illuminate\Support\Facades\DB::select("select m_users.login as user_name, glory_wins.system, glory_wins.game_id,
    wins.currency, glory_wins.created_at, glory_wins.win as win, glory_wins.bet, wins.total_win as total_win
    from glory_wins

    inner join
        (SELECT user_id, currency, MAX(created_at) as dt
            from glory_wins
            where created_at >= '$start_date'
            and created_at <= '$end_date'
            group by user_id, currency
            ) as ldates
        on (ldates.user_id = glory_wins.user_id)
        and (ldates.currency = glory_wins.currency)
        and (ldates.dt = glory_wins.created_at)

    inner join
        (SELECT user_id, currency,
        (sum(win) * IF(currency = 1, 1,
        IF(currency = 2, ".$courses['UAH'].",
        IF(currency = 4, ".$courses['EUR'].",
        IF(currency = 5, ".$courses['INR'].",
        IF(currency = 7, ".$courses['USD'].",
        IF(currency = 8, ".$courses['KZT'].", 1)))))) ) as total_win
            from glory_wins
            where created_at >= '$start_date'
            and created_at <= '$end_date'
             group by user_id, currency
            ) as wins
        on (wins.user_id = glory_wins.user_id)
        and (wins.currency = glory_wins.currency)

    inner join m_users on (m_users.id = glory_wins.user_id)

    where glory_wins.created_at >= '$start_date'
    and glory_wins.created_at <= '$end_date'

    order by wins.total_win desc
    limit 25
    ");
@endphp

@if (count($wins) === 0)

    <div class="no-records">
        <div class="no-records__title">{{__('hall-of-fame.no_records')}}</div>
    </div>

@else

    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>{{__('hall-of-fame.player')}}</th>
{{--            <th>Ставка</th>--}}
            <th>{{__('hall-of-fame.game')}}</th>
{{--            <th>Кофециент</th>--}}
            <th class="table__cell-right">{{__('hall-of-fame.win')}}</th>
        </tr>
        </thead>

        <tbody>
        @foreach($wins as $win)
            @php
                $i++;
                $provider = \App\GamesProviders\GamesManager::getProvider($win->system);
                $game = $provider?->getGame('', $win->game_id);
            @endphp
            <tr>
                <td class="table__rating-index" data-index="{{ $i }}"></td>

                <td>
                <span class="table__text_white flex ai-center">
                    <img src="/img/ui/icons/avatar.png" class="table__icon">
                    <b style="overflow: hidden; text-overflow: ellipsis; max-width: 7rem;">{{ \App\MUser::hideLogin($win->user_name) }}</b>
                </span>
                </td>

{{--                <td>--}}
{{--                    <div class="table__money flex ai-center">--}}
{{--                        <div class="currency-icon currency-icon_{{ $win->currency == 1 ? 'rub' : 'uah' }} table__icon"></div>--}}
{{--                        {!! \App\Helpers\UI::renderMoney($win->bet) !!}--}}
{{--                    </div>--}}
{{--                </td>--}}

                <td>
                    <div class="flex ai-center" data-game="{{ $win->game_id .' - ' . $win->system }}">
                        <img src="{{ $game == null ? '/img/ui/icons/round-game-preview.png' : $game->getImageUrl() }}" class="table__icon table__game-icon">
                        <a class="table__link" href="{{route('games', ['name' => $game === null ? 'Dopping fruits' : $game->getName()])}}"><b>{{ $game === null ? 'Dopping fruits' : $game->getName() }}</b></a>
                    </div>
                </td>

{{--                <td>--}}
{{--                    <span class="table__multiplier">x{{ $win->bet == 0 ? 10 : round($win->win/$win->bet, 2) }}</span>--}}
{{--                </td>--}}

                <td class="table__cell-right ">
                    <div class="table__money table__money_green flex ai-center">
                        <div class="currency-icon currency-icon_{{ $currency_name }} table__icon"></div>
{{--                        @if($win->currency !== $currency )--}}
                            {!! \App\Helpers\UI::renderMoney(number_format(\App\Helpers\Currency::convert($win->total_win, 'RUB', $currency_name), 2, '.', '')) !!}
{{--                        @else--}}
{{--                            {!! \App\Helpers\UI::renderMoney(number_format($win->total_win, 2, '.', '')) !!}--}}
{{--                        @endif--}}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

@endif
