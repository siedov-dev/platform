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
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Период сетевой кампании:</div>
        <div class="tournament__cond-text">От <strong>25.12.2023 00:00 UTC</strong> до <strong>01.01.2024 23:59 UTC</strong></div>



        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Горячий период</span> - From 29.12.2023 15:00 UTC</div>

        <div class="tournament__cond-title">3 Условия турнира Oaks Network:</div>
        <div class="tournament__cond-text">
            <p>
                1. Участвуя в Network Lucky Drops, вы соглашаетесь с Условиями, изложенными здесь, а также с условиями, предоставленными
                 оператор.<br>
                 2. В чрезвычайных случаях 3 Oaks Gaming оставляет за собой право отменить акцию Lucky Drops в любое время, включая оплату
                 выигрыши, если это будет сочтено необходимым.<br>
                 3. В акции Lucky Drops могут участвовать только раунды на реальные деньги.<br>
                 4. За участие в розыгрыше Network Lucky Drops не взимается дополнительная плата, кроме платы за вращение.<br>
                 5. Правила сети Lucky Drops можно изменить в любое время.<br>
                 6. Неисправность аннулирует все выплаты.
            </p>
        </div>


        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Игрок должен присоединиться или дать согласие на участие в любой из участвующих игр.</li>
                     <li>Минимальная ставка, необходимая для получения LuckyDrop, составляет <span style="color: red; Font-weight: Bold">0,50 евро</span> или эквивалентную сумму в доступных валютах в участвующей игре( с).</li>
                     <li>Каждая квалификационная ставка, сделанная в любой из участвующих игр, может привести к получению случайного приза из призового фонда в течение периода акции.</li>
                     <li>Каждый игрок может выиграть максимум 3 приза.</li>
                </ul>
            </div>
            <img src="/img/tournaments/winter-lucky-drops/snowman.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Механика победы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops -</span> предлагает игрокам возможность выиграть денежные призы в любое время в течение периода проведения мероприятия. Призы предоставляются случайным образом, и все игроки имеют одинаковые шансы на выигрыш в каждом квалификационном вращении.
            </div>
        </div>
        <div class="tournament__cond-title">Участие в играх:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Все игры</span></div>
        <div class="tournament__cond-title">Призовой фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Призовой фонд составляет 30 000 евро для этой Кампании (или эквивалент в валюте игрока), объединенный из нескольких пулов по следующей структуре:</div>
             <div class="tournament__cond-text"><span style="color: red; Font-weight: Bold">30 000 евро</span> за период (с 25.12.2023 00:00 UTC по 01.01.2023 23:59 UTC) </div>
             <li class="tournament__cond-text"><span style="color: red; Font-weight: Bold">30 000 евро</span> Всего за <span style="color: red; Font-weight: Bold" >Каждую неделю</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Количество</th>
                     <th>Стоимость приза</th>
                     <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>15</td><td>€ 200</td><td>€ 3,000</td></tr>
                    <tr><td>30</td><td>€ 100</td><td>€ 3,000</td></tr>
                    <tr><td>60</td><td>€ 75</td><td>€ 4,500</td></tr>
                    <tr><td>80</td><td>€ 60</td><td>€ 4,800</td></tr>
                    <tr><td>105</td><td>€ 50</td><td>€ 5,250</td></tr>
                    <tr><td>130</td><td>€ 30</td><td>€ 3,900</td></tr>
                    <tr><td>150</td><td>€ 20</td><td>€ 3,000</td></tr>
                    <tr><td>170</td><td>€ 15</td><td>€ 2,550</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
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
