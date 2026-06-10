@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
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


    <div class="tournament__cond-title">GameArt Fruit Party Terms and Conditions</div>
            <ul class="tournament__list">
                <div class="tournament__cond-text">The player can participate in the promotion during the validity period as follows:</div>
                <li>Play the qualifying game and keep track of your score in the leaderboard.</li>
                <li>For every bet of 1 euro made with real money in the qualifying game, the player receives 1 point.</li>
                <div class="tournament__cond-title">Extra achievements:</div>
                <li>BIG WIN – 50 points</li>
                <li>300 symbols Scatter in any game – 300 points</li>
                <li>EPIC WIN – 500 points</li>
                <li>5000 spins – 1000 points (bet value €0,40 or higher)</li>
                <li>Тhe Leaderboard will be live and displayed in the game itself.</li>
            </ul>

        <div class="tournament__cond-title">Mechanics:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>The Fruit Party GameArt is a global tournament available to all players.</li>
                <li>The tournament will start at 00:01 GMT on Thursday, February 10 and end at 23:59 GMT.
                      on Thursday, February 17, 2022.</li>
                <li>Players can win the following prizes: € 10,000 will be distributed among 150 players</li>
                <li>The company reserves the right to change, cancel or terminate the promotion at any time.</li>
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

    @include('phone.footer')

</div>
