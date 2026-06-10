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
        box-shadow: #0d8f1b 0 0 5px;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;
        font-weight: 500;
        text-align: center;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table tr:last-child td{
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__table th {
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__list strong{
        color: #00bcff;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Положення та умови:</div>
                <ul class="tournament__list">
                    <li>Мережевий турнір Evoplay Bet Trills доступний лише у реальному режимі.</li>
                    <li>Мережевий турнір Evoplay Bet Trills проводиться з 00:01 <strong>15 травня 2023 г і 24 травня 2023 г.</strong> до 23:59 UTC</li>
                    <li>Сума турнірного призу становить <strong>10 000 євро</strong> і буде розподілена між <strong>100</strong> найкращими гравцями, які набрали найбільшу кількість очок у списках лідерів.</li>
                    <li>Мінімальна ставка, необхідна для участі у турнірі, становить <strong>0,20 євро</strong> або еквівалент у місцевій валюті. Перші <strong>100</strong> гравців у таблиці лідерів отримають призи.</li>
                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в <strong>євро</strong> і можуть залежати від коливань конвертації валюти.</li>
                    <li>Кожен спин впливає на загальний рахунок гравця, який відображається у таблиці лідерів. Загальний рахунок розраховується на основі очок, набраних у всіх раундах ігор, що беруть участь.</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center mt-1 ai-center">
            <img src="/img/tournaments/bet-trills/girl1.png" alt="girl1" style="width: 140px;">
            <img src="/img/tournaments/bet-trills/evo.png" alt="evo" style="height: 25px; filter: drop-shadow(0 0 5px #FFFFFF);">
            <img src="/img/tournaments/bet-trills/girl2.png" alt="girl2" style="width: 110px;">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>У турнірі є кваліфікована механіка: <strong>накопичення загальної ставки</strong>.</li>
                    <li><strong>Приклад:</strong> якщо ви поставите 1 євро і ваш виграш становитиме 12,80 євро, ви заробите 12,8 бала. Якщо у другому раунді гри ви поставите 1 євро, а виграш становитиме 7,50 євро, то ви заробите 7,5 очок, а загальна кількість зароблених очок становитиме 20,3. Будь-який виграш із мінімальною ставкою конвертується в <strong>БАЛИ</strong></li>
                    <li>У турніру є список кваліфікаційних ігор та призовий фонд (євро), який розподіляється між переможцями відповідно до правил турніру.</li>
                    <li>Якщо кілька учасників набирають однакову кількість балів, то вищу позицію в таблиці лідерів отримує учасник, який набрав перше місце.</li>
                    <li>Грошові призи будуть зараховані на рахунки гравців казино у вигляді коштів, які можна вивести після закінчення акції.</li>
                    <li>Evoplay залишає за собою право анулювати результати або не виплачувати призи у випадку, якщо всі або частина очок є результатом якоїсь очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), спричиненої машиною або людиною. помилка в будь-якій з ігор.</li>
                    <li>Evoplay також залишає за собою право анулювати бали або не сплачувати приз, якщо, на думку, вся частина рахунку є результатом шахрайства або змови з іншими гравцями.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center mt-1 ai-center">
            <img src="/img/tournaments/bet-trills/evo.png" alt="evo" style="margin-left: .5rem;height: 25px;">
    </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/bet-trills/tbg3.png"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 664 €</td>
                    <tr><td>2</td><td>1 270 €</td>
                    <tr><td>3</td><td>1 000 €</td>
                    <tr><td>4</td><td>667 €</td>
                    <tr><td>5</td><td>600 €</td>
                    <tr><td>6</td><td>539 €</td>
                    <tr><td>7</td><td>400 €</td>
                    <tr><td>8</td><td>300 €</td>
                    <tr><td>9</td><td>220 €</td>
                    <tr><td>10-19</td><td>133 €</td>
                    <tr><td>20-49</td><td>33 €</td>
                    <tr><td>50-100</td><td>20 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
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
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
