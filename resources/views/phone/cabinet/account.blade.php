@php
    /** @var \App\MUser $user */
    $user = auth()->user();
    $user_rank = $user->getRank();
@endphp
<div id="cabinet-position" data-position="account"></div>
<!-- Account info -->
<div class="cabinet__acc-wrap">
    <div class="flex ai-center">

        @include('common.blocks.user-avatar', ['percent' => 0])
        <div class="cabinet__acc-user-info">
            <div class="cabinet__menu-hello">{{ __('cabinet.welcome') }}</div>
            <div class="cabinet__menu-name">{{ auth()->check() ? auth()->user()->login : 'Guest' }}</div>
            @if(\Illuminate\Support\Facades\Auth::check())
                <div class="user-menu__id">id-{{App\MUser::getAuth()?->getId()}}</div>
            @endif
            <div class="cabinet__change-name-link cabinet__link change-login-button">{{ __('userMenu.changeNickname') }}</div>
        </div>
    </div>
</div>

<div class="cabinet__acc-wrap" style="display: flex; flex-flow: column">
    <div class="cabinet__pad-title">{{ __('cabinet.realMoney') }}:</div>

    <div class="cabinet__money-wallets">
        @foreach($user->getActiveCurrencies() as $currency_code)
            @php
                $currency_name = \App\UserWallet::currencyName($currency_code);
            @endphp
            <div class="cabinet__money-wallet @if($currency_name === $user->getCurrencyName()) active @endif">
                <div class="cabinet__money-wallet-icon currency-icon currency-icon_{{strtolower($currency_name)}} cabinet__change-currency" data-id="{{$currency_code}}" data-name="{{$currency_name}}"></div>
                <span class="cabinet__change-currency" data-id="{{$currency_code}}" data-name="{{$currency_name}}">{!! \App\Helpers\UI::renderMoney(auth()->check() ? auth()->user()->getWallet($currency_code)->getBalance() : 0, $currency_name) !!}</span>
                <div class="del-currency button_del_currency" data-id="{{$currency_code}}">&times;</div>
            </div>
        @endforeach
    </div>
    @if(!empty(array_diff(\App\UserWallet::activeCurrencies(), $user->getActiveCurrencies())))
        <div class="flex mt-1">
            <div class="button_add_currency">{{__('wallet.add_currency')}}</div>
        </div>
    @endif
</div>

<!-- Account info -->
{{--<div class="cabinet__acc-wrap">--}}

{{--    <!-- Rank -->--}}
{{--    <div class="cabinet__acc-rank-info">--}}
{{--        <div class="cabinet__acc-rank-icon">--}}
{{--            <img src="/img/ui/icons/star-colored.png" class="cabinet__acc-rank-image">--}}
{{--        </div>--}}

{{--        <div class="cabinet__acc-rank-texts">--}}
{{--            <div class="cabinet__acc-rank-name">--}}
{{--                {{ auth()->user()->getRankName() }}--}}
{{--                <div class="cabinet__acc-rank-hint cabinet__hint">--}}
{{--                    <img src="/img/ui/icons/question-mark.svg">--}}
{{--                    <div class="cabinet__hint-popup" style="width: 15rem">--}}
{{--                        <div>{{ __('cabinet.rankHint') }}</div>--}}
{{--                        <a href="#" class="cabinet__link" style="display: inline-block" onclick="event.preventDefault(); document.querySelector('.cabinet__ranks-link').click()">{{ __('cabinet.details') }}</a>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="cabinet__acc-rank-progress-wrap">--}}
{{--                <div class="cabinet__acc-rank-progress-track">--}}
{{--                    <div class="cabinet__acc-rank-progress-bar" style="width: {{ auth()->user()->getNextRankProgress() }}%"></div>--}}
{{--                </div>--}}
{{--                <img src="/img/ui/icons/gift-colored.svg">--}}
{{--            </div>--}}
{{--            <div class="cabinet__acc-rank-next">--}}
{{--                {{ __('cabinet.next') }}: <span class="cabinet__acc-rank-next-name">{{ auth()->user()->getRankName() }}</span> <span class="cabinet__acc-rank-percents">{{ auth()->user()->getNextRankProgress() }}%</span>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}



<!-- Verification -->
<div class="cabinet__acc-wrap">
    <div class="cabinet__acc-verification">
        <div class="cabinet__acc-vrf-title">
            {{ __('cabinet.verification') }}
            <div class="cabinet__acc-vrf-hint cabinet__hint">
                <img src="/img/ui/icons/question-mark.svg">
                <div class="cabinet__hint-popup" style="width: 15rem">
                    <div>{{ __('cabinet.verificationHint') }}</div>
                    <a href="#" class="cabinet__link" style="display: inline-block" onclick="event.preventDefault(); document.querySelector('.cabinet__settings-link').click()">{{ __('cabinet.details') }}</a>
                </div>
            </div>
        </div>

        <div class="cabinet__acc-vrf-status @if(auth()->user()->personalData()->isConfirmed()) status-verified @endif">
            @if(auth()->user()->personalData()->isConfirmed())
                <img src="/img/ui/icons/shield-succ.svg" class="cabinet__acc-vrf-icon">
            @else
                <img src="/img/ui/icons/shield-fail.svg" class="cabinet__acc-vrf-icon">
            @endif
            &nbsp;{{ auth()->user()->personalData()->getStatusString() }}
        </div>

        @if(!auth()->user()->personalData()->isConfirmed() && auth()->user()->personalData()->status != \App\Models\PersonalData::STATUS_REQUESTED)
            <div class="cabinet__acc-vrf-link cabinet__link">{{ __('cabinet.verifyAccount') }}</div>
        @endif
    </div>
</div>



<!-- Cash back -->
<div class="cabinet__acc-wrap">
    <div class="cabinet__acc-cashback">
        <div class="cabinet_acc-cashback-icon">
            <img src="/img/ui/icons/cash-back-colored.png" class="cabinet__acc-cashback-image">
        </div>

        <div class="cabinet_acc-cashback-texts">
            <div class="cabinet__acc-vrf-title">
                {{ __('cabinet.yourCashback') }}:
{{--                <div class="cabinet__acc-vrf-hint cabinet__hint">--}}
{{--                    <img src="/img/ui/icons/question-mark.svg">--}}
{{--                    <div class="cabinet__hint-popup" style="width: 15rem">--}}
{{--                        <div>{{ __('cabinet.cashBackHint') }}</div>--}}
{{--                    </div>--}}
{{--                </div>--}}
            </div>

            <div class="cabinet__acc-cashback-percents">{{$user->getRank()->getWeeklyCashBackPrc()}}% +
                {{ number_format($user->getRank()->getWeeklyCashBackPrc() *  $user->getValue('cashback_plus', 0) / 100, 1, '.', '')}}% =
                {{ number_format($user->getRank()->getWeeklyCashBackPrc() + ($user->getRank()->getWeeklyCashBackPrc() *  $user->getValue('cashback_plus', 0) / 100), 1, '.', '')}}%
            </div>
        </div>
    </div>
</div>

@php
    $user = \Illuminate\Support\Facades\Auth::user();
        $query = "
        SELECT game_id, login_date as start_date, `system` as provider, login_balance, logout_balance, currency
            FROM `user_game_stats`
            WHERE user_id = $user->id

        ORDER BY start_date DESC
        LIMIT 10
            ";
        $recent_games = \Illuminate\Support\Facades\DB::select(\Illuminate\Support\Facades\DB::raw($query));
@endphp



<div class="cabinet__recent-bets-wrap">
    <div class="cabinet__content-title">{{ __('cabinet.recent_games') }}</div>

@if (count($recent_games) > 0)
    <!-- Table -->
    <table class="table">
        <thead>
        <tr>
            <th><div class="flex ai-center">{{ __('cabinet.date') }} </div></th>
            <th><div class="flex ai-center">{{ __('cabinet.login_balance') }}</div></th>
            <th>{{ __('cabinet.game') }}</th>
            <th><div class="flex ai-center">{{ __('cabinet.logout_balance') }}</div></th>
{{--            <th>Статус</th>--}}
        </tr>
        </thead>
        <tbody>
        @foreach($recent_games as $game)
            @php
                $currency = mb_strtolower($game->currency);
                $provider = \App\GamesProviders\GamesManager::getProvider($game->provider);
                $game_info = $provider?->getGame('', $game->game_id)
            @endphp
            @if($game_info)
                <tr>
                    <td> <span class="table__date">{{\Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $game->start_date)->format("d/m/y")}}</span></td>
                    <td class="table__money">
                        <div class="currency-icon currency-icon_{{$currency}} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($game->login_balance) !!}
                    </td>
                    <td>
                        <div class="table__text_white">
                            <img src="{{ $game_info->getImageUrl() }}" class="table__image table__image-small">
                        </div>
                    </td>
                    <td>
                        <div class="table__money">
                            <div class="currency-icon currency-icon_{{$currency}} table__icon"></div>
                            {!! \App\Helpers\UI::renderMoney($game->logout_balance) !!}
                        </div>
                    </td>
                    {{--                    <td class="table__cell-right">{!! rand(0, 10) > 5 ? '<span class="text-green">'.e(__('cabinet.success')).'</span>' : '<span class="text-red">'.e(__('cabinet.error')).'</span>' !!}</td>--}}
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>
    @else

        <div class="no-records mt-4">
            <div class="no-records__title">{{ __('cabinet.noRecentGamesTitle') }}</div>
            <div class="no-records__hint">{{ __('cabinet.noRecentGamesHint') }}</div>
        </div>

    @endif

</div>

