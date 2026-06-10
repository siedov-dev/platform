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
    ol.tournament__list  li {
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Tournament period:</div>
        <div class="tournament__cond-text">From March 23 to March 27 (both days included)</div>

    <div class="tournament__cond-title">3 Oaks Network Tournament Terms and Conditions:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>By participating in the tournament you agree to the Terms and Conditions outlined here and the ones provided by the
                    operator.</li>
                <li>In cases of emergency, 3 Oaks Gaming holds the right to cancel the tournament promo at any time, including payments for winnings, if deemed necessary.</li>
                <li>Only qualified players can claim the prize.</li>
                <li>Only real money rounds can participate in the tournament.</li>
                <li>Other than the price to make a spin, there is no additional cost to enter the tournament.</li>
                <li>Each player will get a personal tournament ID for the leaderboard.</li>
                <li>If two or more players are tied, those who get results earlier are the ones who get better prizes.</li>
                <li>Tournament rules can be changed anytime.</li>
                <li>Malfunction voids all payouts.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Qualification:</div>
        <div class="flex">
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
            <img src="/img/tournaments/gold-nuggets/goldbarrel.png" alt="pumpkin" style="margin-left: 1rem; height: 170px; filter: drop-shadow(0px 0px 7px #fcc339)">
        </div>


        <div class="tournament__cond-title">Tournament Scoring (Winning Mechanic):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> WIN RACE:</span> player wagered 1 EUR total bet and won 120 EUR, player will get 120 Points
            </div>
        </div>


        <div class="tournament__cond-title">Prize Pool:</div>
        <div class="flex ai-fe mt-4">
            <img src="/img/tournaments/gold-nuggets/oakgold.png" style="transform: scale(-1, -1);">

            <table class="tournament__table" style="width: 50%; background: #0a0a17A0; background-size: cover; z-index: 2; border-radius: 10px; margin: 1rem">
                <thead>
                <tr>
                    <th>Player</th>
                    <th>Prize</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                    <tr><td>2nd</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                    <tr><td>3rd</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                    <tr><td>4th - 5th</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                    <tr><td>6th - 10th</td><td>€ 750</td><td>€ 3,750</td></tr>
                    <tr><td>11th - 20th</td><td>€ 500</td><td>€ 5,000</td></tr>
                    <tr><td>21th - 50th</td><td>€ 375</td><td>€ 11,250</td></tr>
                    <tr><td>51th - 100th</td><td>€ 175</td><td>€ 8,750</td></tr>
                    <tr><td>101th - 150th</td><td>€ 100</td><td>€ 5,000</td></tr>
                    <tr><td>151th - 200th</td><td>€ 45</td><td>€ 2,250</td></tr>
                    <tr><td colspan="2"></td><td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td></tr>
                </tbody>
            </table>

            <img src="/img/tournaments/gold-nuggets/oakgold.png" style="transform: scale(1, -1);">

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
