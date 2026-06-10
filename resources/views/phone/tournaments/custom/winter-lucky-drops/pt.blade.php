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
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Network Campaign period:</div>
        <div class="tournament__cond-text">From <strong>25.12.2023 00:00 UTC</strong> to <strong>01.01.2024 23:59 UTC</strong></div>


        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Hot Period</span> - From 29.12.2023 15:00 UTC</div>

        <div class="tournament__cond-title">3 Oaks Network Tournament Terms and Conditions:</div>
        <div class="tournament__cond-text">
            <p>
                1. By participating in the Network Lucky Drops, you agree to the Terms and Conditions outlined here and the ones provided by
                the operator.<br>
                2. In cases of emergency, 3 Oaks Gaming holds the right to cancel the Lucky Drops promo at any time, including payments for
                winnings, if deemed necessary.<br>
                3. Only real money rounds can participate in the Lucky Drops campaign.<br>
                4. Other than the price to make a spin, there is no additional cost to enter the Network Lucky Drops.<br>
                5. Network Lucky Drops rules can be changed anytime.<br>
                6. Malfunction voids all payouts.
            </p>
        </div>


        <div class="tournament__cond-title">Qualification:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>A player must join or opt into any of the participating games.</li>
                    <li>The minimum bet required to be able to receive a LuckyDrop is <span style="color: red; font-weight: bold">EUR 0.50</span> or the equivalent amount in the available currencies in the participating game(s).</li>
                    <li>Every qualifying bet placed on any of the participating games may trigger a random prize from the prize pool during the promotion period.</li>
                    <li>Each player can win Maximum 3 prizes.</li>
                </ul>
            </div>
            <img src="/img/tournaments/winter-lucky-drops/snowman.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Win Mechanics:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops -</span> offers players the possibility of winning cash prizes at any time during the event period. The prizes are granted randomly, and all players have the same chance of winning in every qualifying spin.
            </div>
        </div>
        <div class="tournament__cond-title">Games participating:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">All games</span></div>
        <div class="tournament__cond-title">Prize Pool:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Prize Pool is 30,000 EUR for this Campaign (or player’s currency equivalent) combined from several pools by the following structure:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">30,000 EUR</span> for (25.12.2023 00:00 UTC to 01.01.2023 23:59 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">30 000 EUR</span> in Total for <span style="color: red; font-weight: bold">Each Week</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>15</td><td>€ 200</td><td>€ 3,000</td></tr>
                    <tr><td>30</td><td>€ 100</td><td>€ 3,000</td></tr>
                    <tr><td>60</td><td>€ 75</td><td>€ 4,500</td></tr>
                    <tr><td>80</td><td>€ 60</td><td>€ 4,800</td></tr>
                    <tr><td>105</td><td>€ 50</td><td>€ 5,250</td></tr>
                    <tr><td>130</td><td>€ 30</td><td>€ 3,900</td></tr>
                    <tr><td>150</td><td>€ 20</td><td>€ 3,000</td></tr>
                    <tr><td>170</td><td>€ 15</td><td>€ 2,550</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
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
