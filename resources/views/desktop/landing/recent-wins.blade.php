@php
    /** @var \App\Models\BonusWin|\App\Models\TopWin $win */
    // $recentWins = \DB::table('last_win_kraken')->orderByDesc('date')->limit(10)->get();
    $storage = \App\GameProvidersApi\CurrentWin::getWinStorage();
    $recentWins = $storage->allCollection();
    $bonusWins = \App\Models\BonusWin::orderByDesc('updated_at')->limit(10)->get();
    $topWins = \App\Models\TopWin::orderByDesc('updated_at')->limit(10)->get();
@endphp

<div class="recent-wins">
    <div class="tabs">
        <div class="tabs__tab tabs__tab_active" data-target="#recent-wins">{{ __('landing.recentWins') }}</div>
        <div class="tabs__tab" data-target="#top-wins">{{ __('landing.bigWins') }}</div>
        <div class="tabs__tab" data-target="#bonus-wins">{{ __('landing.bonusWins') }}</div>
    </div>

    <!-- Recent wins -->
    <table id="recent-wins" class="table w-100">
        <thead>
        <tr>
            <th>{{ __('landing.game') }}</th>
            <th>{{ __('landing.user') }}</th>
            <th>{{ __('landing.bet') }}</th>
            <th>{{ __('landing.time') }}</th>
            <th class="table__cell-right">{{ __('landing.win') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($recentWins as $win)
            @php
                $jsonString = json_encode($win);
                $win = json_decode($jsonString);

                if (!empty($win->provider)) {
                    $id = $win->system.'/'.$win->provider.'/'.$win->game_alias;
                    $game = \App\GamesProviders\GamesManager::getGameByCompoundId($id);
                } else {
                    $provider = \App\GamesProviders\GamesManager::getProvider($win->system);
                    $game= $provider?->getGame('', $win->game_alias);
                }
//                if ($game === null) $game = \App\GamesProviders\GamesManager::getRandomGame();
                if ($game === null) continue;
            @endphp
            <tr>
                <td>
                    <div class="flex ai-center">
                        <img src="{{ $game === null ? '/img/ui/icons/round-game-preview.png' : $game->getImageUrl() }}"
                             onerror="this.src='/img/ui/icons/round-game-preview.png'" class="table__image">
                        <span class="table__text_white">{{ $win->last_game }}</span>
                    </div>
                </td>
                <td>
                    <div class="table__text_white">{{\App\MUser::hideLogin($win->nik)}}</div>
                </td>
                <td>
                    <div class="table__money table__money_green">
                        <div class="currency-icon currency-icon_{{ strtolower($win->currency) }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney(empty($win->bet) ? 0 : $win->bet) !!}
                    </div>
                </td>
                <td class="table__date">{{ date('H:i', strtotime($win->date)) }}</td>
                <td class="table__cell-right">
                    <div class="table__money table__money_green">
                        <div class="currency-icon currency-icon_{{ strtolower($win->currency) }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($win->sum) !!}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Top wins -->
    <table id="top-wins" class="table w-100" style="display: none">
        <thead>
        <tr>
            <th>{{ __('landing.game') }}</th>
            <th>{{ __('landing.user') }}</th>
            <th>{{ __('landing.time') }}</th>
            <th class="table__cell-right">{{ __('landing.win') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($topWins as $win)
            @php
                $id = $win->system.'/'.$win->provider.'/'.$win->game_id;
                if (!empty($win->provider)) {
                    $id = $win->system.'/'.$win->provider.'/'.$win->game_id;
                    $game = \App\GamesProviders\GamesManager::getGameByCompoundId($id);
                } else {
                    $provider = \App\GamesProviders\GamesManager::getProvider($win->system);
                    $game = $provider->getGame('', $win->game_id);
                }
//                if ($game === null) $game = \App\GamesProviders\GamesManager::getRandomGame();
                if ($game === null) continue;
            @endphp
            <tr>
                <td>
                    <div class="flex ai-center">
                        <img src="{{ $game->getImageUrl() }}" class="table__image">
                        <span class="table__text_white">{{ $game->getName() }}</span>
                    </div>
                </td>
                <td>
                    <div class="table__text_white">{{\App\MUser::hideLogin($win->nickname)}}</div>
                </td>
                <td class="table__date">{{ date('H:i', strtotime($win->updated_at)) }}</td>
                <td class="table__cell-right">
                    <div class="table__money table__money_green">
                        <div class="currency-icon currency-icon_{{ strtolower($win->currency) }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($win->win) !!}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Bonus wins -->
    <table id="bonus-wins" class="table w-100" style="display: none">
        <thead>
        <tr>
            <th>{{ __('landing.game') }}</th>
            <th>{{ __('landing.user') }}</th>
            <th>{{ __('landing.time') }}</th>
            <th class="table__cell-right">{{ __('landing.win') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($bonusWins as $win)
            @php
                $id = $win->system.'/'.$win->provider.'/'.$win->game_id;
                if (!empty($win->provider)) {
                    $id = $win->system.'/'.$win->provider.'/'.$win->game_id;
                    $game = \App\GamesProviders\GamesManager::getGameByCompoundId($id);
                } else {
                    $provider = \App\GamesProviders\GamesManager::getProvider($win->system);
                    $game= $provider->getGame('', $win->game_id);
                }
//                if ($game === null) $game = \App\GamesProviders\GamesManager::getRandomGame();
                if ($game === null) continue;
            @endphp
            <tr>
                <td>
                    <div class="flex ai-center">
                        <img src="{{ $game->getImageUrl() }}" class="table__image">
                        <span class="table__text_white">{{ $game->getName() }}</span>
                    </div>
                </td>
                <td>
                    <div class="table__text_white">{{\App\MUser::hideLogin($win->nickname)}}</div>
                </td>
                <td class="table__date">{{ date('H:i', strtotime($win->updated_at)) }}</td>
                <td class="table__cell-right">
                    <div class="table__money table__money_green">
                        <div class="currency-icon currency-icon_{{ strtolower($win->currency == 1 ? 'RUB' : 'UAH') }} table__icon"></div>
                        {!! \App\Helpers\UI::renderMoney($win->win) !!}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>


<script>
    (() => {
        let lastWinTime = '';
        let ti = setInterval(async () => {
            let table = document.getElementById('recent-wins');
            if (table === null) return;

            try {
                let res = await network.get('{{action('IndexController@getLastWin', [], false)}}?date=' + lastWinTime);
                let data = await res.json();
                lastWinTime = data.date;

                let html = `
                        <td>
                            <div class="flex ai-center">
                                <img src="${data.gameImageUrl}" class="table__image">
                                <span class="table__text_white">${data.game}</span>
                            </div>
                        </td>
                        <td>
                            <span class="table__text_white">${data.user}</span>
                        </td>
                        <td>
                            <div class="table__money table__money_green">
                                <div class="currency-icon currency-icon_${data.currency.toLowerCase()} table__icon"></div>
                                <div class="money">
                                    <div class="money__int">${Math.floor(data.bet)}</div>
                                    <div class="money__rest">.${Math.floor((data.bet - Math.floor(data.bet)) * 100).toString().padEnd(2, '0')}</div>

                                </div>
                            </div>
                        </td>
                        <td class="table__date">${data.date.slice(11, 16)}</td>
                        <td class="table__cell-right">
                            <div class="table__money table__money_green">
                                <div class="currency-icon currency-icon_${data.currency.toLowerCase()} table__icon"></div>
                                <div class="money">
                                    <div class="money__int">${Math.floor(data.win)}</div>
                                    <div class="money__rest">.${Math.floor((data.win - Math.floor(data.win)) * 100).toString().padEnd(2, '0')}</div>
                                </div>
                            </div>
                        </td>`;
                let tr = document.createElement('tr');
                tr.innerHTML = html;

                let tbody = table.querySelector('tbody');
                tbody.insertBefore(tr, tbody.firstChild);

                let last = tbody.lastChild;
                while (last.nodeType === Node.TEXT_NODE) {
                    last = last.previousSibling;
                }
                tbody.removeChild(last);

            } catch (errorMsg) {
                console.error(errorMsg);
            }
        }, 6000);
    })();
</script>
