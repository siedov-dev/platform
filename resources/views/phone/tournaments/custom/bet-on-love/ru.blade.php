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
    .tourn_stats{
        display: block;
        justify-content: space-between;
        margin-top: 20px;
    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 100%;
        padding: 20px 0;
        border-radius: 10px;
        margin-bottom: 10px;
    }
    .tourn_stat_itm h5{
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
    }
    .tourn_stat_itm h4{
        color: #FFC660;
        font-size: 16px
    }
    .tabs_tour{
        display: flex;
        background: #181833;
        margin-top: 30px;
        padding: 5px;
        justify-content: space-between;
    }
    .tabs_tour button{
        width: 32%;
        background: transparent;
        border: none;
        padding: 7px;
        border-radius: 5px;
        color: #8C90CA;
        cursor: pointer;
    }
    .tabs_tour button.active{
        background: #2B2F5B;
        color: #fff;
    }
    .tabs_tour button:hover{
        background: #2B2F5B;
        color: #fff;
    }
    .panes_tour{
        margin-top: 30px;
    }
    .panes_tour_itm{
        display: none;
    }
    .panes_tour_itm.active{
        display: block;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">
        <div class="tourn_stat_itm">
            <h5>Продолжительность турнира</h5>
            <h4>06.02.2024 - 15.02.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Призовой фонд</h5>
            <h4>€ 50,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Минимальная ставка</h5>
            <h4>€ 0.40</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Мин. количество вращений</h5>
            <h4>100</h4>
        </div>
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">Игры</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">Условия и положения</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">Призы</button>
    </div>
    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
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
        </div>
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                1. Сетевую кампанию Evoplay можно играть только в реальном режиме.
                 состоит из двух турниров, которые проводятся одновременно: Ставка на любовь
                 Сетевой турнир и акция «Ставка на любовь».<br>
                 2. Сетевая кампания Evoplay длится десять (10) согласованных дней и проводится
                 с 00:01 UTC 6 февраля 2024 г. по 15 февраля 2024 г. до 23:59
                 UTC.<br>
                 3. Общая сумма призов турнира составляет 50 000 евро.
                 4. Таблица лидеров консолидирована для всех операторов, участвующих в данном мероприятии.
                 сетевая кампания.<br>
                 5. Все призы вычитаются из GGR до выставления счета, независимо от
                 процент отчисления бонуса согласно договору.<br>
                 6. Призовой фонд и минимальная ставка для этой кампании установлены в евро и
                 может зависеть от колебаний конвертации валюты.<br>
                 7. В кампании есть список квалифицированных игр и призовой фонд (евро), который
                 распределяется между победителями согласно правилам турнира.<br>
                 8. Каждое квалифицированное вращение будет способствовать увеличению общего количества очков игрока, т.е.
                 отображается в таблице лидеров. Общий балл рассчитывается по баллам
                 полученные во всех раундах участвующих игр. Чем больше очков,
                 тем выше игрок попадает в таблицу лидеров.<br>
                 9. Evoplay оставляет за собой право аннулировать результаты или не выплачивать приз, если
                 все или часть баллов являются результатом какой-либо очевидной ошибки, ошибки или технической ошибки.
                 ошибка (включая неправильные игровые выплаты), вызванная машиной или человеком
                 ошибка в любой из участвующих игр.<br>
                 10. Evoplay также оставляет за собой право аннулировать результаты или не выплачивать приз.
                 где, по мнению, вся часть очков является результатом мошенничества или сговора
                 с другими игроками.<br>
                 11. Система вознаграждений рассчитывается в соответствии с призовым фондом.
                 объединить и установить количество участвующих лидеров.<br>
                 12. Казино обязано поддержать турнир, реализовав список
                 маркетинговая деятельность представлена ниже.<br>
                 13. Игроки получат всплывающее объявление о начале
                 турнир за два (2) дня до турнира.<br>
                 14. Подайте заявку на участие в турнире минимум за семь (7) дней.
                 до начала турнира.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;">Сетевой турнир «Зал побед»</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                <tr>
                    <th>Место</th>
                    <th>Приз</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td><td>€ 3000</td>
                </tr>
                <tr>
                    <td>2</td><td>€ 2800</td>
                </tr>
                <tr>
                    <td>3</td><td>€ 2450</td>
                </tr>
                <tr>
                    <td>4</td><td>€ 2100</td>
                </tr>
                <tr>
                    <td>5</td><td>€ 1750</td>
                </tr>
                <tr>
                    <td>6</td><td>€ 1400</td>
                </tr>
                <tr>
                    <td>7</td><td>€ 1155</td>
                </tr>
                <tr>
                    <td>8</td><td>€ 980</td>
                </tr>
                <tr>
                    <td>9</td><td>€ 910</td>
                </tr>
                <tr>
                    <td>10</td><td>€ 455</td>
                </tr>
                <tr>
                    <td>11-20</td><td>€ 280</td>
                </tr>
                <tr>
                    <td>21-50</td><td>€ 140</td>
                </tr>
                <tr>
                    <td>51-100</td><td>€ 105</td>
                </tr>
                <tr>
                    <td>101-150</td><td>€ 70</td>
                </tr>
                <tr>
                    <td>151-200</td><td>€ 35</td>
                </tr>
                <tr>
                    <td ></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 35000</span></td>
                </tr>
                </tbody>
            </table>
            <h4 style="margin-top: 30px; color: #fff; margin-bottom: 15px;">Кампания по раздаче призов в Зале побед</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                <tr>
                    <th>Количество</th>
                    <th>Приз</th>
                </tr>
                </thead>
                <tbody>
          
                <tr><td>1</td><td>€ 65</td></tr>
                <tr><td>2</td><td>€ 45</td></tr>
                <tr><td>7</td><td>€ 35</td></tr>
                <tr><td>10</td><td>€ 20</td></tr>
                <tr><td>20</td><td>€ 15</td></tr>
                <tr><td>60</td><td>€ 10</td></tr>
             
                <tr>
                    <td></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15000</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>


  

    <script>
        document.getElementById("pt_games").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_games");
            pane.classList.add('active');

            let but = document.getElementById("pt_games");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
        document.getElementById("pt_terms").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_terms");
            pane.classList.add('active');

            let but = document.getElementById("pt_terms");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
        document.getElementById("pt_prizes").addEventListener("click", function(){
            console.log('asdasd');
    
            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });
    
            let pane = document.getElementById("pn_prizes");
            pane.classList.add('active');

            let but = document.getElementById("pt_prizes");
            but.classList.add('active');
            //panes_tour_itm.pane_
    
        });
    </script>
    @include('desktop.footer')

</div>
