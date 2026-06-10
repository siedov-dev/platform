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

    $ts = new \App\Services\Tournaments\TournamentsService(MUser::getAuth());
    $tournament = null;
    if ($ts->tournamentsEnabled()) {
        $tournament = $ts->getGameTournament($game->getProviderId(), $game->getId());
    }
@endphp
<button id="iphone-hack"
        style="width: 1px; height: 1px; position: absolute; top: -1000px; left: -1000px; z-index: 2; background-color: rgb(255, 0, 0);"
        title="select to enable accessability for this content"></button>
<div class="width-limiter flex column">
    <div class="play__buttons">
        {{--        <div class="play__button" title="{{ __('games.addToFavourites') }}"><img src="/img/ui/icons/game/star.svg"></div>--}}
        {{--        <div class="play__button" title="{{ __('games.tournament') }}"><img src="/img/ui/icons/game/cup.svg"></div>--}}
        <div class="play__button play__button-close hidden" title="{{ __('games.close') }}"><img
                    src="/img/ui/icons/game/close.svg"></div>
    </div>


    <div id="game-wrapper" class="" webkitallowfullscreen="false" mozallowfullscreen="false" allowfullscreen="false">
        <div id="play__wager" style="--progress: 0%"></div>
        @if($tournament !== null)
            <div id="game-tournament-position">
                {{__('tournament.position-t')}}
                <span class="t-position ml-05"></span>
            </div>
        @endif

        <iframe id="game-frame" src="{{ $url }}"></iframe>
    </div>

    <script>
        (() => {
            let chatBtn = document.getElementById('supportTrigger');
            if (chatBtn) chatBtn.style.display = 'none';

            let frame = document.getElementById('game-frame');
            //frame.style.height = Math.round(frame.parentNode.offsetWidth * 0.5625) + 'px';
            frame.style.aspectRatio = 16 / 9;
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

            @if($tournament !== null)
                setTimeout( async () => {
                    try {
                        if (document.getElementById("game-tournament-position") === null) {
                            return;
                        }
                        const resp = await network.post('{{route('tournaments.table', [], false)}}', {
                            tournament_id: {{ $tournament->getId() }}
                        })
                        const data = await resp.json()
                        const position = data.user_position;
                        if (position > 0) {
                            document.querySelector('#game-tournament-position').parentElement.classList.add('show-tournament');
                        } else {
                            document.querySelector('#game-tournament-position').parentElement.classList.remove('show-tournament');
                            return;
                        }
                        let positionEl =  document.querySelector('#game-tournament-position .t-position');
                        if (positionEl.innerText !== position.toString()) {
                            positionEl.innerText = position;
                            positionEl.parentElement.classList.add('highlighted');
                            setTimeout(() => positionEl.parentElement.classList.remove('highlighted'), 500);
                        }
                    } catch (e) {
                        console.log(e.message)
                    }
                }, 500)
            @endif

        })();
    </script>
</div>
