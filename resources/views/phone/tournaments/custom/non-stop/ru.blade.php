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
        white-space: nowrap;
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
        <div class="tournament__cond-text">Турнир <strong class="gold">Non-Stop Drop June 150,000 EUR</strong> проходит с <b class="red">0:01 01.06.2023 UTC</b> по <b class="red">23:59 30.06.2023 UTC</b> и разделен на <b class="red">еженедельные</b> фазы</div>
        <div class="tournament__cond-text"><strong>Фаза 1:</strong> <b class="red">01.06.2023 - 11.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 2:</strong> <b class="red">12.06.2023 - 18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 3:</strong> <b class="red">19.06.2023 - 25.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 4:</strong> <b class="red">26.06.2023 - 30.06.2023</b></div>
        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/non-stop/cup.png" alt="grass" style="height: 120px; margin-bottom: 30px;">
        </div>
        <div class="tournament__cond-text">
            Функция <b class="red">Cash Blast</b> предлагает игрокам возможность выиграть денежные призы в любое время в период события. Призы предлагаются случайным образом, и каждый игрок имеет одинаковые шансы на выигрыш после каждого спина.
        </div>

        <div class="tournament__cond-title">Соревнование:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li><strong>Non-Stop Drop 1,000,000 EUR</strong> доступно только в режиме реальной игры.</li>
                <li>Минимальная ставка для участия составляет <b class="red">0.20 EUR</b> или эквивалент в другой валюте.</li>
                <li>Любой спин на игровых автоматах, участвующих в акции, может запустить случайный приз <b class="red">Cash Blast</b>.</li>
                <li>Любой отдельный игрок может выиграть несколько призов <b class="red">Cash Blast</b> в течение периода акции.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Призы:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li><strong>Денежные призы</strong> будут автоматически зачислены на счет игроков сразу после получения выигрыша. После зачисления приза он будет удален из призового фонда. Количество оставшихся призов отображается в реальном времени в виджете <b class="red">Cash Blast</b> или в разделе <b class="red">Призы</b>.</li>
                    <li>Призовой фонд и минимальная ставка для этой кампании установлены в <b class="red">EUR</b> и могут подвергаться колебаниям курса обмена валюты.</li>
                    <li>Курсы обмена валюты устанавливаются автоматически нашей системой за <b class="red">72 часа</b> до начала турнира и останутся неизменными на протяжении всего периода турнира.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center mt-1">
            <img src="/img/tournaments/non-stop/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>


        <div class="tournament__cond-title">Общие правила:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Playson оставляет за собой право изменить, приостановить или отменить Сетевую акцию в любое время, включая любые связанные с ней условия и положения.</li>
                    <li>Мы оставляем за собой право аннулировать очки или не выплачивать приз, если все или часть результатов получены в результате очевидной ошибки, ошибки или технического сбоя (включая неправильные выплаты в игре), вызванного как машиной, так и человеческой ошибкой в отношении любой из участвующих игр.</li>
                    <li>Мы также оставляем за собой право аннулировать очки или не выплачивать приз, если, по нашему мнению, все или часть результатов получены в результате мошенничества или сговора с другими игроками.</li>
                    <li>Мы оставляем за собой право изменить или отменить кампанию в любое время.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Таблица призов:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>150,000 EUR/ месяц</strong>, <b class="red">37,500 EUR/ неделя</b></div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Приз</th>
                    <th>Количество</th>
                    <th>Сумма EUR</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>€ 5,000</td><td>1</td><td>€ 5,000</td></tr>
                <tr><td>€ 2,500</td><td>2</td><td>€ 5,000</td></tr>
                <tr><td>€ 1,000</td><td>4</td><td>€ 4,000</td></tr>
                <tr><td>€ 500</td><td>10</td><td>€ 5,000</td></tr>
                <tr><td>€ 250</td><td>20</td><td>€ 5,000</td></tr>
                <tr><td>€ 100</td><td>40</td><td>€ 4,000</td></tr>
                <tr><td>€ 75</td><td>50</td><td>€ 3,750</td></tr>
                <tr><td>€ 50</td><td>60</td><td>€ 3,000</td></tr>
                <tr><td>€ 25</td><td>70</td><td>€ 1,750</td></tr>
                <tr><td>€ 10</td><td>100</td><td>€ 1,000</td></tr>
                <tr>
                    <td><strong>Итого</strong></td>
                    <td><b class="red">357</b></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 37,500</span></td>
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
