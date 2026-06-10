@php
    /** @var \App\Models\Tournaments\Pragmatic\PragmticTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }
    .tournament__list ul li {
        font-size: 0.875rem;
        color: #7a79b0;
    }
    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }
    .tournament__table {
        border-spacing: 0;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #8888af;
        border-bottom: 1px solid #ffffff1a;

    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        border-bottom: 2px solid #fff;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__cond-title">Period of tournament:</div>
        <div class="tournament__cond-text">From 11 of November 2021 to 18 of November 2021.</div>

        <div class="tournament__cond-title">Prize fund:</div>
        <div class="tournament__cond-text">10,000 € for 150 players</div>

        <div class="tournament__cond-title">Mechanics:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>The Promotion “GameArt’s cash rain” is a global promotion open to all players.</li>
                <li>The promotion will begin at 00:01 GMT on Thursday, the 11th of November and end at 23:59 GMT
                on Thursday, the 18th of November 2021.</li>
                <li>The following prizes are available to be won by Players: 10000 € will be distributed among 150 players</li>
            </ul>


            <table class="tournament__table mt-2">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>2,500 €</td></tr>
                    <tr><td>2nd</td><td>1,000 €</td></tr>
                    <tr><td>3rd</td><td>600 €</td></tr>
                    <tr><td>4th</td><td>300 €</td></tr>
                    <tr><td>5th -14th</td><td>100 €</td></tr>
                    <tr><td>15th-24th</td><td>80 €</td></tr>
                    <tr><td>25th-50th</td><td>50 €</td></tr>
                    <tr><td>51st-150th</td><td>25 €</td></tr>
                </tbody>
            </table>
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


</div>
