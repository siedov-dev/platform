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
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
    .text-gold{
        color: #ffb500;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: #e4b556;
        white-space: nowrap;
    }
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2 text-gold">Say goodbye to summer in style!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> is the new Wazdan promotion during which you will be able to win rewards from the massive
            <strong>€3,000,000</strong> prize pool. Play your favourite Wazdan games and let the <span class="hot">Mystery Drop™</span> work its wonders.</div>
        <div class="tournament__cond-title">Campaign period:</div>
        <div class="tournament__cond-text">From <strong>the 15th of September to the 15th of October 2023</strong></div>

        <div class="tournament__cond-title">Terms and Conditions:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Mystery Drop campaign (“The campaign”) is only available in Play for real mode.</li>
                    <li> Participating players will take part in the campaign by placing a qualifying bet in participating games during the campaign period. <strong>There is no additional cost</strong>.</li>
                    <li> Players can win more than one random prize during the campaign.</li>
                    <li> Any qualifying bet in participating games during the campaign period can trigger a prize from the prize pool.</li>
                    <li> A qualifying bet can win only one prize. Prizes will be awarded randomly throughout the campaign period.</li>
                    <li> <strong>No min bet size requirement</strong>. All bets are qualified to participate in the campaign.</li>
                    <li> The number of available prizes is updated instantly.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> All prizes won will be instantly credited to the players’ casino accounts upon receiving the winning notification.</li>
                    <li> Prizes will be paid out as per the prize pool tab.</li>
                    <li> The maximum total value of cash prizes is <strong>500 000 EUR</strong> divided into <strong>5000</strong> Mystery Boxes.</li>
                    <li> Cash prizes <strong>do not have ANY wagering requirements</strong>.</li>
                    <li> Prizes paid while using bonus funds will be paid out in bonus funds.</li>
                    <li> Cash prizes will be paid out in the currency the winning bet was placed with, according to currency conversion from Euro at the time the winning bet was placed.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
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
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Games participating:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">All wazdan games</span></div>

        <div class="tournament__cond-title">Prize Pool:</div>
        <div class="tournament__cond-text">The maximum total value of Cash prizes is <strong>500 000 EUR</strong>. There’s a total of <strong>5000 Mystery Boxes</strong>. Each Mystery Box can reward a prize from the following list:</strong></div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>25 EUR</li>
                    <li>20 EUR</li>
                    <li>15 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/MysteryDrop_box.png" alt="bunny" style="width: 200px; margin-left: 10px;">
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
</div>
