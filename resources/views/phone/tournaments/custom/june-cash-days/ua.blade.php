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
        color: #FFF;
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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: initial;">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Умови та положення:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">June CashDays 80k</span> доступний лише для гри в реальному режимі</li>

                    <li>June CashDays 80k проходить між <span class="yellow-text">0:01 UTC 1 червня.
                            2023 р. та 8 червня 2023 р. до 23:59 UTC</span></li>

                    <li>Мінімальна ставка, необхідна для участі у розіграші призів, становить <span class="yellow-text">0,20 євро</span> або еквівалент в іншій валюті.</li>

                    <li>Будь-який виграш накопичуватиметься на загальному рахунку гравця, який відображатиметься в
                        Таблиця лідерів. Загальний бал розраховується шляхом складання набраних балів
                        у всіх раундах, зіграних в іграх, що беруть участь у період проведення турніру.</li>

                    <li>Бали нараховуються поступово, з кожним 1 євро (або еквівалентом в іншій валюті)
                        виграш складає 10 очок. <span class="yellow-text">Наприклад, 10 євро виграшу дорівнюють 100 балам.</span></li>

                    <li>Грошові призи будуть зараховані на рахунки гравців у вигляді коштів, які можна вивести.
                        протягом 72 годин (3 робочих днів) після закінчення акції.</li>

                </ul>

            </div>

        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/june-cash-days/cup.png" alt="Lion head" class="tournament__image" style="filter: drop-shadow(0 0 5px gold); height: 10rem; width: 12rem;">
        </div>

        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлені в <span class="yellow-text">євро</span> і можуть бути
                        залежить від коливань конвертації валюти.</li>

                    <li>Курси конвертації валют встановлюються нашою системою автоматично за допомогою 72
                        годин до початку турніру, а значення залишаться незмінними під час
                        весь період турніру.</li>

                    <li>Ми залишаємо за собою право анулювати бали або не виплачувати приз, якщо всі чи частина
                        оцінка результатів внаслідок будь-якої очевидної помилки, помилки або технічного збою (включаючи
                        ігрові виплати), незалежно від того, викликані вони машинною або людською помилкою стосовно будь-якого з
                        беруть участь ігри.</li>

                    <li>Крім того, ми залишаємо за собою право анулювати бали або не виплачувати приз, якщо за нашими
                        на думку, весь рахунок або його частина є результатом шахрайства чи змови з іншими гравцями.</li>

                    <li>Ми залишаємо за собою право змінити або скасувати кампанію у будь-який час.</li>
                </ul>
            </div>
        </div>
        <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image" style="filter: drop-shadow(0 0 5px white);">


        <div class="flex ai-center mb-1">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/june-cash-days/tbg.png"); background-size: cover; box-shadow: #0C0328 0 0 5px;'>
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                        <th>Сума</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>10 000 €</td><td>10 000 €</td></tr>
                    <tr><td>2nd</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>3rd</td><td>3 000 €</td><td>3 000 €</td></tr>
                    <tr><td>4th -10th</td><td>1 000 €</td><td>7 000 €</td></tr>
                    <tr><td>11th -25th</td><td>750 €</td><td>11 250 €</td></tr>
                    <tr><td>26th - 50th</td><td>350 €</td><td>8 750 €</td></tr>
                    <tr><td>51th - 100th</td><td>200 €</td><td>10 000 €</td></tr>
                    <tr><td>101th - 200th</td><td>100 €</td><td>10 000 €</td></tr>
                    <tr><td>201th - 500th</td><td>50 €</td><td>15 000 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b><span class="yellow-text">Grand Total 80 000 €</span></b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/june-cash-days/fly-coins.png" alt="fly coins" class="tournament__image" style="filter: drop-shadow(0 0 5px gold);">


    </div>



    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
            <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
