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
        width: 40%;
        height: auto;
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
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Условия и положения:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">May CashDays 80k</span> доступен только для игры в реальном режиме</li>

                    <li>May CashDays 80k проходит между <span class="yellow-text">0:01 UTC 1 мая 2023 г. и 8 мая 2023 г. до 23:59 UTC</span></li>

                    <li>Минимальная ставка, необходимая для участия в розыгрыше призов, составляет <span class="yellow-text">0,20 евро</span> или эквивалент в другой валюте.</li>

                    <li>Любой выигрыш будет накапливаться на общем счету игрока, который будет отображаться в
                        Таблица лидеров. Общий балл рассчитывается путем сложения набранных баллов
                        во всех раундах, сыгранных в участвующих играх в период проведения турнира.</li>

                    <li>Очки начисляются постепенно, с каждым 1 евро (или эквивалентом в другой валюте)
                        выигрыш составляет 10 очков. <span class="yellow-text">Например, 10 евро выигрыша равны 100 баллам.</span></li>

                    <li>Денежные призы будут зачислены на счета игроков в виде средств, которые можно вывести.
                        в течение 72 часов (3 рабочих дней) после окончания акции.</li>

                </ul>
            </div>

            <img src="/img/tournaments/may-cash-days/cup.png" alt="Lion head" class="tournament__image" style="margin-left: 3rem; filter: drop-shadow(0 0 5px gold);">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/may-cash-days/playson.png" alt="playson" class="tournament__image" style="margin-right: 3rem; filter: drop-shadow(0 0 5px white);">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Призовой фонд и минимальная ставка для этой кампании установлены в <span class="yellow-text">евро</span> и могут быть
                        зависит от колебаний конвертации валюты.</li>

                    <li>Курсы конвертации валют устанавливаются нашей системой автоматически с помощью 72
                        часов до начала турнира, а значения останутся прежними во время
                        весь период турнира.</li>

                    <li>Мы оставляем за собой право аннулировать баллы или не выплачивать приз, если все или часть
                        оценка результатов в результате любой очевидной ошибки, ошибки или технического сбоя (включая
                        игровые выплаты), независимо от того, вызваны ли они машинной или человеческой ошибкой в ​​отношении любого из
                        участвующие игры.</li>

                    <li>Кроме того, мы оставляем за собой право аннулировать баллы или не выплачивать приз, если, по нашим
                        по мнению, весь счет или его часть являются результатом мошенничества или сговора с другими игроками.</li>

                    <li>Мы оставляем за собой право изменить или отменить кампанию в любое время.</li>

                </ul>
            </div>
        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/may-cash-days/tbg.png"); background-size: cover; box-shadow: #0C0328 0 0 5px;'>
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                        <th>Сумма</th>
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
            <img src="/img/tournaments/may-cash-days/fly-coins.png" alt="fly coins" class="tournament__image" style="margin-left: 3rem; filter: drop-shadow(0 0 5px gold);">
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
