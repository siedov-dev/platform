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
        white-space: nowrap;
        color: #e4b556;
    }
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Money Wonderwood триватиме:</div>
        <div class="tournament__cond-text">З <strong>24.08.2023 </strong>по <strong> 30.08.2023</strong></div>

        <img src="/img/tournaments/money-wonderwood/tree2.png" alt="bats" style="width: 100%;">

        <div class="tournament__cond-title">Умови та положення турніру 3 Oaks Network:</div>
        <div class="flex">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>Беручи участь у турнірі, ви <strong>погоджуєтеся</strong> з Термінами та Умовами, визначеними тут, а також тими, які надані оператором.</li>
                    <li>У разі надзвичайної ситуації <strong>3 Oaks Gaming</strong> залишає за собою право скасувати промо в будь-який час, включаючи виплати виграшів, якщо це буде вважатися необхідним.</li>
                    <li>Брати участь у кампанії можуть лише раунди з <strong>реальними грошима</strong>.</li>
                    <li>Окрім <strong>ціни за спін</strong>, немає додаткових витрат для вступу в турнір.</li>
                    <li>Кожен гравець отримає <strong>особистий ідентифікатор турніру</strong> для таблиці лідерів.</li>
                    <li>Правила турніру можуть бути змінені в будь-який момент.</li>
                    <li>Помилка скасовує всі виплати.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Турнір:</strong> для того щоб мати право на отримання призу, гравець повинен виконати наступні
                умови:
                <ol class="tournament__list">
                    <li><strong>Мінімальна ставка</strong> становить <span style="color: red; font-weight: bold">0,20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">і</span>
                    <li><strong>Мінімальна кількість</strong> спінів - <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Усі набрані очки на етапі кваліфікації зберігаються та підраховуються для гравця після
                проходження кваліфікації. Некваліфіковані гравці також відображаються в Таблиці лідерів, але
                рядок для гравця без кваліфікації відзначено сірим до моменту отримання кваліфікації.
            </div>
        </div>

        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/owl_tree.png" alt="owl on branch" style="width: 100%; margin-top: -0px;">
        </div>

        <div class="tournament__cond-title">Механіка перемоги:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА МУЛЬТИПЛІКАТОРІВ:</span> якщо гравець зробив загальну ставку <strong>2 євро</strong> і виграв <strong>120 євро </strong>, гравець отримає 120(виграш)÷2(ставка) = 60-кратний множник = <strong>60 очок</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Призовий фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Прогресивний, починаючи з <span class="hot">40 000 євро</span></div>
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 10px; width: 100%'>
                <thead>
                <tr>
                    <th>Місце</th>
                    <th>Приз</th>
                    <th>Сума</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>10%</td><td>10%</td></tr>
                <tr><td>2</td><td>8%</td><td>8%</td></tr>
                <tr><td>3</td><td>6%</td><td>6%</td></tr>
                <tr><td>4</td><td>5%</td><td>5%</td></tr>
                <tr><td>5</td><td>4%</td><td>4%</td></tr>
                <tr><td>6</td><td>2.5%</td><td>2.5%</td></tr>
                <tr><td>7</td><td>1.6%</td><td>1.6%</td></tr>
                <tr><td>8</td><td>0.9%</td><td>0.9%</td></tr>
                <tr><td>9</td><td>0.8%</td><td>0.8%</td></tr>
                <tr><td>10-20</td><td>0.7%</td><td>7.7%</td></tr>
                <tr><td>21-30</td><td>0.6%</td><td>6%</td></tr>
                <tr><td>31-40</td><td>0.5%</td><td>5%</td></tr>
                <tr><td>41-50</td><td>0.4%</td><td>4%</td></tr>
                <tr><td>51-100</td><td>0.3%</td><td>15%</td></tr>
                <tr><td>101-150</td><td>0.2%</td><td>10%</td></tr>
                <tr><td>151-200</td><td>0.15%</td><td>7.5%</td></tr>
                <tr><td>201-250</td><td>0.12%</td><td>6%</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">100%</span></td>
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
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
