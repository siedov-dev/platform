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
    .tournament__list strong{
        color: #00bcff;
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
                    <li>Evoplay Bet Trills Network Tournament is available to play in real mode
                        only.</li>
                    <li>Evoplay Bet Trills Network Tournament takes place between 00:01
                        UTC on <strong>15th May 2023 and 24th May 2023</strong> until 23:59 UTC</li>
                    <li>The tournament prize amount is <strong>€10,000</strong> and will be distributed among the
                        top <strong>100</strong> players who have the highest scores on the leaderboards.</li>
                    <li>The minimum bet required to participate in the tournament is <strong>0,20 EUR</strong> or
                        the equivalent in the local currency. The first <strong>100</strong> players in the leaderboard
                        will receive prizes.</li>
                    <li>The prize pool and minimum bet for this campaign are set in <strong>EUR</strong> and
                        might be subject to currency conversion fluctuations.</li>
                    <li>Every spin will contribute to the player’s total score that is shown on the
                        leaderboard. The total score is calculated with the points gained during all
                        rounds played in participating games.</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center mt-1 ai-center">
            <img src="/img/tournaments/bet-trills/girl1.png" alt="girl1" style="width: 140px;">
            <img src="/img/tournaments/bet-trills/evo.png" alt="evo" style="height: 25px; filter: drop-shadow(0 0 5px #FFFFFF);">
            <img src="/img/tournaments/bet-trills/girl2.png" alt="girl2" style="width: 110px;">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>The tournament has its qualified mechanic: <strong>Total Bet accumulation</strong>.</li>
                    <li><strong>Example:</strong> If you bet €1 and your winnings are €12,80, then
                        you earn 12.8 points. If in the second round of the game you
                        bet €1 and the winnings are €7,50, then you will earn 7.5
                        points, and the total number of points earned will be 20.3.
                        Any winnings with the minimum bet are converted to <strong>POINTS</strong></li>
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

        <div class="flex jc-center mt-1 ai-center">
            <img src="/img/tournaments/bet-trills/evo.png" alt="evo" style="margin-left: .5rem;height: 25px;">
    </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/bet-trills/tbg3.png"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 664 €</td>
                    <tr><td>2</td><td>1 270 €</td>
                    <tr><td>3</td><td>1 000 €</td>
                    <tr><td>4</td><td>667 €</td>
                    <tr><td>5</td><td>600 €</td>
                    <tr><td>6</td><td>539 €</td>
                    <tr><td>7</td><td>400 €</td>
                    <tr><td>8</td><td>300 €</td>
                    <tr><td>9</td><td>220 €</td>
                    <tr><td>10-19</td><td>133 €</td>
                    <tr><td>20-49</td><td>33 €</td>
                    <tr><td>50-100</td><td>20 €</td>
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
