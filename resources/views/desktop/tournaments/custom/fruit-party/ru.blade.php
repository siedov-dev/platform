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
        <div class="tournament__cond-title">Период проведения турнира:</div>
        <div class="tournament__cond-text">С 10 по 17 февраля 2022 (включены оба дня)</div>

        <div class="tournament__cond-title">Положения и условия «Фруктовая вечеринка от GameArt»</div>
            <ul class="tournament__list">
                <div class="tournament__cond-text">Игрок может участвовать в акции в период действия акции следующим образом:</div>
                <li>Сыграйте в квалификационную игру и следите за своим счетом во внутриигровой таблице лидеров.</li>
                <li>За каждую ставку в €1, сделанную реальными деньгами в квалификационной игре, игрок получает 1 очко.</li>
                <div class="tournament__cond-title">Дополнительные достижения:</div>
                <li>БОЛЬШОЙ ВЫИГРЫШ – 50 баллов</li>
                <li>300 символов Scatter в любой игре – 300 очков</li>
                <li>ЭПИЧЕСКАЯ ПОБЕДА – 500 очков</li>
                <li>5000 вращений – 1000 очков (стоимость ставки 0,40 евро или выше)</li>
                <li>Таблица лидеров будет отображаться в самой игре.</li>
            </ul>

        <div class="tournament__cond-title">Механика:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнир «Fruit Party GameArt» - это глобальный турнир, доступный для всех игроков.</li>
                <li>Турнир начнется в 00:01 по Гринвичу в четверг, 10 февраля, и закончится в 23:59 по Гринвичу
                    в четверг, 17 февраля 2022 года.</li>
                <li>Игроки могут выиграть следующие призы: 10000 € будет распределено между 150 игроками</li>
                <li>Компания оставляет за собой право изменять, отменять или прекращать акцию в любое время.</li>
            </ul>

            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Место</th>
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
