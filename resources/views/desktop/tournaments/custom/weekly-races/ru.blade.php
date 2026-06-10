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
        color: #fff;
    }
    .article__text strong.gold{
        color: #d7b96b;
    }
    .tournament__list strong{
        color: #d7b96b;
    }
    .article__text b.red{
        color: red;
    }
    @keyframes gold-shine {
        from {filter: drop-shadow(0 0 0px goldenrod);}
        to {filter: drop-shadow(0 0 10px goldenrod);}
    }
    .shine{
        animation: gold-shine;
        animation-duration: 2s;
        filter: drop-shadow(0 0 10px goldenrod);
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-text">Турнир <strong class="gold">Weekly Races 40k</strong> проходит с <b class="red">0:01 по UTC 15 июня 2023 года до 23:59 UTC 25 июня 2023 года</b> и разделен на несколько фаз.</div>
        <div class="tournament__cond-text"><strong>Фаза 1:</strong> <b class="red">15.06.2023-18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 2:</strong> <b class="red">22.06.2023-25.06.2023</b></div>

        <div style="display: flex; margin-top: -80px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/weekly-races/cup.png" alt="grass" style="height: 220px; margin-bottom: -80px; margin-right: 50px;">
        </div>

        <div class="tournament__cond-title">Условия участия:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнир <strong>Weekly Races 40k</strong> доступен только в режиме игры на реальные деньги.</li>
                <li>Минимальная ставка для участия составляет <b class="red">0,20 евро</b> или эквивалент в другой валюте.</li>
                <li>Любая ставка будет учитываться в <b class="red">Общем счете игрока</b>, который будет отображаться в <strong>Таблице лидеров</strong>. Общий счет рассчитывается путем суммирования баллов, набранных за все раунды, сыгранные во время турнира на участвующих играх.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Призы:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Денежные призы будут зачислены на счет игроков в качестве средств для вывода в течение <b class="red">72 часов (3 рабочих дней)</b> после окончания акции.</li>
                    <li>Размер призового фонда и минимальная ставка для этой кампании установлены в евро (EUR) и могут подвергаться влиянию колебаний обменного курса валюты.</li>
                    <li>Обменные курсы устанавливаются автоматически нашей системой <b class="red">за 72 часа</b> до начала турнира, и значения останутся неизменными на протяжении всего периода турнира.</li>
                    <li>Мы оставляем за собой право аннулировать результаты, не выплачивать призы, если весь или часть результатов являются результатом очевидной ошибки, ошибки или технического сбоя (включая неправильные выплаты в игре), вызванных машинными или человеческими ошибками на одной из участвующих игр.</li>
                    <li>Мы также оставляем за собой право аннулировать результаты, не выплачивать призы, если, по нашему мнению, весь или часть результатов являются результатом мошенничества или сговора с другими игроками.</li>
                    <li>Мы оставляем за собой право изменить или отменить кампанию в любое время.</li>
                </ul>
            </div>
            <img src="/img/tournaments/weekly-races/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; height: 140px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Механика победы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <strong>Гонка ставок (Сумма всех СТАВОК)</strong>: Баллы рассчитываются по раундам, где каждая <b class="red">1 евро ставки равна 100 баллам</b>. Например, 5 евро ставки принесут 500 баллов.
            </div>
        </div>

        <div class="tournament__cond-title">Призовой фонд:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>40 000 евро</strong>, разделенный на <b class="red">2 турнира по 20 000 евро</b> каждый:</div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Место</th>
                    <th>Приз</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                <tr><td>2</td><td>€ 2,000</td><td>€ 2,000</td></tr>
                <tr><td>3</td><td>€ 1,000</td><td>€ 1,000</td></tr>
                <tr><td>4 - 10</td><td>€ 400</td><td>€ 2,800</td></tr>
                <tr><td>11 - 20</td><td>€ 150</td><td>€ 1,500</td></tr>
                <tr><td>21 - 50</td><td>€ 100</td><td>€ 3,000</td></tr>
                <tr><td>51 - 100</td><td>€ 50</td><td>€ 2,500</td></tr>
                <tr><td>101 - 150</td><td>€ 20</td><td>€ 1,000</td></tr>
                <tr><td>151 - 270</td><td>€ 10</td><td>€ 1,200</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 20,000</span></td>
                </tr>
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
