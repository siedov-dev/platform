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
        font-size: 0.875rem;
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Турнірний період:</div>
        <div style="font-size: 0.875rem; font-weight: 600; color: #FFFFFF; text-shadow: #cf2d17 0 0 4px; margin-top: 0.5rem;">З 31 жовтня до 1 листопада 2022 року (включаючи обидва дні)</div>
        <div class="flex mt-1" style="justify-content: space-evenly;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
        </div>


        <div class="tournament__cond-title" style="margin-top: 0px;">3 Oaks Network Tournament Умови та положення:</div>
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

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Щоб бути кваліфікованим та мати право претендувати на приз, гравець має виконати такі
                умови:
                <ol class="tournament__list" style="margin-right: 10rem;">
                    <li><strong>Мінімальна ставка</strong> <span style="color: red; font-weight: bold">1.00 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">ТА</span>
                    <li><strong>Мінімальна кількість</strong> спінів <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                <div class="flex" style="justify-content: flex-end">
                    <img src="/img/tournaments/pumpkin-fortune/pumpkin08.png" alt="pumpkin" style="width: 50%; filter: drop-shadow(0 0 5px #fcc339); margin-top: -8rem;">
                </div>
                Усі очки, набрані на кваліфікаційному етапі, зберігаються та враховуються для гравця.
                після проходження кваліфікації. Некваліфіковані гравці також відображаються у таблиці лідерів.
                але роздягнені й у сірому, доки вони не кваліфіковані.
            </div>
        </div>


        <div class="tournament__cond-title">Підрахунок очок у турнірі (Переможна механіка):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> HSRM:</span> гравці отримають бали за найвищий множник в одному раунді
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/pumpkin-fortune/broom1.png" alt="gates" style="filter: drop-shadow(0 0 5px #0a0a17)">
        </div>


        <div class="tournament__cond-title">Призовий фонд:</div>
        <div class="flex ai-center">

            <table class="tournament__table" style="width: 100%; background: #0a0a17A0; background-size: cover; z-index: 2; border-radius: 10px;">
                <thead>
                <tr>
                    <th>Гравець</th>
                    <th>Приз</th>
                    <th>Сума</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1st</td><td>10%</td><td>10%</td></tr>
                <tr><td>2nd</td><td>8%</td><td>8%</td></tr>
                <tr><td>3rd</td><td>6%</td><td>6%</td></tr>
                <tr><td>4th</td><td>5%</td><td>5%</td></tr>
                <tr><td>5th</td><td>4%</td><td>4%</td></tr>
                <tr><td>6th</td><td>2.5%</td><td>2.5%</td></tr>
                <tr><td>7th</td><td>1.6%</td><td>1.6%</td></tr>
                <tr><td>8th</td><td>0.9%</td><td>0.9%</td></tr>
                <tr><td>9th</td><td>0.8%</td><td>0.8%</td></tr>
                <tr><td>10th - 20th</td><td>0.7%</td><td>7.7%</td></tr>
                <tr><td>21th - 30th</td><td>0.6%</td><td>6%</td></tr>
                <tr><td>31th - 40th</td><td>0.5%</td><td>5%</td></tr>
                <tr><td>41th - 50th</td><td>0.4%</td><td>4%</td></tr>
                <tr><td>51th - 100th</td><td>0.3%</td><td>15%</td></tr>
                <tr><td>101th - 150th</td><td>0.2%</td><td>10%</td></tr>
                <tr><td>151th - 200th</td><td>0.15%</td><td>7.5%</td></tr>
                <tr><td>201th - 250th</td><td>0.12%</td><td>6%</td></tr>
                <tr><td colspan="2"></td><td><span style="color: red; text-transform: uppercase; font-weight: bold">100%</span></td></tr>
                </tbody>
            </table>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/pumpkin-fortune/h_gates2.png" alt="gates" style="width: 90%; margin-top: 1rem; filter: drop-shadow(0 0 10px #0a0a17)">
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
