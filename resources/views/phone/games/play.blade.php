<?php
/**
 * @var string $url
 * @var \App\GamesProviders\Game $game
 */

use App\Models\Bonuses\UserBonus;
use App\Models\Tournaments\TournamentGames\TournamentGame;
use Illuminate\Support\Facades\Auth;

//$provider = \App\GamesProviders\GamesManager::getProvider($game->getProviderId());
$games = \App\Services\Games\GameManagerApi::getInstance()->searchGames(
    groups: [$game->getGroupId()],
    page: 1,
    per_page: 20,
);
$has_bonus = (UserBonus::findFirstActiveBonus(Auth::id()) !== null || TournamentGame::findFirstActiveGame(Auth::id()));
?>
<button id="iphone-hack"
        style="width: 1px; height: 1px; position: absolute; top: -1000px; left: -1000px; z-index: 2; background-color: rgb(255, 0, 0);"
        title="select to enable accessability for this content"></button>
<div class="width-limiter flex column">
    <div class="play__buttons">
        <div class="play__button" title="{{ __('games.addToFavourites') }}"><img src="/img/ui/icons/game/star.svg">
        </div>
        {{--        <div class="play__button" title="{{ __('games.tournament') }}"><img src="/img/ui/icons/game/cup.svg"></div>--}}
        @if($has_bonus)
            <div class="play__button play__button-fullscreen" title="{{ __('games.fullscreen') }}"
                 onclick="toggleFullscreen(document.getElementById('game-wrapper'), false); toggleFullscreen(document.getElementById('game-frame'), true)">
                <img src="/img/ui/icons/game/full-screen.svg"></div>
        @else
            <div class="play__button play__button-fullscreen" title="{{ __('games.fullscreen') }}"
                 onclick="toggleFullscreen(document.getElementById('game-frame'), false);"><img
                        src="/img/ui/icons/game/full-screen.svg"></div>
        @endif
        <div class="play__button play__button-close" title="{{ __('games.close') }}"><img
                    src="/img/ui/icons/game/close.svg"></div>
    </div>

    <div id="play__wager" style="display: none"></div>

    @if($has_bonus)
        <div id="game-wrapper" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true">
            <div id="play__wager" style="display: none"></div>

            <iframe id="game-frame" src="{{ $url }}"></iframe>
        </div>
    @else
        <div id="game-wrapper">
            <div id="play__wager" style="display: none"></div>
            <iframe id="game-frame" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"
                    src="{{ $url }}"></iframe>
        </div>
    @endif


    <div class="games-slider mt-3">

        <div class="games-slider__header">
            <div class="games-slider__info">
                <img src="/img/ui/icons/game/crown.svg" class="games-slider__icon">
                <div>
                    <div class="games-slider__title" style="margin-bottom: 0">{{ __('games.weAlsoRecommend') }}</div>
                </div>
            </div>
        </div>

        <div class="games-slider__games">
            @include('phone.games.items', ['games' => $games])
        </div>
    </div>

    <script>
        (() => {
            let chatBtn = document.getElementById('supportTrigger');
            if (chatBtn) chatBtn.style.display = 'none';

            let frame = document.getElementById('game-frame');
            frame.style.height = Math.round(frame.parentNode.offsetWidth * 0.6) + 'px';

            let ti = setInterval(async () => {

                try {
                    if (window.location.pathname.indexOf('/play/') === -1) {
                        clearInterval(ti);
                        return;
                    }
                    let wager = document.getElementById('play__wager');
                    if (wager === null) {
                        clearInterval(ti);
                        return;
                    }
                    let res = await network.get('/GetVegasBonus');
                    let data;
                    try {
                        data = await res.json();
                    } catch (e) {
                        wager.innerHTML = "";
                        return;
                    }
                    if (data.status == 2) {
                        if (data.modal !== undefined && data.modal !== null) {
                            clearInterval(ti);
                            Modal.show(data.modal, true, async () => {
                                let modal = document.querySelector('.modal');
                                while (modal !== null) {
                                    modal.remove();
                                    modal = document.querySelector('.modal');
                                }
                                location.href = '{{route('cabinet.bonuses', [], true)}}';
                            });
                            // modal.closeButton.classList.add('promo-modal__close-button');
                        } else {
                            setTimeout(function () {
                                document.querySelector('.play__button-close').click();
                            }, 500);
                        }
                    } else {
                        wager.style.display = 'flex';
                        wager.innerHTML = "<div>" + data.show_status + "</div>";
                    }
                } catch (e) {
                    console.log(e.toString());
                    // clearInterval(ti);
                }
            }, 1000);

            @if($has_bonus)
            setTimeout(function () {
                toggleFullscreen(document.getElementById('game-wrapper'), true)
            }, 1000);
            @else
            setTimeout(function () {
                toggleFullscreen(document.getElementById('game-frame'), true)
            }, 1000);
            @endif

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
