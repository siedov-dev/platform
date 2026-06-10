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
        width: 80%;
        height: auto;
        margin: 0 auto;
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


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay Spins of Love Network Tournament is available to play in real mode only</li>

                    <li>Evoplay Network Tournament runs for twelfth (12) agreed days in the
                        month of February 2022. The tournament will take place between 00:01
                        UTC on 8th February 2022 and 19th February2022 until 23:59 UTC.</li>

                    <li>The total tournament prize amount is €12,000 and will be distributed among
                        the top 100 players who have the highest scores on the leaderboards.</li>

                    <li>The minimum bet required to participate in the tournament is 0,20 EUR or the
                        equivalent in the local currency. The more points, the higher the player gets on
                        the leaderboard. The first 100 players on the leaderboard will receive prizes.</li>

                    <li>Prize pool and minimum bet for this campaign are set in EUR and might be
                        subject to currency conversion fluctuations.</li>
                </ul>
            </div>

        </div>
        <img src="/img/tournaments/spins-of-love/masks.png" alt="Gambling lady" class="tournament__image">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
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
        <img src="/img/tournaments/spins-of-love/sphere.png" alt="Gambling man" class="tournament__image">


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay reserves the right to amend, suspend or cancel the promotion any
                        time and without prior notice.</li>

                    <li>The compensation system is calculated in accordance with the prize pool and
                        set amount of participating leaders.</li>

                    <li><b>Games participating:</b>
                        Midnight show, Candy Dreams, Anubis Moon, Indiana's quest,
                        Curse Of The Pharaon, Runes Of Destiny, Dragon's Tavern, Forest Dreams, Tree Of Light</li>

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


        <img src="/img/tournaments/spins-of-love/box.png" alt="Gambling girl" class="tournament__image">

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>2 000 €</td></tr>
                    <tr><td>2nd</td><td>1 520 €</td></tr>
                    <tr><td>3rd</td><td>1 200 €</td></tr>
                    <tr><td>4th</td><td>800 €</td></tr>
                    <tr><td>5th</td><td>720 €</td></tr>
                    <tr><td>6th</td><td>640 €</td></tr>
                    <tr><td>7th</td><td>480 €</td></tr>
                    <tr><td>8th</td><td>352 €</td></tr>
                    <tr><td>9th</td><td>264 €</td></tr>
                    <tr><td>10th - 19th</td><td>160 €</td></tr>
                    <tr><td>20th - 49th</td><td>40 €</td></tr>
                    <tr><td>50th - 100th</td><td>24 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 12 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/spins-of-love/girl.png" alt="Gambling man" class="tournament__image">


    </div>



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

    @include('phone.footer')

</div>
