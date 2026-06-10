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

    ol.tournament__list {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    ol.tournament__list li {
        font-size: 0.875rem;
        /*list-style: none;*/
        color: #fff;
    }

    ol.tournament__list li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }

    .tournament__table {
        border-spacing: 0;
        margin-left: auto;
        margin-right: auto;
    }

    .tournament__table td {
        padding: 0.5rem 1.5rem;
        color: #ffffff;
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

    .tournament__image {
        width: 20%;
        height: auto;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: #fff;
    }
    .article__text strong.gold{
        color: #d7b96b;
    }
    .tournament__list strong{
        color: #d7b96b;
    }
    .article__text b.red{
        color: red;
    }
    @keyframes gold-shine {
        from {filter: drop-shadow(0 0 0px goldenrod);}
        to {filter: drop-shadow(0 0 10px goldenrod);}
    }
    .shine{
        animation: gold-shine;
        animation-duration: 2s;
        filter: drop-shadow(0 0 10px goldenrod);
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-text">The <strong class="gold">Weekly Races 160k</strong> tournament takes place between <b class="red">0:01 UTC on the 15th of June 2023 and 25th of June 2023 until 23:59 UTC</b>, and it’s split into multiple phases.</div>
        <div class="tournament__cond-text"><strong>Phase 1:</strong> <b class="red">15.06.2023-18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Phase 2:</strong> <b class="red">22.06.2023-25.06.2023</b></div>

        <div style="display: flex; margin-top: -80px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/weekly-races/cup.png" alt="grass" style="height: 220px; margin-bottom: -80px; margin-right: 50px;">
        </div>

        <div class="tournament__cond-title">Competition:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li><strong>Weekly Races 40k tournament</strong> is available to play for real mode only.</li>
                <li>The minimum bet required to participate is <b class="red">0.20 EUR</b> or currency equivalent.</li>
                <li>Any bet will contribute to the <b class="red">Player's Total score</b> that will be shown in the<strong>Leaderboard</strong>. The total score is calculated by the addition of the points scored during all rounds played on participating games during the tournament period.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Prizes:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>The cash prizes will be credited to the players’ accounts as withdrawable fundsNetwork Promotion within <b class="red">72 hours (3 business days)</b> after the end of the promotion.</li>
                    <li>Prize pool and minimum bet for this campaign are set up in <b class="red">EUR</b> and might be subject to currency conversion fluctuations.</li>
                    <li>The currency conversion rates are being set automatically by our system with <b class="red">72 hours</b> before the tournament starts, and the values will remain the same during the whole tournament period.</li>
                    <li>We reserve the right to void scores, or not pay out a prize, where all or part of the score results from any obvious error, mistake or technical fault (including incorrect game payouts) whether caused by a machine or human error in respect of any of the participating games.</li>
                    <li>We further reserve the right to void scores, or not pay out a prize where, in our opinion, all or part of the score results from cheating or collusion with other players.</li>
                    <li>We reserve the right to amend or cancel the campaign at any time.</li>
                </ul>
            </div>
            <img src="/img/tournaments/weekly-races/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; height: 140px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Win Mechanics:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <strong>Stake Race (Sum of all BETS)</strong>: Points are calculated round-by-round, with each <b class="red">1 EUR wagered equaling 100 points</b>. For example, 5 EUR wagered will generate 500 points.
            </div>
        </div>

        <div class="tournament__cond-title">Prize Pool:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>40,000 EUR</strong>, split into <b class="red">2 tournaments of 20,000 EUR</b> each:</div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Place</th>
                    <th>Prize</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                <tr><td>2</td><td>€ 2,000</td><td>€ 2,000</td></tr>
                <tr><td>3</td><td>€ 1,000</td><td>€ 1,000</td></tr>
                <tr><td>4 - 10</td><td>€ 400</td><td>€ 2,800</td></tr>
                <tr><td>11 - 20</td><td>€ 150</td><td>€ 1,500</td></tr>
                <tr><td>21 - 50</td><td>€ 100</td><td>€ 3,000</td></tr>
                <tr><td>51 - 100</td><td>€ 50</td><td>€ 2,500</td></tr>
                <tr><td>101 - 150</td><td>€ 20</td><td>€ 1,000</td></tr>
                <tr><td>151 - 270</td><td>€ 10</td><td>€ 1,200</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 20,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
    </div>


    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
