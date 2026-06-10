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
        color: white;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: yellow;
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
    .tournament__title-text{
        margin-top: 1rem;
        /*color: #8184BC;*/
        color: white;
        font-size: 1rem;
        font-weight: bold;
    }
    .yellow-text{
        color: #fcc339;
        text-transform: uppercase;
    }
    .ai-fe{
        align-items: flex-end;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>


    <div class="article__text">
        <div class="tournament__title-text mt-2">
            <p>
                Our second network promo bring players a share of <span class="yellow-text">€70,000 CASH</span> across 1,000 prizes DAILY.
                Spin the random wheel of fortune this September 29th - October 9th inclusive for <span class="yellow-text">BIG WINS</span> daily.
            </p>
            <p>
                Choose one of 40+ slotsfrom Betsoft's award-winning portfolio and get ready for reaches.Prize winning spins can be triggered at any point in the game without interrupting play to add to the thrills and chills of playing to win.
            </p>
        </div>
        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">Take the Prize</span> Network Promotion runs daily between 10:00AM Thursday 29th of September untin 10:00AM 9th of October CEST.</li>
                    <li>Each daily promo runs for 24h from 10AM until 10AM CEST.</li>
                    <li>BETSOFT Gaming reserves right to amend, suspend or cancel the promotion any time.</li>
                    <li>Total prize-pool: <span class="yellow-text">€70,000 cash</span></li>
                    <li>Daily prize-pool: <span class="yellow-text">€7,000</span> cash across 100 prizes</li>
                    <li>1,000 cash prizes throughout the promotion</li>
                </ul>
            </div>
        </div>


        <div class="flex jc-center">
            <img src="/img/tournaments/take-the-prize/Thief.png" alt="Thief" class="tournament__image" style="width: 180px;">
        </div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">How to win:</div>
                <ul class="tournament__list">
                    <li>Get lucky with only one real money spin on any of the qualifying games</li>
                    <li>Minimum bet to qualify: €0,50</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/take-the-prize/wheel_coins.png" alt="wheel coins" class="tournament__image" style="margin-left: -5rem; width: 500px;">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Number of Prizes</th>
                        <th>Amount of Prizes daily</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>€ 1,000.00</td></tr>
                    <tr><td>2</td><td>€ 500.00</td></tr>
                    <tr><td>4</td><td>€ 300.00</td></tr>
                    <tr><td>13</td><td>€ 100.00</td></tr>
                    <tr><td>30</td><td>€ 50.00</td></tr>
                    <tr><td>50</td><td>€ 20.00</td></tr>
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
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
