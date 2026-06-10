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
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Условия и положения:</div>
                <ul class="tournament__list">
                    <li>Evoplay Instant Network Tournament доступен только для игры на реальные деньги.</li>

                    <li>Instant Network Tournament Evoplay длится 1 одну неделю в январе 2022 года. Турнир пройдет
                       в период с 0:01 UTC 25 января 2022 года по 31 января
                        2022 года, 23:59 UTC.</li>

                    <li>Общая сумма приза турнира составляет 7000 евро и будет распределена между
                        100 лучших игроков, набравших наибольшее количество очков в таблице лидеров.</li>

                    <li>Минимальная ставка, необходимая для участия в турнире составляет 0,50 евро или
                        эквивалент в местной валюте. Чем больше очков, тем выше поднимается игрок в таблице лидеров.
                        Первые 100 игроков в таблице лидеров получат призы.</li>

                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в евро и может зависеть от колебаний курса валюты.</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/instant-network/man.png?t=5" alt="Gambling man" class="tournament__image" style="margin-right: 3rem;">

        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Каждое вращение будет способствовать общему счету игрока, который отображается на
                        таблица лидеров. Общий балл рассчитывается путем сложения набранных баллов
                        во всех раундах, сыгранных в участвующих играх.</li>

                    <li>У турнира есть своя квалифицированная механика: накопление Total Win.</li>

                    <li>Турнир имеет список квалификационных игр и призовой фонд (евро), который
                        распределяется между победителями таблицы лидеров по правилам турнира.</li>

                    <li>Если несколько Участников набирают одинаковое количество баллов в процессе
                        подсчета результатов турнира, дается более высокая позиция в рейтинге
                        участнику, набравшему количество очков первым.</li>

                    <li>Денежные призы будут зачислены на счета игроков в виде возможности вывода
                        средства после окончания турнира.</li>

                    <li>Evoplay оставляет за собой право аннулировать баллы или не выплачивать приз, если все или
                        часть счета является результатом любой очевидной ошибки, ошибки или технической неисправности
                        (включая неправильные игровые выплаты), будь то машина или человек, в отношении любой из участвующих игр.</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/instant-network/girl.png?t=5" alt="Gambling girl" class="tournament__image">

        <div class="flex">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay оставляет за собой право изменять, приостанавливать или отменять турнир в любое
                        время и без предварительного уведомления.</li>

                    <li>Система вознаграждения рассчитывается в соответствии с призовым фондом и
                        устанавливается в соответствии с таблицей выигрышей.</li>

                    <li><b>Участие в играх:</b>
                        Серия пенальти, Колесо времени, Книга Кено, Бросок на удачу,
                        Золотое колесо</li>

                    <li><b>Турнирный рейтинг:</b> накопление общего выигрыша.</li>

                    <li><b>Механика:</b> Суммируются все суммы выигрышей, где 1 очко = 1
                        денежная единица базовой валюты турнира. Конвертируются все
                        значения выигрыша в другой валюте в валюту турнира.
                        Ставки только на сумму большую или равную
                        минимальной сумме ставки турнира.</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/instant-network/boy.png?t=5" alt="Gambling man" class="tournament__image mt-2">

        <div class="flex mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>1 170 €</td></tr>
                    <tr><td>2nd</td><td>887 €</td></tr>
                    <tr><td>3rd</td><td>700 €</td></tr>
                    <tr><td>4th</td><td>467 €</td></tr>
                    <tr><td>5th</td><td>420 €</td></tr>
                    <tr><td>6th</td><td>373 €</td></tr>
                    <tr><td>7th</td><td>280 €</td></tr>
                    <tr><td>8th</td><td>205 €</td></tr>
                    <tr><td>9th</td><td>154 €</td></tr>
                    <tr><td>10th - 19th</td><td>93 €</td></tr>
                    <tr><td>20th - 49th</td><td>23 €</td></tr>
                    <tr><td>50th - 100th</td><td>14 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Всего 7 000 €</b></td></tr>
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
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>


</div>

@include('phone.footer')
