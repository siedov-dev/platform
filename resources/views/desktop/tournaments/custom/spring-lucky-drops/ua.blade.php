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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Період мережевої кампанії:</div>
        <div class="tournament__cond-text">З <strong>15–28 травня 2023 р.</strong> (включаючи обидва дні)</div>
        <div class="tournament__cond-text"><strong>Тиждень 1:</strong> 15.05.2023-21.05.2023</div>
        <div class="tournament__cond-text"><strong>Тиждень 2:</strong> 22.05.2023-28.05.2023</div>

        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img src="/img/tournaments/spring-lucky-drops/leaves.png" alt="grass" style="width: 280px; height: 320px; margin-bottom: -180px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Гарячий період</span> - збільшує шанс гравця на виграш призу.:</div>
        <div class="tournament__cond-text"><strong>Тиждень 1:</strong> Початок з <span style="color: red; font-weight: bold">18.05.2023 0:00 UTC</span> закінчення кампанії <strong>Weekly Lucky Drops</strong></div>
        <div class="tournament__cond-text"><strong>Тиждень 2:</strong> початок з <span style="color: red; font-weight: bold">25.05.2023 0:00 UTC</span> закінчення кампанії <strong>Weekly Lucky Drops</strong></div>

        <div class="tournament__cond-title">Положення та умови турніру 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Беручи участь у турнірі, ви погоджуєтесь з Умовами, викладеними тут, а також умовами, наданими оператором.</li>
                <li>В екстрених випадках 3 Oaks Gaming залишає за собою право скасувати акцію Lucky Drops у будь-який час, включаючи виплату виграшів, якщо це буде необхідним.</li>
                <li>У кампанії Lucky Drops можуть брати участь лише раунди з реальними грошима.</li>
                <li>Крім плати за обертання, за участь у Network Lucky Drops не стягується додаткова плата.</li>
                <li>Кожен гравець отримає особистий турнірний ідентифікатор для таблиці лідерів.</li>
                <li>Мережні правила Lucky Drops можна змінити у будь-який час.</li>
                <li>Несправність анулює всі виплати.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Гравець повинен приєднатися або взяти участь у будь-якій з ігор, що беруть участь.</li>
                    <li>Мінімальна ставка, необхідна для отримання LuckyDrop, становить <span style="color: red; font-weight: bold">0,2 євро</span> або еквівалентну суму в доступних валютах у грі, що бере участь( с).< /li>
                    <li>Кожна кваліфікаційна ставка, розміщена в будь-якій з ігор, що беруть участь, може викликати отримання випадкового призу з призового фонду протягом періоду проведення акції.</li>
                    <li>Кожен гравець може виграти максимум 3 призи.</li>
                </ul>
            </div>
            <img src="/img/tournaments/spring-lucky-drops/sakura2.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Переможна механіка:</div>
        <div class="tournament__cond-text">
            <div class="турнір__список">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops — </span> пропонує гравцям можливість виграти грошові призи у будь-який час під час заходу.
                період. Призи видаються випадковим чином, і всі гравці мають рівні шанси на перемогу у
                кожне кваліфікаційне обертання.
            </div>
        </div>
        <div class="tournament__cond-title">Ігри, що беруть участь:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Всі ігри</span></div>
        <div class="tournament__cond-title">Призовий фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Призовий фонд цієї Кампанії становить 60 000 євро (або еквівалент у валюті гравця), об'єднаний з кількох активів за наступною оцінкою:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">60 000 євро</span> за період (15.05.2023 00:00 UTC to 28.05.2023 23:00 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">30 000 євро</span> всього за <span style="color: red; font-weight: bold">Кожен тиждень</span></li>
        </ul>

        <div class="flex ai-center mt-1">

            <table class="tournament__table"
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
        <img src="/img/tournaments/spring-lucky-drops/grass.png" alt="grass" style="width: 100%; margin-top: 0px;">
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
