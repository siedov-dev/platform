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
        font-size: 1.2rem;
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
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__title-text mt-2">
            <p>
                Наша вторая сетевая акция приносит игрокам <span class="yellow-text">€70 000 НАЛИЧНЫМИ</span> в виде 1000 призов ЕЖЕДНЕВНО.
                Вращайте случайное колесо фортуны с 29 сентября по 9 октября включительно и получайте <span class="yellow-text">БОЛЬШИЕ ВЫИГРЫШИ</span> ежедневно.
            </p>
            <p>
                Выберите один из более чем 40 слотов из отмеченного наградами портфолио Betsoft и приготовьтесь к розыгрышу. Призовые спины могут быть запущены в любой момент игры, не прерывая игру, чтобы добавить острых ощущений и озноба от игры, чтобы выиграть.
            </p>
        </div>
        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Положения и условия:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">Получите приз</span> Сетевая акция проводится ежедневно с 10:00 четверга, 29 сентября, до 10:00 9 октября по центральноевропейскому летнему времени.</li>
                    <li>Каждая ежедневная акция действует круглосуточно с 10:00 до 10:00 по центральноевропейскому летнему времени.</li>
                    <li>BETSOFT Gaming оставляет за собой право изменить, приостановить или отменить акцию в любое время.</li>
                    <li>Общий призовой фонд: <span class="yellow-text">70 000 евро наличными</span></li>
                    <li>Ежедневный призовой фонд: <span class="yellow-text">€7000</span> наличными из 100 призов</li>
                    <li>1000 денежных призов за всю акцию</li>
                </ul>
            </div>
            <img src="/img/tournaments/take-the-prize/dog.png" alt="dog" class="tournament__image" style="margin-left: 3rem; width: 200px;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/take-the-prize/Thief.png" alt="Thief" class="tournament__image" style="margin-right: 3rem; width: 180px;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Как выиграть:</div>
                <ul class="tournament__list">
                    <li>Станьте удачливым, лиш с одним вращением на реальные деньги в любой из квалификационных игр.</li>
                    <li>Минимальная ставка для квалификации: 0,50 евро.</li>
                </ul>
            </div>

        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Количество призов</th>
                        <th>Сумма призов в день</th>
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
            <img src="/img/tournaments/take-the-prize/wheel_coins.png" alt="wheel coins" class="tournament__image" style="margin-left: -5rem; width: 500px;">
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
