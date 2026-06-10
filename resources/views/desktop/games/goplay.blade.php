@php
    /**
     * @var string $url
     * @var \App\GamesProviders\Game $game
     */
    use Illuminate\Support\Facades\Auth;
    use \App\Models\GameDepositThreshold;
    use \App\MUser;

    $games = \App\Services\Games\GameManagerApi::getInstance()->searchGames(
        groups: [$game->getGroupId()],
        page: 1,
        per_page: 20,
    );
    $has_bonus = isset($bonus);

    $tournament = null;
    if (Auth::check() && !$has_bonus) {
        $ts = new \App\Services\Tournaments\TournamentsService(MUser::getAuth());
        if ($ts->tournamentsEnabled()) {
            $tournament = $ts->getGameTournament($game->getProviderId(), $game->getId());
            if ($tournament) $ts->subscribeNotifications($tournament->getId(), Auth::id());
        }
    }
@endphp

<div class="width-limiter flex column">

    <div class="play__buttons">

        @if($has_bonus)
            <div class="play__button" title="{{ __('games.fullscreen') }}"
                 onclick="toggleFullscreen(document.getElementById('game-wrapper'), false); toggleFullscreen(document.getElementById('game-frame'), true)">
                <img src="/img/ui/icons/game/full-screen.svg">
            </div>
        @else
            <div class="play__button" title="{{ __('games.fullscreen') }}"
                 onclick="toggleFullscreen(document.getElementById('game-frame'), false);">
                <img src="/img/ui/icons/game/full-screen.svg">
            </div>
        @endif

        <div class="play__button play__button-close" title="{{ __('games.close') }}">
            <img src="/img/ui/icons/game/close.svg">
        </div>
    </div>

    @if($has_bonus)
        <div id="game-wrapper" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
            <div id="play__wager" style="--progress: 0%"></div>
            <iframe id="game-frame" src="{{ $url }}"></iframe>
        </div>
    @else
        @if($tournament !== null)
            @include('desktop.tournaments.tournament-table', ['tournament' => $tournament,])
        @endif
        <div id="game-wrapper" class="@if($tournament !== null) tournament-game @endif">
{{--            <div id="play__wager" style="display: none"></div>--}}
            <iframe id="game-frame" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true" src="{{ $url }}"></iframe>
        </div>
    @endif


    <div class="games-slider mt-3">

        <div class="games-slider__header">
            <div class="games-slider__info">
                <img src="/img/ui/icons/game/crown.svg" class="games-slider__icon">
                <div>
                    <div class="games-slider__title"
                         style="margin-bottom: 0">{{ __('games.weAlsoRecommend') }}</div>
                </div>
            </div>

            <div class="games-slider__controls">
                <div class="games-slider__all-button">{{ __('landing.all') }} ({{ $games->getTotalGames() }})
                </div>
                <div class="games-slider__arrow-left games-slider__arrow-disabled"></div>
                <div class="games-slider__arrow-right"></div>
            </div>
        </div>

        <div class="games-slider__games">
            @include('desktop.games.items', ['games' => $games])
        </div>
    </div>

    <script>
        (() => {
            let chatBtn = document.getElementById('supportTrigger');
            if (chatBtn) chatBtn.style.display = 'none';
            setTimeout(() => {
                network.post('/updateBonusPad');
            }, 200);

            let trig = true;
            window.onmessage = function (event) {
                if (event && (event.data === 'closeGame' || event.data === 'close') && trig) {
                    trig = false;
                    setTimeout(function () {
                        document.querySelector('.play__button-close').click();
                    }, 100);
                }
            };

        })();
    </script>
</div>
