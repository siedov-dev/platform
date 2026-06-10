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
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: initial;">
    </div>


    <div class="article__text flex column">
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

        </div>
        <img src="/img/tournaments/spin-mania/lion-head.png" alt="Lion head" class="tournament__image" class="tournament__image">


        <div class="flex ai-center mt-2">
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
        <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image">


        <div class="flex ai-center mt-2 mb-1">
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
                    <tr><td>1</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>2</td><td>2 5000 €</td><td>2 500 €</td></tr>
                    <tr><td>3</td><td>1 500 €</td><td>1 500 €</td></tr>
                    <tr><td>4 -10</td><td>500 €</td><td>3 500 €</td></tr>
                    <tr><td>11 -25</td><td>250 €</td><td>3 750 €</td></tr>
                    <tr><td>26 - 50</td><td>100 €</td><td>2 500 €</td></tr>
                    <tr><td>51 - 100</td><td>75 €</td><td>3 750 €</td></tr>
                    <tr><td>101 - 200</td><td>50 €</td><td>5 000 €</td></tr>
                    <tr><td>201 - 300</td><td>25 €</td><td>2 500 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b>Grand Total 30 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/spin-mania/fly-coins.png" alt="fly coins" class="tournament__image">


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
