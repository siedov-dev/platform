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
        white-space: nowrap;
        color: #e4b556;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Juicy Multiplier Race runs:</div>
        <div class="tournament__cond-text">From <strong>27.07.2023 9:00 UTC </strong>to <strong>31.07.2023 21:00 UTC</strong></div>

        <div class="tournament__cond-title">3 Oaks Network Tournament Terms and Conditions:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>By participating in the tournament you <strong>agree</strong> to the Terms and Conditions outlined here and the ones provided by the operator.</li>
                    <li>In cases of emergency, <strong>3 Oaks Gaming</strong> holds the right to cancel the promo at any time, including payments for winnings, if deemed necessary.</li>
                    <li>Only <strong>real money</strong> rounds can participate in the campaign.</li>
                    <li>Other than <strong>the price to make a spin</strong>, there is no additional cost to enter the tournament..</li>
                    <li>Each player will get a <strong>personal tournament ID</strong> for the leaderboard.</li>
                    <li>Tournament rules can be changed anytime.</li>
                    <li>Malfunction voids all payouts.</li>
                </ul>
            </div>
            <img src="/img/tournaments/juicy-multiplier-race/palm1.png" alt="palm" style="width: 320px; height: 380px; margin-bottom: 0px; margin-top: -100px;">
        </div>


        <div class="tournament__cond-title">Qualification:</div>
        <div class="flex">
            <div style="margin-top: 0px;">
                <img src="/img/tournaments/juicy-multiplier-race/cocktail2.png" alt="cocktail" style="margin-left: 1rem; width: 240px; margin-top: 20px">
            </div>
            <div class="tournament__cond-text"> To be qualified and have the right to claim the prize, the player must fulfill the following
                conditions:
                <ol class="tournament__list">
                    <li><strong>Minimum Bet</strong> is <span style="color: red; font-weight: bold">0.20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">and</span>
                    <li><strong>Minimum number</strong> of spins is <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                All points collected during the qualification stage are saved and counted for a player after
                passing the qualification. Non-qualified players are also shown on the Leaderboard, but the
                line for the Non-Qualified Player is striped in Grey until they are qualified.
            </div>
        </div>

        <div class="tournament__cond-title">Win Mechanics:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> MULTIPLIER RACE:</span> if the player placed a <strong>EUR 2</strong> total bet and won <strong>EUR 120</strong>, the player will get 120(win)÷2(bet)= a 60x multiplier = <strong>60 points</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Prize Pool:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">The total prize pool of the Tournament is <span style="color: red; font-weight: bold">EUR 50,000</span> (or the player's currency equivalent).</div>
            <div class="tournament__cond-text">Prize distribution table in <span style="color: red; font-weight: bold">EUR</span>:</div>
        </ul>


        <div class="flex ai-center jc-center mt-2">
        <div style='width: 80%; background-image: url("/img/tournaments/juicy-multiplier-race/beach.png"); background-position: center; background-size: cover;'>

            <table class="tournament__table"
                   style='box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D3160; width: 100%; text-shadow: #0B0D31 2px 2px 2px; font-weight: 800;'>
                <thead>
                <tr>
                    <th>Place</th>
                    <th>Prize</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                <tr><td>2</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                <tr><td>3</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                <tr><td>4 - 5</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                <tr><td>6 - 10</td><td>€ 750</td><td>€ 3,750</td></tr>
                <tr><td>11 - 20</td><td>€ 500</td><td>€ 5,000</td></tr>
                <tr><td>21 - 50</td><td>€ 375</td><td>€ 11,250</td></tr>
                <tr><td>51 - 100</td><td>€ 175</td><td>€ 8,750</td></tr>
                <tr><td>101 - 150</td><td>€ 100</td><td>€ 5,000</td></tr>
                <tr><td>151 - 200</td><td>€ 45</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
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
