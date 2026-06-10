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
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFFFFF;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
    .tournament__image {
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__list strong{
        color: #00b8ff;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay Goal Hitting Network Tournament is available to play in real
                        mode only</li>

                    <li>Evoplay Goal Hitting Network Tournament runs for (7) seven agreed
                        days in the month of November 2022. The tournament will take place
                        between 00:01 UTC on 17th November, 2022 and 23rd November,
                        2022 until 23:59 UTC.</li>

                    <li>The tournament prize pool amount is <strong>€10,000</strong> and will be distributed
                        among the top 100 players who have the highest scores on the leaderboards.</li>

                    <li>The minimum bet required to participate in the tournament is <strong>0,50 EUR</strong> or the
                        equivalent in the local currency. The more points, the higher the player gets on
                        the leaderboard. The first 100 players on the leaderboard will receive prizes. </li>

                    <li>The prize pool and minimum bet for this campaign are set in <strong>EUR</strong> and
                        might be subject to currency conversion fluctuations.</li>
                </ul>
            </div>

        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/ball.png" alt="Gambling lady" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff)">
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Every spin will contribute to the player’s total score that is shown on the
                        leaderboard. The total score is calculated with the points gained during all
                        rounds played in participating games.</li>
                    <li>The tournament has its qualified mechanic: Total Win accumulation.</li>

                    <li>The tournament has <strong>a list of qualified games and a prize pool</strong> (EUR), which is
                        distributed among the leaderboard winners according to these tournament
                        rules.</li>

                    <li>If several Participants gain the same number of points during the process of
                        calculating the tournament’s results, a higher position in the ranking is given
                        to the Participant who scored first.</li>

                    <li>The cash prizes will be credited to the players’ accounts as withdrawable
                        funds by the casino after the end of the tournament.</li>

                    <li>Evoplay reserves the right to void scores, or not pay out a prize, where all or
                        part of the score results from any obvious error, mistake, or technical fault
                        (including incorrect game payouts) whether caused by a machine or human
                        error in respect to any of the participating games.</li>

                    <li>Evoplay further reserves the right to void scores, or not pay out a prize
                        where, in opinion, all part of the score results from cheating or collusion
                        with other players.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/cup.png" alt="Gambling man" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff);">
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">

                    <li><strong>Tournament rankings:</strong> Total Win accumulation.</li>
                    <li><strong>Tournament criteria:</strong> tournament points are awarded for
                        each win (minimum bet is 0,50 EUR)</li>

                    <li><strong>Example:</strong> If you bet €1 and your winnings are €12,80, then
                        you earn 12.8 points. If in the second round of the game you
                        bet €1 and the winnings are €7,50, then you will earn 7.5
                        points, and the total number of points earned will be 20.3.
                        Any winnings with the minimum bet are converted to
                        POINTS</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/evo.png" alt="Gambling lady" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff);">
        </div>



        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/goal-hitting/stadium.png') no-repeat center; background-size: cover; border-radius: 2rem;" >
            <div class="tournament__cond-text" style="width: 20rem; padding-bottom: 5rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style=" background: linear-gradient(#8f173399, #4d4ab599);">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>1 664 €</td></tr>
                    <tr><td>2nd</td><td>1 270 €</td></tr>
                    <tr><td>3rd</td><td>1 000 €</td></tr>
                    <tr><td>4th</td><td>667 €</td></tr>
                    <tr><td>5th</td><td>600 €</td></tr>
                    <tr><td>6th</td><td>539 €</td></tr>
                    <tr><td>7th</td><td>400 €</td></tr>
                    <tr><td>8th</td><td>300 €</td></tr>
                    <tr><td>9th</td><td>220 €</td></tr>
                    <tr><td>10th - 19th</td><td>133 €</td></tr>
                    <tr><td>20th - 49th</td><td>33 €</td></tr>
                    <tr><td>50th - 100th</td><td>20 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
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
