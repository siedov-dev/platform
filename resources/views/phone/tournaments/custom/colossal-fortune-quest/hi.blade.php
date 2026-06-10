@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }
    .tournament__list ul li {
        font-size: 0.875rem;
        color: #000000;
    }
    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        /*border: 1px solid black;*/
        background-color: #00000033;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFFFFF;
        text-shadow: 0 0 2px black;
        border-bottom: 1px solid black;
        white-space: nowrap;
        font-weight: 600;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #FFF;
        text-align: left;
        /*background-color: #3c3b89;*/
        border-bottom: 2px solid black;
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
    .article__text p{
        color: #FFFFFF;
        font-size: 1rem;
        font-weight: 500;
        text-shadow: 0 0 2px black;
        text-indent: 1rem;
    }
    .red-text{
        color: red;
        font-weight: bold;
    }

    .tournament__cond-text{
        background-image: url("/img/tournaments/fortune-quest/paper_03.png");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100% 100%;
        padding: 1.4rem;
        color: #000000;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <article class="article__text">

        <div style="position: relative;">
            <p class="mt-3">
                It’s all about the wins! Yggdrasil invites you to join the fun and excitement of the “Colossal Fortune Quest” network tournament.
            </p>
            <p>
                <span class="red-text">A total cash prize pool worth 40,000 EUR to be won!</span> Play any of the participating games for a chance to be among the random prize drop winners!
            </p>
            <p class="mb-2">
                The tournament runs from <span class="red-text">October 10th to 17th, 2022.</span>
                Fall back into fun!
            </p>
            <img src="/img/tournaments/colossal-fortune-quest/pyramid.png" alt="pyramid" class="tournament__image" style="position: absolute; bottom: 1rem; right: 0; width: 100%; filter: opacity(0.3)">
        </div>

        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li><span class="red-text">There are no rankings or points. The idea is simple - all prizes are randomly generated.</span> There is no minimum bet required to participate in the campaign.</li>
                    <li>To participate in the campaign, players shall confirm that they wish to take part in the campaign by clicking “join now” in the pop-up window. Participating players will take part in the campaign by placing a bet in the participating games during the campaign period. No additional payment is required to take part in the campaign.</li>
                    <li>Any bet placed on <span class="red-text">any of the participating games</span> triggers a random mystery prize from the prize pool.</li>
                    <li>The remaining number of prizes is updated every 60 seconds.</li>
                    <li>Prizes will be awarded throughout the campaign period. A minimum of one of the three most valuable prizes (a “top prize”) will be awarded during the final period of the campaign, such period being equal to 5% of the overall campaign period (for example if the campaign period is 100 hours, a minimum of one top prize will be awarded during the final 5 hours).</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/colossal-fortune-quest/totem.png" alt="skull" class="tournament__image">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style=''>
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Each bet can win only 1 random prize.</li>
                    <li>Players can win more than 1 random prize reward during the campaign.</li>
                    <li>Yggdrasil reserves the right to void scores or not pay out a prize, where all or part of the score results from any obvious error, mistake, or technical fault (including incorrect game payouts), whether caused by a machine or human error in respect of any of the participating games. We further reserve the right to void scores or not pay out a prize where, in our opinion, all or part of the score results from cheating or collusion with other players. </li>
                    <li>Only bets made in play for real mode qualify for the campaign.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/colossal-fortune-quest/dyno.png" alt="compass" class="tournament__image">
        </div>

        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/fortune-quest/map2.png') no-repeat center; background-size: 100% 90%;" >
            <div class="" style="padding-bottom: 3rem; margin-right: auto; margin-left: auto; width: 90%;">
                <table class="tournament__table mt-2" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Winners</th>
                            <th>EUR</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>7 000 €</td></tr>
                    <tr><td>2-3</td><td>4 000 €</td></tr>
                    <tr><td>4-8</td><td>1 000 €</td></tr>
                    <tr><td>9-18</td><td>550 €</td></tr>
                    <tr><td>19-69</td><td>150 €</td></tr>
                    <tr><td>69-168</td><td>40 €</td></tr>
                    <tr><td>169-668</td><td>10 €</td></tr>
{{--                    <tr style="font-weight: bold;"><td>668 winners</td><td>40 000 €</td></tr>--}}
                    </tbody>
                </table>
            </div>
        </div>



    </article>



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
