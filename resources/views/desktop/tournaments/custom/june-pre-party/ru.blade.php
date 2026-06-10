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
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Условия и положения:</div>
                <ul class="tournament__list">
                    <li>Evoplay June Pre-party Network Tournament доступен только для игры в реальном режиме.</li>
                    <li>Evoplay June Pre-party Network Tournament проходит в течение 12 (двенадцати) дней.
                        в июне месяце 2022 года. Турнир состоится между 00:01 UTC
                        15 июня 2022 г. и 26 июня 2022 г. до 23:59 UTC.</li>
                    <li>Сумма призового фонда турнира составляет €14 000 и будет распределена между
                        100 лучших игроков, набравших наибольшее количество очков в таблицах лидеров.</li>
                    <li>Минимальная ставка, необходимая для участия в турнире, составляет 0,20 евро или
                        эквивалент в местной валюте. Чем больше очков, тем выше поднимается игрок
                        в таблице лидеров. Первые 100 игроков в таблице лидеров получат призы.</li>
                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в евро и
                        могут быть подвержены колебаниям конвертации валюты.</li>
                    <li>Каждый вращение будет накапливать общий счет игрока, который отображается на
                        таблица лидеров. Общий балл рассчитывается с учетом баллов, набранных во время всех
                        раундов, сыгранных в участвующих играх.</li>
                </ul>
            </div>

            <img src="/img/tournaments/pre-party/1.png" alt="Lion head" class="tournament__image" style="margin-left: 3rem;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/pre-party/3.png" alt="playson" class="tournament__image" style="margin-right: 3rem;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>У турнира есть своя квалифицированная механика: накопление Total Win.</li>
                    <li>Турнирные очки начисляются за каждую победу (минимальная ставка 0,20 евро)</li>
                    <li>Турнир имеет список квалификационных игр и призовой фонд (евро),
                        который распределяется между победителями согласно правилам турнира.</li>
                    <li>Если несколько участников набирают одинаковое количество баллов, то более высокий
                        место в таблице лидеров отдается участнику, набравшему первое место.</li>
                    <li>Денежные призы будут зачислены на счета игроков в виде возможности вывода.
                        средств казино после окончания акции.</li>
                    <li>Evoplay оставляет за собой право аннулировать баллы или не выплачивать приз в случае
                        вся оценка или ее часть является результатом любой очевидной ошибки, ошибки или технического
                        ошибка (включая неправильные игровые выплаты), вызванная машиной или человеком.</li>
                    <li>Evoplay также оставляет за собой право аннулировать баллы или не выплачивать призы.
                        где, по мнению, вся часть счета является результатом обмана или сговора
                        с другими игроками.</li>
                </ul>
            </div>
        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>2 330 €</td>
                    <tr><td>2</td><td>1 770 €</td>
                    <tr><td>3</td><td>1 400 €</td>
                    <tr><td>4</td><td>934 €</td>
                    <tr><td>5</td><td>840 €</td>
                    <tr><td>6</td><td>746 €</td>
                    <tr><td>7</td><td>560 €</td>
                    <tr><td>8</td><td>410 €</td>
                    <tr><td>9</td><td>308 €</td>
                    <tr><td>10-19</td><td>186 €</td>
                    <tr><td>20-49</td><td>46 €</td>
                    <tr><td>50-100</td><td>28 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 14 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/pre-party/athena.png" alt="fly coins" class="tournament__image" style="margin-left: 3rem;">
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
