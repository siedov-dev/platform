@php
/** @var \App\GamesProviders\SearchResult $games */
@endphp
@foreach($games->getGames() as $game)
    <div class="game">
        <img src="{{ $game->getImageUrl() }}" class="game__image">
        <div class="name">
            <div>{{$game->getName()}}</div>
            <div class="provider">
                <img src="/img/icons/providers/{{$game->getProviderId()}}/{{str_replace(' ', '_', $game->getGroupId())}}.png">
                <span>{{$game->getGroupId()}}</span>
            </div>
        </div>
        @if(auth()->check())
            <div class="play-button game__button" data-game-id="{{$game->getCompoundId()}}"
                 data-url="{{ auth()->check() ? $game->getRealPlayUrl() : ($game->hasDemoMode()? $game->getDemoPlayUrl() : "#") }}"
                 data-name="{{ $game->getName() }}">Play</div>
        @else
            <div class="play-button" onclick="document.getElementById('login-button').click()">Play</div>
        @endif
    </div>
@endforeach
<div class="games-count">
    @if(!empty($name))
        <div>{{__('games.searchResult')}}&nbsp;<span>{{$games->getTotalGames()}}</span></div>
    @else
        <div>{{__('games.popularGames')}}</div>
    @endif
</div>
