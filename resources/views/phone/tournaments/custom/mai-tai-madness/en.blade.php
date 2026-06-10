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
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tourn_stats{
        display: block;
        justify-content: space-between;
        margin-top: 20px;
    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 100%;
        padding: 20px 0;
        border-radius: 10px;
        margin-bottom: 10px;
    }
    .tourn_stat_itm h5{
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
    }
    .tourn_stat_itm h4{
        color: #FFC660;
        font-size: 16px
    }
    .tabs_tour{
        display: flex;
        background: #181833;
        margin-top: 30px;
        padding: 5px;
        justify-content: space-between;
    }
    .tabs_tour button{
        width: 32%;
        background: transparent;
        border: none;
        padding: 7px;
        border-radius: 5px;
        color: #8C90CA;
        cursor: pointer;
    }
    .tabs_tour button.active{
        background: #2B2F5B;
        color: #fff;
    }
    .tabs_tour button:hover{
        background: #2B2F5B;
        color: #fff;
    }
    .panes_tour{
        margin-top: 30px;
    }
    .panes_tour_itm{
        display: none;
    }
    .panes_tour_itm.active{
        display: block;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">
        <div class="tourn_stat_itm">
            <h5>Tournament duration</h5>
            <h4>15.05.2024 - 28.05.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Prize fund</h5>
            <h4>€70,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Minimum bid</h5>
            <h4>€0.4</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Min. number of spins</h5>
            <h4>100</h4>
        </div>
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">Games</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">Terms and Conditions</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">Prizes</button>
    </div>
    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
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
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                1. Evoplay Network Campaign is available to play in real mode only and
                consist of two tournaments that happen in sequence: Mai Tai Madness
                Tournament and Mai Tai Madness Prize Drops Campaign.<br>
                2. Evoplay Network Campaign runs for (14) agreed days and takes place
                between 00:01 UTC on 15th May 2024 and 28th May 2024 until 23:59 UTC.<br>
                3. The total tournament prize amount is €70,000.<br>
                4. The leaderboard is consolidated for all operators participating in this
                network campaign.<br>
                5. All prizes are deducted from the GGR before invoicing regardless of the
                percentage of Bonus Deduction according to the contract.<br>
                6. The prize pool and minimum bet for this campaign are set in EUR and
                might be subject to currency conversion fluctuations.<br>
                7. The campaign has a list of qualified games and a prize pool (EUR), which
                is distributed among the winners according to the tournament rules.<br>
                8. Every qualified spin will contribute to the player’s total score that is
                shown on the leaderboard. The total score is calculated with the points
                gained during all rounds played in participating games. The more points, the
                higher the player gets on the leaderboard.<br>
                9. Evoplay reserves the right to void scores, or not pay out a prize, where all
                or part of the score results from any obvious error, mistake, or technical
                fault (including incorrect game payouts) caused by a machine or human
                error to any of the participating games.<br>
                10. Evoplay further reserves the right to void scores, or not pay out a prize
                where, in opinion, all part of the score results from cheating or collusion
                with other players.<br>
                11. The compensation system is calculated in accordance with the prize pool
                and set amount of participating leaders.<br>
                12. A casino must support the tournament by implementing the list of
                marketing activities provided below.<br>
                13. Players will receive a pop-up announcement about the start of the
                tournament two (2) days before the tournament.<br>
                14. Submit your participation in the tournament minimum seven (7) days
                before the start of the tournament.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;">Mai Tai Madness Tournament</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                <tr>
                    <th>Place</th>
                    <th>Prize</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 3500</td></tr>
                <tr><td>2</td><td>€ 2800</td></tr>
                <tr><td>3</td><td>€ 2450</td></tr>
                <tr><td>4</td><td>€ 2100</td></tr>
                <tr><td>5</td><td>€ 1750</td></tr>
                <tr><td>6</td><td>€ 1400</td></tr>
                <tr><td>7</td><td>€ 1155</td></tr>
                <tr><td>8</td><td>€ 980</td></tr>
                <tr><td>9</td><td>€ 910</td></tr>
                <tr><td>10</td><td>€ 455</td></tr>
                <tr><td>11-20</td><td>€ 280</td></tr>
                <tr><td>21-50</td><td>€ 140</td></tr>
                <tr><td>51-100</td><td>€ 105</td></tr>
                <tr><td>101-150</td><td>€ 70</td></tr>
                <tr><td>151-200</td><td>€ 35</td></tr>
                <tr>
                    <td colspan="1"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">TOTAL 35,000 EUR</span></td>
                </tr>
                </tbody>
            </table>
            <h4 style="margin-top: 30px; color: #fff; margin-bottom: 15px;">Mai Tai Madness Prize Drops Campaign</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0; ">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 400</td></tr>
                <tr><td>2</td><td>€ 200</td></tr>
                <tr><td>7</td><td>€ 100</td></tr>
                <tr><td>10</td><td>€ 80</td></tr>
                <tr><td>20</td><td>€ 40</td></tr>
                <tr><td>60</td><td>€ 15</td></tr>
                <tr><td>100</td><td>€ 10</td></tr>
                <tr>
                    <td colspan="1"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">
                        TOTAL 35,000 EUR: 5000 EUR/200 prizes daily</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>




    <script>
        document.getElementById("pt_games").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_games");
            pane.classList.add('active');

            let but = document.getElementById("pt_games");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
        document.getElementById("pt_terms").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_terms");
            pane.classList.add('active');

            let but = document.getElementById("pt_terms");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
        document.getElementById("pt_prizes").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_prizes");
            pane.classList.add('active');

            let but = document.getElementById("pt_prizes");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
    </script>
    @include('desktop.footer')

</div>
