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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay Instant Network Tournament is available for playing in real mode only.</li>

                    <li>Evoplay Instant Network Tournament runs 1 (one) week with (7) agreed days
                        (Monday-Sunday) in the month of January 2022. The tournament will take
                        place between 0:01 UTC on the 25th of January, 2022 and 31st of January,
                        2022 until 23:59 UTC.</li>

                    <li>The total tournament prize amount is €7,000 and will be distributed among
                        the top 100 players who have the highest scores on the leaderboards.</li>

                    <li>The minimum bet required to participate in the tournament is 0,50 EUR or the
                        equivalent in the local currency. The more points, the higher the player gets on
                        the leaderboard. The first 100 players on the leaderboard will receive prizes.</li>

                    <li>Prize pool and minimum bet for this campaign are set in EUR and might be
                        subject to currency conversion fluctuations.</li>
                </ul>
            </div>
        </div>


        <img src="/img/tournaments/instant-network/man.png?t=5" alt="Gambling man" class="tournament__image">
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Every spin will contribute to the player’s total score that is shown on the
                        leaderboard. The total score is calculated by the addition of the points gained
                        during all rounds played in participating games.</li>

                    <li>The tournament has its qualified mechanic: Total Win accumulation.</li>

                    <li>The tournament has a list of qualified games and a prize pool (EUR), which is
                        distributed among the leaderboard winners according to these tournament
                        rules.</li>

                    <li>If several Participants gain the same number of points during the process of
                        calculating the tournament’s results, a higher position in the ranking is given
                        to the Participant who scored first.</li>

                    <li>The cash prizes will be credited to the players’ accounts as withdrawable
                        funds by the casino after the end of the tournament.</li>

                    <li>Evoplay reserves the right to void scores, or not pay out a prize, where all or
                        part of the score results from any obvious error, mistake, or technical fault
                        (including incorrect game payouts) whether caused by a machine or human
                        error in respect to any of the participating games.</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/instant-network/girl.png?t=5" alt="Gambling girl" class="tournament__image">
        <div class="flex">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay reserves the right to amend, suspend or cancel the promotion any
                        time and without prior notice.</li>

                    <li>The compensation system is calculated in accordance with the prize pool and
                        set amount of participating leaders.</li>

                    <li><b>Games participating:</b>
                        Penalty Shoot-out, Wheel of Time, Book Of Keno, Roll To Luck,
                        Bonanza Wheel</li>

                    <li>Tournament rankings: Total Win accumulation.</li>

                    <li><b>Mechanic:</b> Sum all win amounts where 1 Point = 1
                        Monetary Unit of tournament base currency. Convert all
                        other currency win values to the currency of tournament
                        base currency. Only bets with an amount greater or equal
                        to the minimum bet amount of the tournament are taken
                        into account.</li>
                </ul>
            </div>
        </div>


        <img src="/img/tournaments/instant-network/boy.png?t=5" alt="Gambling man" class="tournament__image mt-2">
        <div class="flex mt-2">

            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>1 170 €</td></tr>
                    <tr><td>2nd</td><td>887 €</td></tr>
                    <tr><td>3rd</td><td>700 €</td></tr>
                    <tr><td>4th</td><td>467 €</td></tr>
                    <tr><td>5th</td><td>420 €</td></tr>
                    <tr><td>6th</td><td>373 €</td></tr>
                    <tr><td>7th</td><td>280 €</td></tr>
                    <tr><td>8th</td><td>205 €</td></tr>
                    <tr><td>9th</td><td>154 €</td></tr>
                    <tr><td>10th - 19th</td><td>93 €</td></tr>
                    <tr><td>20th - 49th</td><td>23 €</td></tr>
                    <tr><td>50th - 100th</td><td>14 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 7 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>




    </div>



    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
            <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>


</div>


@include('phone.footer')
