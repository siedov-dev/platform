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
        box-shadow: #0d8f1b 0 0 5px;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;
        font-weight: 500;
        text-align: center;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table tr:last-child td{
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__table th {
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay Harvest Of Bets Network Tournament is available to play in real mode
                        only.</li>
                    <li>Evoplay Harvest of Bets Network Tournament runs for 12 (twelve)
                        agreed days in the month of September 2022. The tournament
                        will take place between 00:01 UTC on 19th September 2022
                        and 30th September 2022 until 23:59 UTC</li>
                    <li>The tournament prize pool amount is €12,000 and will be distributed
                        among the top 100 players who have the highest scores on the
                        leaderboards.</li>
                    <li>The minimum bet required to participate in the tournament is 0,50 EUR
                        or the equivalent in the local currency. The more points, the higher the
                        player gets on the leaderboard. The first 100 players on the
                        leaderboard will receive prizes.</li>
                    <li>The prize pool and minimum bet for this campaign are set in EUR and
                        might be subject to currency conversion fluctuations.</li>
                    <li>Every spin will contribute to the player’s total score that is shown on the
                        leaderboard. The total score is calculated with the points gained during all
                        rounds played in participating games.</li>
                </ul>
            </div>

            <img src="/img/tournaments/harvest-of-bets/cat2.png" alt="Lion head" style="margin-left: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/harvest-of-bets/rept2.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>The tournament has its qualified mechanic: Total Bet accumulation. Tournament points are awarded
                        for each bet (minimum bet is 0,50 EUR)</li>
                    <li>Example: If you bet €1, it doesn't matter whether you
                        win or not, you get 1 point anyway. If in the second
                        round of the game you bet €2, then you get 2 points,
                        and the total number of points earned will be 3.
                        1 € = 1 POINT</li>
                    <li>The tournament has a list of qualified games and a prize pool (EUR),
                        which is distributed among the winners according to the tournament rules.</li>
                    <li>If several participants gain the same number of points, then a higher
                        position on the leaderboard is given to the participant who scored first.</li>
                    <li>The cash prizes will be credited to the players’ accounts as withdrawable
                        funds by the casino after the end of promotion.</li>
                    <li>Evoplay reserves the right to void scores, or not pay out a prize, in case of
                        all or part of the score results from any obvious error, mistake, or technical
                        fault (including incorrect game payouts) caused by a machine or human
                        error to any of the participating games.</li>
                    <li>Evoplay further reserves the right to void scores, or not pay out a prize
                        where, in opinion, all part of the score results from cheating or collusion
                        with other players.</li>
                </ul>
            </div>
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 70%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/harvest-of-bets/tbg.jpg"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 985 €</td>
                    <tr><td>2</td><td>1 520 €</td>
                    <tr><td>3</td><td>1 200 €</td>
                    <tr><td>4</td><td>800 €</td>
                    <tr><td>5</td><td>720 €</td>
                    <tr><td>6</td><td>647 €</td>
                    <tr><td>7</td><td>480 €</td>
                    <tr><td>8</td><td>360 €</td>
                    <tr><td>9</td><td>264 €</td>
                    <tr><td>10-19</td><td>160 €</td>
                    <tr><td>20-49</td><td>40 €</td>
                    <tr><td>50-100</td><td>24 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 12 000 €</b></td></tr>
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
