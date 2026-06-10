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
            <h4>09.02.2024 - 05.01.2025</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Призовой фонд</h5>
            <h4>$1,000,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Минимальная ставка</h5>
            <h4>$0.2</h4>
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
                Чтобы принять участие, игроки должны нажать «ПРИСОЕДИНИТЬСЯ» во внутриигровом виджете и сыграть в квалификационные игры с помощью
                 минимальная ставка составляет 0,20 евро или эквивалент в другой валюте на одно вращение. Игроки могут следить за своим счетом в прямом эфире в игре.
                 таблица лидеров. Чтобы его результат был действительным и отображался в таблице лидеров, игрок должен выполнить
                 хотя бы один цикл из 30 вращений. Игрок может выполнить неограниченное количество циклов, но только
                 полный цикл с наивысшим результатом попадает в таблицу лидеров.<br>
                 В Yeet&Sweet можно играть только на реальные деньги.<br><br>
                 Как работают турниры Yeet&Sweet?<br>
                 Игрокам нужно просто сделать выигрышные ставки в любой из подходящих игр, после чего они будут вознаграждены.
                 очки таблицы лидеров. Обратите внимание, что в Yeet&Sweet разрешена игра в несколько игр, поэтому игроки
                 может играть только в одну игру в любой момент времени, но может переключаться между подходящими играми в любой момент.<br>
                 Система очков основана на сумме накопленных множителей за цикл из 30 раундов вращений.
                 Очки за спин рассчитываются как множители — сумма, выигранная в каждом вращении, делится на сумму
                 поставил. Например: если игрок ставит 1 евро и выигрыш составляет 10 евро, это означает, что множитель выигрыша равен 10. Очки начисляются.
                 рассчитывается как множитель выигрыша, умноженный на 100. Итак, в этом примере игрок получит 1000 очков.
                 когда множитель выигрыша равен 10.<br>
                 Обратите внимание, что функция Gamble не участвует в этом расчете. Только победы с базы
                 игра засчитывается; любые очки, полученные с помощью функции Gamble, будут аннулированы. Например, если игрок
                 делает ставку в 1 евро и выигрывает 10 евро, а затем увеличивает выигрыш, используя функцию Gamble, только 1000 очков
                 быть награжденным.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style=" color: #fff; margin-bottom: 10px; ">Турниры</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Имя</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Турнир 1</td>
                        <td>Sweetheart Dragon </td>
                    </tr>
                    <tr>
                        <td>Турнир 2</td>
                        <td>Sweet Greens</td>
                    </tr>
                    <tr>
                        <td>Турнир 3</td>
                        <td>Candy Blossoms</td>
                    </tr>
                    <tr>
                        <td>Турнир 4</td>
                        <td>Lucky Loser</td>
                    </tr>
                    <tr>
                        <td>Турнир 5</td>
                        <td>Joker’s Sweets</td>
                    </tr>
                    <tr>
                        <td>Турнир 6</td>
                        <td>Fruitish</td>
                    </tr>
                    <tr>
                        <td>Турнир 7</td>
                        <td>Golden Dunes</td>
                    </tr>
                    <tr>
                        <td>Турнир 8</td>
                        <td>Sugar & Skulls</td>
                    </tr>
                    <tr>
                        <td>Турнир 9</td>
                        <td>Candy Cane Cup</td>
                    </tr>
                    <tr>
                        <td>Турнир 10</td>
                        <td>ULTIMATE 100</td>
                    </tr>
                </tbody>
            </table>
            <h4 style=" color: #fff; margin-bottom: 10px; margin-top: 30px;">Призы</h4>
            <table class="tournament__table" style="background-color: #0B0D31C0;width: 100%;">
                <thead>
                    <tr>
                        <th>Позиция</th>
                        <th>Приз</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>€31,000</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>€19,000</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>€12,000</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>€6,000</td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>€4,000</td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>€3,600</td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td>€3,200</td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td>€2,800</td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td>€2,400</td>
                    </tr>
                    <tr>
                        <td>10</td>
                        <td>€2,000</td>
                    </tr>
                    <tr>
                        <td>10-20</td>
                        <td>€300</td>
                    </tr>
                    <tr>
                        <td>21-30</td>
                        <td>€250</td>
                    </tr>
                    <tr>
                        <td>31-40</td>
                        <td>€200</td>
                    </tr>
                    <tr>
                        <td>41-50</td>
                        <td>€150</td>
                    </tr>
                    <tr>
                        <td>51-100</td>
                        <td>€100</td>
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
