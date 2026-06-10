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
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">The Lucky Drops event runs:</div>
        <div class="tournament__cond-text mb-1">From <strong>24th to 27th of August 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="seaguls" style="width: 180px; height: 160px; margin-bottom: 10px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Hot Period</span> - increases the player's chance of winning a prize.:</div>
        <div class="tournament__cond-text">Start from <span style="color: red; font-weight: bold">26.08.2023 0:00 UTC</span> till the end of <strong>Weekend Lucky Drops</strong> campaign</div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/cocktail3.png" alt="cocktail" style="width: 150px;">
        </div>

        <div class="tournament__cond-title">3 Oaks Network Tournament Terms and Conditions:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>By participating in the tournament you agree to the Terms and Conditions outlined here and the ones provided by the operator.</li>
                <li>In cases of emergency, 3 Oaks Gaming holds the right to cancel the Lucky Drops promo at any time, including payments for winnings, if deemed necessary.</li>
                <li>Only real money rounds can participate in the Lucky Drops campaign.</li>
                <li>Other than the price to make a spin, there is no additional cost to enter the Network Lucky Drops.</li>
                <li>Each player will get a personal tournament ID for the leaderboard.</li>
                <li>Network Lucky Drops rules can be changed anytime.</li>
                <li>Malfunction voids all payouts.</li>
            </ul>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacation" style="width: 250px;">
        </div>

        <div class="tournament__cond-title">Qualification:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>A player must join or opt into any of the participating games.</li>
                    <li>The minimum bet required to be able to receive a LuckyDrop is <span style="color: red; font-weight: bold">0.50 EUR</span> or the equivalent amount in the available currencies in the participating game(s).</li>
                    <li>Every qualifying bet placed on any of the participating games may trigger a random prize from the prize pool during the promotion period.</li>
                    <li>Each player can win Maximum <span style="color: red; font-weight: bold">3 prizes</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Win Mechanics:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops -</span> offers players the possibility of winning cash prizes at any time during the event period. The prizes are granted randomly, and all players have the same chance of winning in every qualifying spin.
            </div>
        </div>

        <img src="/img/tournaments/summer-lucky-drops/man_hammok.png" alt="beach" style="width: 100%;">

        <div class="tournament__cond-title">Games participating:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">All games</span></div>
        <div class="tournament__cond-title">Prize Pool:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">The Prize Pool is 15`000 EUR for this Campaign (or player’s currency equivalent) combined from several pools by the following structure:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15`000 EUR</span> for the period of 24.08.2023 00:00 UTC to 27.08.2023 23:00 UTC</div>
        </ul>

        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>15</td><td>€ 100</td><td>€ 1,500</td></tr>
                <tr><td>30</td><td>€ 60</td><td>€ 1,800</td></tr>
                <tr><td>45</td><td>€ 50</td><td>€ 2,250</td></tr>
                <tr><td>60</td><td>€ 40</td><td>€ 2,400</td></tr>
                <tr><td>80</td><td>€ 30</td><td>€ 2,400</td></tr>
                <tr><td>120</td><td>€ 20</td><td>€ 2,400</td></tr>
                <tr><td>150</td><td>€ 15</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/summer-lucky-drops/water3.png" alt="wave" style="width: 100%; margin-top: -100%; z-index: -5; height: 120px;">
    </div>


    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
