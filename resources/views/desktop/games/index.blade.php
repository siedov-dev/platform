<?php
/**
 * @var \App\GamesProviders\SearchResult $games
 */
?>

<div class="width-limiter">
    @include('desktop.games.search-form', ['categories' => $categories])
    <div class="games-type">
        @if($onlyBonusGames?? false)
            @if($no_active_bonus)
                <div class="game-search-form__games-type-header">{{__('games.noActiveBonusGames')}}</div>
            @else
                <div class="game-search-form__games-type-header">{{__('games.activeBonusGames')}}</div>
            @endif
        @endif
        @if($onlyTournamentGames?? false)
                <div class="game-search-form__games-type-header">{{__('games.activeTournamentsGames')}}</div>
        @endif
    </div>

    <div class="games-slider mt-2">
        <div id="games__items" class="games-slider__games">
            @include('desktop.games.items', ['games' => $games])
        </div>
    </div>

    <div id="games__show-more-wrap" class="flex jc-center">
        @include('desktop.games.show-more', ['games' => $games])
    </div>
</div>

