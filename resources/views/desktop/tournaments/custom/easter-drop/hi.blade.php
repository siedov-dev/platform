@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    .tournament__list li {
        font-size: 1rem;
        color: #7a79b0;
        margin-top: 0.5rem;
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
        color: red;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Chick out Wazdan’s Easter Drop and win a share of the massive <span style="color: red; font-weight: bold">€250,000</span> prize pool!</div>
        <div class="tournament__cond-title">Campaign period:</div>
        <div class="tournament__cond-text">From <strong>the 3rd to the 16th of April 2023</strong></div>

        <div class="tournament__cond-title">Terms and Conditions:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Mystery Box campaign (“The campaign”) is only available in Play for real mode.</li>
                    <li> Participating players will take part in the campaign by placing a qualifying bet in participating games during the campaign period. <strong>There is no additional cost</strong>.</li>
                    <li> Players can win more than one random prize during the campaign.</li>
                    <li> Any qualifying bet in participating games during the campaign period can trigger a prize from the prize pool.</li>
                    <li> A qualifying bet can win only one prize. Prizes will be awarded randomly throughout the campaign period.</li>
                    <li> <strong>No min bet size requirement</strong>. All bets are qualified to participate in the campaign.</li>
                    <li> The number of available prizes is updated instantly.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/bunny_1C.png" alt="bunny" style="width: 250px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe">
            <img src="/img/tournaments/easter-drop/MagicEggs_blue.png" alt="egg" style="width: 300px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> All prizes won will be instantly credited to the players’ casino accounts upon receiving the winning notification.</li>
                    <li> Prizes will be paid out as per the prize pool tab.</li>
                    <li> The maximum total value of cash prizes is <strong>250 000 EUR</strong> divided into <strong>2500</strong> Mystery Boxes.</li>
                    <li> Cash prizes <strong>do not have ANY wagering requirements</strong>.</li>
                    <li> Prizes paid while using bonus funds will be paid out in bonus funds.</li>
                    <li> Cash prizes will be paid out in the currency the winning bet was placed with, according to currency conversion from Euro at the time the winning bet was placed.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            {{--            <img src="/img/tournaments/easter-drop/bunny_2B.png" alt="bunny" style="width: 250px;">--}}
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Only verified Casino Players are eligible to win the prizes.</li>
                    <li> Casino reserves the right to not pay out a cash prize where the win results from any obvious error, mistake or technical fault (including incorrect game pay-outs) whether caused by a machine or human error in respect of any of the participating games. Casino further reserves the right to not pay out a cash prize where, in its opinion, the win results from cheating or collusion with other players.</li>
                    <li> Casino reserves the right to modify, alter, discontinue or terminate this promotion at any time for any reason and also reserves the right to change the terms and conditions of this offer at any time. Previous terms and offer will be honored for players claiming up until the time of any change in the offer.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MagicEggs_green.png" alt="egg" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Games participating:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">All wazdan games</span></div>

        <div class="tournament__cond-title">Prize Pool:</div>
        <div class="tournament__cond-text">The maximum total value of Cash prizes is <strong>250 000 EUR</strong>. There’s a total of <strong>2500 Mystery Boxes</strong>. Each Mystery Box can reward a prize from the following list:</strong></div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>75 EUR</li>
                    <li>50 EUR</li>
                    <li>25 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MysteryDrop_box.png" alt="bunny" style="width: 250px; margin-left: 100px;">
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
</div>
