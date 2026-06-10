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
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__cond-title">Період проведення турніру:</div>
        <div class="tournament__cond-text">З 11 листопада 2021 р. від 18 листопада 2021 р.</div>

        <div class="tournament__cond-title">Призовий фонд:</div>
        <div class="tournament__cond-text">1000 євро для 150 гравців</div>

        <div class="tournament__cond-title">Механіка:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнір «Cash Rain GameArt» - це глобальний турнір, доступний для всіх гравців.</li>
                <li>Турнір начнеться о 00:01 по Гринвичу в четвер, 11 листопада, і закінчиться о 23:59 по Гринвичу
                    у четвер, 18 листопада 2021 року.</li>
                <li>Ігри можуть грати наступні призи: 10000 євро буде розподілено між 150 гравцями</li>
            </ul>


            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Гравець</th>
                    <th>Приз</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td>2,500 €</td></tr>
                    <tr><td>2</td><td>1,000 €</td></tr>
                    <tr><td>3</td><td>600 €</td></tr>
                    <tr><td>4</td><td>300 €</td></tr>
                    <tr><td>5 -14</td><td>100 €</td></tr>
                    <tr><td>15-24</td><td>80 €</td></tr>
                    <tr><td>25-50</td><td>50 €</td></tr>
                    <tr><td>51-150</td><td>25 €</td></tr>
                </tbody>
            </table>
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


</div>
