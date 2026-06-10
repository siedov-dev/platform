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
            <h5>{{ __('tournament.duration') }}</h5>
            <h4>2024-06-20 - 2024-06-26/h4>
        </div>

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.prize_fund') }}</h5>
            <h4>€50,000</h4>
        </div>

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.minimum_bid') }}</h5>
            <h4>€0.5</h4>
        </div>

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.min_number_of_spins') }}</h5>
            <h4>200</h4>
        </div>

    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">{{ __('tournament.games') }}</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">{{ __('tournament.terms_conditions') }}</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">{{ __('tournament.prize_fund') }}</button>
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
                <div class="article__text">
                    @include('desktop.tournaments.terms.kz', ['provider' => '3Oaks'])
                </div>
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;text-transform: uppercase">REGULAR</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                    <tr>
                        <th>{{ __('tournament.place_from') }}</th>
                        <th>{{ __('tournament.prize') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>5 000,00</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>3 500,00</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>2 500,00</td>
                    </tr>
                    <tr>
                        <td>4-5</td>
                        <td>1 500,00</td>
                    </tr>
                    <tr>
                        <td>6-10</td>
                        <td>750,00</td>
                    </tr>
                    <tr>
                        <td>11-20</td>
                        <td>500,00</td>
                    </tr>
                    <tr>
                        <td>21-50</td>
                        <td>375,00</td>
                    </tr>
                    <tr>
                        <td>51-100</td>
                        <td>175,00</td>
                    </tr>
                    <tr>
                        <td>101-150</td>
                        <td>100,00</td>
                    </tr>
                    <tr>
                        <td>151-200</td>
                        <td>45</td>
                    </tr>
                    <tr>
                        <td style="color: green; text-transform: uppercase; font-weight: bold; text-align: right; padding-right: 0">Total</td>
                        <td>50 000,00</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

    <script>
        document.getElementById("pt_games").addEventListener("click", function(){

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
