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
    ol.tournament__list  li {
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Турнірний період:</div>
        <div class="tournament__cond-text">З 14 по 20 жовтня 2022 року (включаючи обидва дні)</div>

        <div class="tournament__cond-title">3 Oaks Network Tournament Умови та положення:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Беручи участь у турнірі, ви погоджуєтесь з Умовами, викладеними тут, а також умовами, наданими
                    оператор.</li>
                <li>3 Oaks має право скасувати турнір на невизначений термін без будь-яких виплат відповідно до
                    таблиця лідерів у екстрених випадках.</li>
                <li>Тільки кваліфіковані гравці можуть претендувати на приз (перевірте розділ «Інформація про кампанію»).</li>
                <li>У турнірі можуть брати участь лише раунди на реальні гроші.</li>
                <li>Окрім ціни за спин, участь у турнірі не потребує додаткових витрат.</li>
                <li>Кожен гравець отримає власний ідентифікатор турніру для таблиці лідерів.</li>
                <li>Якщо у двох або більше гравців нічия, ті, хто досягне результатів раніше, отримають найкращі призи.</li>
                <li>Правила турніру можуть бути змінені у будь-який час.</li>
                <li>Несправність анулює усі виплати.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Щоб бути кваліфікованим та мати право претендувати на приз, гравець має виконати такі
                умови:
                <ol class="tournament__list">
                    <li><strong>Мінімальна ставка</strong> <span style="color: red; font-weight: bold">0.20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">ТА</span>
                    <li><strong>Мінімальна кількість</strong> спінів <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Усі очки, набрані на кваліфікаційному етапі, зберігаються та враховуються для гравця.
                після проходження кваліфікації. Некваліфіковані гравці також відображаються у таблиці лідерів.
                але роздягнені й у сірому, доки вони не кваліфіковані.
            </div>
            <img src="/img/tournaments/hot-chilli-fest/girl.png" alt="girl" style="margin-left: 1rem; width: 200px;">
        </div>


        <div class="tournament__cond-title">Підрахунок очок у турнірі (Переможна механіка):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА ВИГРАШІВ:</span> гравець поставив 1 євро загальною ставкою та виграв 120 євро, гравець отримає 120 очок
            </div>
        </div>


        <div class="tournament__cond-title">Призовий фонд:</div>
        <div class="flex ai-center">

            <table class="tournament__table" style="background: url('/img/tournaments/hot-chilli-fest/table_bg.png') no-repeat center; background-size: cover; box-shadow: #65050a 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Гравець</th>
                    <th>Приз</th>
                    <th>Сума</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                    <tr><td>2nd</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                    <tr><td>3rd</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                    <tr><td>4th - 5th</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                    <tr><td>6th - 10th</td><td>€ 750</td><td>€ 3,750</td></tr>
                    <tr><td>11th - 20th</td><td>€ 500</td><td>€ 5,000</td></tr>
                    <tr><td>21th - 50th</td><td>€ 375</td><td>€ 11,250</td></tr>
                    <tr><td>51st - 100th</td><td>€ 175</td><td>€ 8,750</td></tr>
                    <tr><td>101st - 150th</td><td>€ 100</td><td>€ 5,000</td></tr>
                    <tr><td>151st - 200th</td><td>€ 45</td><td>€ 2,250</td></tr>
                    <tr><td colspan="2"></td><td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td></tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/hot-chilli-fest/pepers.png" alt="pepers" style="width: 100%; margin-top: -120px;">
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
