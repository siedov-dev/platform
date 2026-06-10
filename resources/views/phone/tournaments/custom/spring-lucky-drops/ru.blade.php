@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__cond-title{
        font-size: 1.2rem;
    }
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
    .tournament__cond-text{
        font-size: 0.875rem;
    }
    ul li::marker {
        background-color: #fec30c;
        color: #fec30c;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Период сетевой кампании:</div>
        <div class="tournament__cond-text">С <strong>15–28 мая 2023 г.</strong> (включая оба дня)</div>
        <div class="tournament__cond-text"><strong>Неделя 1:</strong> 15.05.2023-21.05.2023</div>
        <div class="tournament__cond-text"><strong>Неделя 2:</strong> 22.05.2023-28.05.2023</div>

        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img src="/img/tournaments/spring-lucky-drops/leaves.png" alt="grass" style="width: 30%;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Горячий период</span> - увеличивает шанс игрока на выигрыш приза:</div>
        <div class="tournament__cond-text"><strong>Неделя 1:</strong> Начало с <span style="color: red; font-weight: bold">18.05.2023 0:00 UTC</span> по окончание кампании <strong>Weekly Lucky Drops</strong></div>
        <div class="tournament__cond-text"><strong>Неделя 2:</strong> начало с <span style="color: red; font-weight: bold">25.05.2023 0:00 UTC</span> по окончание кампании <strong>Weekly Lucky Drops</strong></div>

        <div class="tournament__cond-title">Положения и условия турнира 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Участвуя в турнире, вы соглашаетесь с Условиями, изложенными здесь, а также условиями, предоставленными оператором.</li>
                <li>В экстренных случаях 3 Oaks Gaming оставляет за собой право отменить акцию Lucky Drops в любое время, включая выплату выигрышей, если это будет сочтено необходимым.</li>
                <li>В кампании Lucky Drops могут участвовать только раунды с реальными деньгами.</li>
                <li>Помимо платы за вращение, за участие в Network Lucky Drops не взимается дополнительная плата.</li>
                <li>Каждый игрок получит личный турнирный идентификатор для таблицы лидеров.</li>
                <li>Сетевые правила Lucky Drops можно изменить в любое время.</li>
                <li>Неисправность аннулирует все выплаты.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Игрок должен присоединиться или принять участие в любой из участвующих игр.</li>
                    <li>Минимальная ставка, необходимая для получения LuckyDrop, составляет <span style="color: red; font-weight: bold">0,2 евро</span> или эквивалентную сумму в доступных валютах в участвующей игре( с).</li>
                    <li>Каждая квалификационная ставка, размещенная в любой из участвующих игр, может вызвать получение случайного приза из призового фонда в течение периода проведения акции.</li>
                    <li>Каждый игрок может выиграть максимум 3 приза.</li>
                </ul>
            </div>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-bottom: -3rem;">
            <img src="/img/tournaments/spring-lucky-drops/sakura2.png" alt="sakura" style="display: flex; height: 140px;">
        </div>

        <div class="tournament__cond-title">Победная механика:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops — </span> предлагает игрокам возможность выиграть денежные призы в любое время в течение периода проведения мероприятия. Призы выдаются случайным образом, и все игроки имеют равные шансы на выигрыш в каждом квалификационном вращении.
            </div>
        </div>
        <div class="tournament__cond-title">Участвующие игры:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Все игры</span></div>
        <div class="tournament__cond-title">Призовой фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Призовой фонд этой Кампании составляет 60 000 евро (или эквивалент в валюте игрока), объединенный из нескольких активов по следующей оценке:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">60 000 евро</span> за период (15.05.2023 00:00 UTC to 28.05.2023 23:00 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">30 000 евро</span> всего за <span style="color: red; font-weight: bold">Каждую неделю</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table mt-1"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>40</td><td>€ 200</td><td>€ 8,000</td></tr>
                <tr><td>50</td><td>€ 100</td><td>€ 5,000</td></tr>
                <tr><td>70</td><td>€ 75</td><td>€ 5,250</td></tr>
                <tr><td>70</td><td>€ 40</td><td>€ 2,800</td></tr>
                <tr><td>90</td><td>€ 30</td><td>€ 2,700</td></tr>
                <tr><td>250</td><td>€ 25</td><td>€ 6,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/spring-lucky-drops/grass.png" alt="grass" style="width: 120%; margin-left: -40px; height: 70px;">

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
