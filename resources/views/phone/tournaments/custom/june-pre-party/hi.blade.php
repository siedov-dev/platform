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
        color: #8888af;
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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: initial;">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay June Pre-party Network Tournament is available to play in real mode
                        only.</li>
                    <li>Evoplay June Pre-party Network Tournament runs for 12 (twelve) agreed days
                        in the month of June 2022. The tournament will take place between 00:01 UTC
                        on 15 th June 2022 and 26 th June 2022 until 23:59 UTC.</li>
                    <li>The tournament prize pool amount is €14,000 and will be distributed among the
                        top 100 players who have the highest scores on the leaderboards.</li>
                    <li>The minimum bet required to participate in the tournament is 0,20 EUR or the
                        equivalent in the local currency. The more points, the higher the player gets on
                        the leaderboard. The first 100 players on the leaderboard will receive prizes.</li>
                    <li><b>All prizes are deducted from the invoice in June, 2022. If the deduction
                            discount is higher than the total invoice sum, discount balance will be
                            considered in the next period</b></li>
                    <li>The prize pool and minimum bet for this campaign are set in EUR and
                        might be subject to currency conversion fluctuations.</li>
                    <li>Once the tournament is over, every eligible operator will receive the list of
                        the winners to credit the prizes.</li>
                    <li>Every spin will contribute to the player’s total score that is shown on the
                        leaderboard. The total score is calculated with the points gained during all
                        rounds played in participating games.</li>
                </ul>
            </div>

        </div>
        <img src="/img/tournaments/pre-party/3.png" alt="playson" class="tournament__image">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>The tournament has its qualified mechanic: Total Win accumulation.</li>
                    <li>Tournament points are awarded for each win (minimum bet is 0,20 EUR)</li>
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
        <img src="/img/tournaments/pre-party/1.png" alt="Lion head" class="tournament__image">

        <div class="flex ai-center mt-2 mb-1">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>2 330 €</td>
                    <tr><td>2</td><td>1 770 €</td>
                    <tr><td>3</td><td>1 400 €</td>
                    <tr><td>4</td><td>934 €</td>
                    <tr><td>5</td><td>840 €</td>
                    <tr><td>6</td><td>746 €</td>
                    <tr><td>7</td><td>560 €</td>
                    <tr><td>8</td><td>410 €</td>
                    <tr><td>9</td><td>308 €</td>
                    <tr><td>10-19</td><td>186 €</td>
                    <tr><td>20-49</td><td>46 €</td>
                    <tr><td>50-100</td><td>28 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 14 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/pre-party/athena.png" alt="fly coins" class="tournament__image">

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
