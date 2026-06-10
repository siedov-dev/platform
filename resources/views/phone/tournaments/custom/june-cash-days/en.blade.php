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
        color: #FFF;
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
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
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
                    <li><span class="yellow-text">June CashDays 80k</span> tournament is available to play for real mode only</li>

                    <li>June CashDays 80k tournament runs between <span class="yellow-text">0:01 UTC on the 1st of June
                            2023 and 8th of June 2023 until 23:59 UTC</span></li>

                    <li>The minimum bet required to qualify for prizes is <span class="yellow-text">0.20 EUR</span> or currency equivalent.</li>

                    <li>Any win will contribute to the Player's Total score that will be shown in the
                        Leaderboard. The total score is calculated by the addition of the points scored
                        during all rounds played on participating games during the tournament period.</li>

                    <li>Points are calculated round-by-round, with each 1 EUR (or currency equivalent)
                        won equaling 10 points. <span class="yellow-text">For example, 10€ won represents 100 points.</span></li>

                    <li>The cash prizes will be credited to the players’ accounts as withdrawable funds
                        within 72 hours (3 business days) after the end of the promotion.</li>

                </ul>

            </div>

        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/june-cash-days/cup.png" alt="Lion head" class="tournament__image" style="filter: drop-shadow(0 0 5px gold); height: 10rem; width: 12rem;">
        </div>

        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Prize pool and minimum bet for this campaign are set up in <span class="yellow-text">EUR</span> and might be
                        subject to currency conversion fluctuations.</li>

                    <li>The currency conversion rates are being set automatically by our system with 72
                        hours before the tournament starts, and the values will remain the same during the
                        whole tournament period.</li>

                    <li>We reserve the right to void scores, or not pay out a prize, where all or part of the
                        score results from any obvious error, mistake or technical fault (including incorrect
                        game payouts) whether caused by a machine or human error in respect of any of
                        the participating games.</li>

                    <li>We further reserve the right to void scores, or not pay out a prize where, in our
                        opinion, all or part of the score results from cheating or collusion with other players.</li>

                    <li>We reserve the right to amend or cancel the campaign at any time.</li>
                </ul>
            </div>
        </div>
        <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image" style="filter: drop-shadow(0 0 5px white);">


        <div class="flex ai-center mb-1">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/june-cash-days/tbg.png"); background-size: cover; box-shadow: #0C0328 0 0 5px;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                        <th>Sum</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>10 000 €</td><td>10 000 €</td></tr>
                    <tr><td>2nd</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>3rd</td><td>3 000 €</td><td>3 000 €</td></tr>
                    <tr><td>4th -10th</td><td>1 000 €</td><td>7 000 €</td></tr>
                    <tr><td>11th -25th</td><td>750 €</td><td>11 250 €</td></tr>
                    <tr><td>26th - 50th</td><td>350 €</td><td>8 750 €</td></tr>
                    <tr><td>51th - 100th</td><td>200 €</td><td>10 000 €</td></tr>
                    <tr><td>101th - 200th</td><td>100 €</td><td>10 000 €</td></tr>
                    <tr><td>201th - 500th</td><td>50 €</td><td>15 000 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b><span class="yellow-text">Grand Total 80 000 €</span></b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/june-cash-days/fly-coins.png" alt="fly coins" class="tournament__image" style="filter: drop-shadow(0 0 5px gold);">


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

    @include('phone.footer')

</div>
