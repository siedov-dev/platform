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
        white-space: nowrap;
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
        <div class="tournament__cond-text">The <strong class="gold">Non-Stop Drop June 150,000 EUR</strong> event runs between <b class="red">0:01 UTC on
                01.06.2023</b> and <b class="red">30.06.2023 until 23:59 UTC</b>, and split in <b class="red">Weekly</b> phases</div>
        <div class="tournament__cond-text"><strong>Phase 1:</strong> <b class="red">01.06.2023 - 11.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Phase 2:</strong> <b class="red">12.06.2023 - 18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Phase 3:</strong> <b class="red">19.06.2023 - 25.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Phase 4:</strong> <b class="red">26.06.2023 - 30.06.2023</b></div>
        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/non-stop/cup.png" alt="grass" style="height: 120px; margin-bottom: 30px;">
        </div>

        <div class="tournament__cond-text">
            The <b class="red">Cash Blast</b> feature offers players the possibility of winning cash prizes at any
            time during the event period. The prizes are offered randomly, and each player has
            the same chances of winning after every spin.
        </div>

        <div class="tournament__cond-title">Competition:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li><strong>Non-Stop Drop 1,000,000 EUR</strong> is available to play for real mode only.</li>
                <li>The minimum bet required to participate is <b class="red">0.20 EUR</b> or currency equivalent.</li>
                <li>Any real money spin on the qualifying games can trigger a random <b class="red">Cash Blast</b> prize.</li>
                <li>Any individual player can win multiple <b class="red">Cash Blast</b> prizes during the event period.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Prizes:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>The <strong>cash prizes</strong> will be automatically credited to the players’ accounts as soon as
                        the win is triggered. Once a prize is credited, it will be removed from the prize pool.
                        The number of remaining prizes is shown in real time, on the <b class="red">Cash Blast widget</b> or in
                        <b class="red">the Prizes section</b>.</li>
                    <li>Prize pool and minimum bet for this campaign are set up in <b class="red">EUR</b> and might be subject to currency conversion fluctuations.</li>
                    <li>The currency conversion rates are being set automatically by our system with <b class="red">72 hours</b> before the tournament starts, and the values will remain the same during the whole tournament period.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center mt-1">
            <img src="/img/tournaments/non-stop/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>

        <div class="tournament__cond-title">General rules:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Playson reserves the right to amend, suspend or cancel the Network Promotion at
                        any time, including any related T&C. </li>
                    <li>We reserve the right to void scores, or not pay out a prize, where all or part of the score results from any obvious error, mistake or technical fault (including incorrect game payouts) whether caused by a machine or human error in respect of any of the participating games.</li>
                    <li>We further reserve the right to void scores, or not pay out a prize where, in our opinion, all or part of the score results from cheating or collusion with other players.</li>
                    <li>We reserve the right to amend or cancel the campaign at any time.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Prize Tables:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>150,000 EUR/ month</strong>, <b class="red">37,500 EUR/ week</b></div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Prize Value</th>
                    <th>Quantity</th>
                    <th>Amount EUR</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>€ 5,000</td><td>1</td><td>€ 5,000</td></tr>
                <tr><td>€ 2,500</td><td>2</td><td>€ 5,000</td></tr>
                <tr><td>€ 1,000</td><td>4</td><td>€ 4,000</td></tr>
                <tr><td>€ 500</td><td>10</td><td>€ 5,000</td></tr>
                <tr><td>€ 250</td><td>20</td><td>€ 5,000</td></tr>
                <tr><td>€ 100</td><td>40</td><td>€ 4,000</td></tr>
                <tr><td>€ 75</td><td>50</td><td>€ 3,750</td></tr>
                <tr><td>€ 50</td><td>60</td><td>€ 3,000</td></tr>
                <tr><td>€ 25</td><td>70</td><td>€ 1,750</td></tr>
                <tr><td>€ 10</td><td>100</td><td>€ 1,000</td></tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><b class="red">357</b></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 37,500</span></td>
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
