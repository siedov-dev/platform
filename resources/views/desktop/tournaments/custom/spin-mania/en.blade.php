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
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>SpinMania 30k tournament is available to play for real mode only</li>

                    <li>SpinMania 30k tournament runs between 0:01 UTC on the 26th of May 2022 and 29th of
                        May 2022 until 23:59 UTC.</li>

                    <li>The minimum bet required to participate is 0.40 EUR or currency equivalent.</li>

                    <li>Every spin of 0.40 EUR or more will contribute to the Player's Total score that will be shown
                        in the Leaderboard. The total score is calculated by the addition of the points scored during
                        all rounds played on participating games during the tournament period.</li>

                    <li>Points are calculated round-by-round, with each paid spin equaling 1 point. Total score=
                        total number of spins of 0.40 EUR or more.</li>

                    <li>The cash prizes will be credited to the players’ accounts as withdrawable funds within 72
                        hours (3 business days) after the end of the promotion.</li>

                </ul>
            </div>

            <img src="/img/tournaments/spin-mania/lion-head.png" alt="Lion head" class="tournament__image" style="margin-left: 3rem;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image" style="margin-right: 3rem;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Prize pool and minimum bet for this campaign are set up in EUR and might be subject to
                        currency conversion fluctuations.</li>

                    <li>The currency conversion rates are being set automatically by our system with 72 hours
                        before the tournament starts, and the values will remain the same during the whole
                        tournament period.</li>

                    <li>We reserve the right to void scores, or not pay out a prize, where all or part of the score
                        results from any obvious error, mistake or technical fault (including incorrect game payouts)
                        whether caused by a machine or human error in respect of any of the participating games.</li>

                    <li>We further reserve the right to void scores, or not pay out a prize where, in our opinion, all
                        or part of the score results from cheating or collusion with other players.</li>

                    <li>We reserve the right to amend or cancel the campaign at any time.</li>

                </ul>
            </div>
        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                        <th>Sum</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>2nd</td><td>2 5000 €</td><td>2 500 €</td></tr>
                    <tr><td>3rd</td><td>1 500 €</td><td>1 500 €</td></tr>
                    <tr><td>4th -10th</td><td>500 €</td><td>3 500 €</td></tr>
                    <tr><td>11th -25th</td><td>250 €</td><td>3 750 €</td></tr>
                    <tr><td>26th - 50th</td><td>100 €</td><td>2 500 €</td></tr>
                    <tr><td>51th - 100th</td><td>75 €</td><td>3 750 €</td></tr>
                    <tr><td>101th - 200th</td><td>50 €</td><td>5 000 €</td></tr>
                    <tr><td>201th - 300th</td><td>25 €</td><td>2 500 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b>Grand Total 30 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/spin-mania/fly-coins.png" alt="fly coins" class="tournament__image" style="margin-left: 3rem;">
        </div>




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

    @include('desktop.footer')

</div>
