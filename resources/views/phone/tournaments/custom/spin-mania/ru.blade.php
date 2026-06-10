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

                <div class="tournament__cond-title">Условия и положения:</div>
                <ul class="tournament__list">
                    <li>Турнир SpinMania 30k доступен только для игры в реальном режиме</li>

                    <li>Турнир SpinMania 30k проходит между 0:01 UTC 26 мая 2022 года и 29 мая 2022 года.
                        мая 2022 г. до 23:59 UTC.</li>

                    <li>Минимальная ставка, необходимая для участия, составляет 0,40 евро или эквивалент в другой валюте.</li>

                    <li>Каждый спин в размере 0,40 евро или более будет накапливать общий счет игрока, который будет показан
                        в таблице лидеров. Общий балл рассчитывается путем сложения баллов, набранных во время
                        всех спинов совершенных в участвующих играх в период проведения турнира.</li>

                    <li>Очки начисляются раунд за раундом, при этом каждый оплаченный спин равен 1 баллу. Общий балл =
                        общее количество спинов от 0,40 евро и более.</li>

                    <li>Денежные призы будут зачислены на счета игроков в виде выводимых средств в течение 72
                        часов (3 рабочих дня) после окончания акции.</li>
                </ul>
            </div>

        </div>
        <img src="/img/tournaments/spin-mania/lion-head.png" alt="Lion head" class="tournament__image" class="tournament__image">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Призовой фонд и минимальная ставка для этой кампании установлены в евро и могут зависеть
                        от колебаний конвертации валюты.</li>

                    <li>Курсы конвертации валют устанавливаются нашей системой автоматически в течение 72 часов
                        до начала турнира, а значения останутся неизменными в течение всего
                        Турнирного периода.</li>

                    <li>Мы оставляем за собой право аннулировать баллы или не выплачивать приз, если все или часть баллов
                        являются результатом любой очевидной ошибки или технического сбоя (включая неправильные игровые выплаты)
                        будь то машинная или человеческая ошибка в отношении какой-либо из участвующих игр.</li>

                    <li>Мы также оставляем за собой право аннулировать баллы или не выплачивать приз, если, по нашему мнению, все
                        или часть счета является результатом мошенничества или сговора с другими игроками.</li>

                    <li>Мы оставляем за собой право изменить или отменить кампанию в любое время.</li>
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
