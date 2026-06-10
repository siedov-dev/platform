@php
    /** @var \App\Models\Tournaments\Pragmatic\PragmticTournament $tournament */
@endphp
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__cond-title">Period of tournament:</div>
        <div class="tournament__cond-text">From 11 of February 2021 to 17 of November 2021.</div>

        <div class="tournament__cond-title">Prize fund:</div>
        <div class="tournament__cond-text">
            <ul>
                <li>Money prizes with total sum €/$ 4,000,000 (€/$ 500,000 monthly)</li>
                <li>16X monthly tournaments with prize fund €/$ 31,000</li>
                <li>24X monthly tournaments with prize fund €/$ 62,000</li>
                <li>112X everyday promotions Prize Drops with prize fund €/$ 4,500</li>
                <li>168X everyday promotions Prize Drops with prize fund €/$ 9,000</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Mechanics:</div>
        <div class="tournament__cond-text">
            Period of every tournament - seven (7) days, from thursday to wednesday, in pair with Prize Drops promotion.<br/>
            Duration of promotion with common mechanics and "Prize multiplier" is limited to 4 weeks period.
        </div>


        <div class="tournament__cond-title">How to win:</div>
        <div class="tournament__cond-text">
            <ul>
                <li>Play every game from the list below on real money.</li>
                <li>Standard mechanics: minimal bet is €/$0,50.</li>
                <li>"Prize multiplier": without minimal bet. No additional deposit required.</li>
            </ul>
        </div>
    </div>



    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
            <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>


    @include('desktop.footer')

</div>
