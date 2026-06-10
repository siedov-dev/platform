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
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 24%;
        padding: 20px 0;
        border-radius: 10px;
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
            <h4>09.02.2024 - 05.01.2025</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Prize fund</h5>
            <h4>$1,000,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Minimum bid</h5>
            <h4>$0.2</h4>
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
                To participate, players must click on JOIN in the in-game widget and play the qualifying games with a
                minimum bet of €0.20 or its currency equivalent per spin. Players can follow their score on the in-game live
                leader board. In order for their score to be valid and displayed on the leader board, a player must complete
                at least one cycle of 30 spins. A player can complete an unlimited number of cycles, but only the
                complete cycle with the highest result qualifies for the leader board.<br>
                “Yeet&Sweet” is available to play with real money only.<br><br>
                How do Yeet&Sweet tournaments work?<br>
                Players need to simply make winning bets in any of the eligible games and then they’ll be awarded
                leader board points. Please note that playing over multiple games is allowed in Yeet&Sweet, so players
                may only play one game at any given time but may switch between eligible games at any point.<br>
                The points system is based on total accumulated multipliers over the cycle of the 30 rounds of spins.
                Points per spin are calculated as multipliers – the amount won in each spin is divided by the amount
                wagered. For example: if player bets €1 and the win is €10 it means that win-multiplier is 10. Points are
                calculated as win-multiplier multiplied by 100. So in this example, the player will receive 1000 points
                when win-multiplier is 10.<br>
                Please note that the Gamble feature does not contribute to this calculation. Only wins from the base
                game are counted; any points acquired using the Gamble feature will be nullified. For example, if a player
                bets €1 and wins €10, and then increases the win by playing the Gamble feature, only 1000 points will
                be awarded.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style=" color: #fff; margin-bottom: 10px; ">Tournaments</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Tournament 1</td>
                        <td>Sweetheart Dragon </td>
                    </tr>
                    <tr>
                        <td>Tournament 2</td>
                        <td>Sweet Greens</td>
                    </tr>
                    <tr>
                        <td>Tournament 3</td>
                        <td>Candy Blossoms</td>
                    </tr>
                    <tr>
                        <td>Tournament 4</td>
                        <td>Lucky Loser</td>
                    </tr>
                    <tr>
                        <td>Tournament 5</td>
                        <td>Joker’s Sweets</td>
                    </tr>
                    <tr>
                        <td>Tournament 6</td>
                        <td>Fruitish</td>
                    </tr>
                    <tr>
                        <td>Tournament 7</td>
                        <td>Golden Dunes</td>
                    </tr>
                    <tr>
                        <td>Tournament 8</td>
                        <td>Sugar & Skulls</td>
                    </tr>
                    <tr>
                        <td>Tournament 9</td>
                        <td>Candy Cane Cup</td>
                    </tr>
                    <tr>
                        <td>Tournament 10</td>
                        <td>ULTIMATE 100</td>
                    </tr>
                </tbody>
            </table>
            <h4 style=" color: #fff; margin-bottom: 10px; margin-top: 30px;">Prizes</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>Position</th>
                        <th>Prize</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>€31,000</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>€19,000</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>€12,000</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>€6,000</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>€4,000</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>€3,600</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>€3,200</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>€2,800</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>€2,400</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>€2,000</td>
                    </tr>
                    <tr>
                        <td>10-20</td>
                        <td>€300</td>
                    </tr>
                    <tr>
                        <td>21-30</td>
                        <td>€250</td>
                    </tr>
                    <tr>
                        <td>31-40</td>
                        <td>€200</td>
                    </tr>
                    <tr>
                        <td>41-50</td>
                        <td>€150</td>
                    </tr>
                    <tr>
                        <td>51-100</td>
                        <td>€100</td>
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
