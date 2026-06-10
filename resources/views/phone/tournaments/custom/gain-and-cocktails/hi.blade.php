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
        color: #8888af;
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
        <img src="{{ $tournament->getBannerUrl() }}" width="100%">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Tournament period:</div>
        <div class="tournament__cond-text">From 4th to 10th of August 2022 (both days included)</div>

    <div class="tournament__cond-title">3 Oaks Network Tournament Terms and Conditions:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>By participating in the tournament you agree to the Terms and Conditions outlined here and the ones provided by the
                    operator.</li>
                <li>3 Oaks holds the right to cancel the tournament for the indefinite term without any payments according to the
                    leaderboard in cases of emergency.</li>
                <li>Only qualified players can claim the prize (please, check Campaign Details Section).</li>
                <li>Only real money rounds can participate in the tournament.</li>
                <li>Other than the price to make a spin, there is no additional cost to enter the tournament.</li>
                <li>Each player will get a personal tournament ID for the leaderboard.</li>
                <li>If two or more players are tied, those who get results earlier are the ones who get better prizes.</li>
                <li>Tournament rules can be changed anytime.</li>
                <li>Malfunction voids all payouts.</li>
            </ul>
        </div>
        <div class="flex jc-center ai-center mt-2">
            <img src="/img/tournaments/gain-and-cocktails/red_cocktail.png" alt="Gambling man" class="tournament__image">
            <img src="/img/tournaments/gain-and-cocktails/blue_cocktail.png" alt="Gambling man" class="tournament__image">
        </div>

        <div class="tournament__cond-title">Qualification:</div>
        <div class="tournament__cond-text"> To be qualified and have a right to claim the prize player should fulfill the following
            conditions:
            <ol class="tournament__list">
                <li><strong>Minimum Bet</strong> is <span style="color: red; font-weight: bold">1.00 EUR,</span></li>
                <span style="color: white; text-transform: uppercase; font-weight: bold">and</span>
                <li><strong>Minimum number</strong> of spins is <span style="color: red; font-weight: bold">150</span>.</li>
            </ol>
            All points collected during the qualification stage are saved and considered for a player
            after passing the qualification. Non-qualified players are also shown on the Leaderboard,
            but stripped and in gray until they are qualified.
        </div>

        <div class="tournament__cond-title">Tournament Scoring (Winning Mechanic):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> MULTIPLIER RACE:</span> player wagered 2 EUR total bet and won 120 EUR, player will get
                                120(win)/2(bet)= x60 multiplier - 60 points
            </div>
        </div>


        <div class="tournament__cond-title">Progressive Prize Pool:</div>
        <div class="flex ai-center mt-2">
            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Player</th>
                    <th>Prize</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>10%</td><td>10%</td></tr>
                    <tr><td>2nd</td><td>8%</td><td>8%</td></tr>
                    <tr><td>3rd</td><td>6%</td><td>6%</td></tr>
                    <tr><td>4th</td><td>5%</td><td>5%</td></tr>
                    <tr><td>5th</td><td>4%</td><td>4%</td></tr>
                    <tr><td>6th</td><td>2.5%</td><td>2.5%</td></tr>
                    <tr><td>7th</td><td>1.6%</td><td>1.6%</td></tr>
                    <tr><td>8th</td><td>0.9%</td><td>0.9%</td></tr>
                    <tr><td>9th</td><td>0.8%</td><td>0.8%</td></tr>
                    <tr><td>10th - 20th</td><td>0.7%</td><td>7.7%</td></tr>
                    <tr><td>21th - 30th</td><td>0.6%</td><td>6%</td></tr>
                    <tr><td>31th - 40th</td><td>0.5%</td><td>5%</td></tr>
                    <tr><td>41st - 50th</td><td>0.4%</td><td>4%</td></tr>
                    <tr><td>51st - 100th</td><td>0.3%</td><td>15%</td></tr>
                    <tr><td>101st - 150th</td><td>0.2%</td><td>10%</td></tr>
                    <tr><td>151st - 200th</td><td>0.15%</td><td>7.5%</td></tr>
                    <tr><td>201st - 250th</td><td>0.12%</td><td>6%</td></tr>
                </tbody>
            </table>

        </div>
        <div class="flex jc-center ai-center mt-2">
            <img src="/img/tournaments/gain-and-cocktails/blue_cocktail.png" alt="Gambling man" class="tournament__image">
            <img src="/img/tournaments/gain-and-cocktails/red_cocktail.png" alt="Gambling man" class="tournament__image">
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
