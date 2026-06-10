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
    .article__text strong.gold{
        color: #d7b96b;
    }
    .tournament__list strong{
        color: #d7b96b;
    }
    .article__text b.red{
        color: red;
        white-space: nowrap;
    }
    @keyframes gold-shine {
        from {filter: drop-shadow(0 0 0px goldenrod);}
        to {filter: drop-shadow(0 0 10px goldenrod);}
    }
    .shine{
        animation: gold-shine;
        animation-duration: 2s;
        filter: drop-shadow(0 0 10px goldenrod);
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-text">Турнір <strong class="gold">Non-Stop Drop June 150,000 EUR</strong> проходить з <b class="red">0:01 01.06.2023 UTC</b> по <b class="red">30.06.2023 до 23:59 UTC</b> і поділений на <b class="red">щотижневі</b> фази</div>
        <div class="tournament__cond-text"><strong>Фаза 1:</strong> <b class="red">01.06.2023 - 11.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 2:</strong> <b class="red">12.06.2023 - 18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 3:</strong> <b class="red">19.06.2023 - 25.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 4:</strong> <b class="red">26.06.2023 - 30.06.2023</b></div>
        <div style="display: flex; margin-top: -80px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/non-stop/cup.png" alt="grass" style="height: 230px; margin-bottom: -120px; margin-right: 50px;">
        </div>
        <div class="tournament__cond-text" style="padding-right: 300px;">
            Функція <b class="red">Cash Blast</b> пропонує гравцям можливість виграти грошові призи в будь-який час протягом події. Призи пропонуються випадковим чином, і кожен гравець має однакові шанси на виграш після кожного спіна.
        </div>

        <div class="tournament__cond-title">Змагання:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li><strong>Non-Stop Drop 1,000,000 EUR</strong> доступний лише в режимі реальної гри.</li>
                <li>Мінімальна ставка для участі складає <b class="red">0.20 EUR</b> або еквівалент у іншій валюті.</li>
                <li>Будь-який спін на ігрових автоматах, що беруть участь у акції, може запустити випадковий приз <b class="red">Cash Blast</b>.</li>
                <li>Будь-який окремий гравець може виграти декілька призів <b class="red">Cash Blast</b> протягом періоду акції.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Призи:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li><strong>Грошові призи</strong> будуть автоматично зараховані на рахунок гравців одразу після отримання виграшу. Після зарахування приз буде видалений з призового фонду. Кількість залишених призів відображається в реальному часі в віджеті <b class="red">Cash Blast</b> або в розділі <b class="red">Призи</b>.</li>
                    <li>Призовий фонд і мінімальна ставка для цієї кампанії встановлені в <b class="red">EUR</b> і можуть підлягати коливанням курсу обміну валюти.</li>
                    <li>Курси обміну валюти встановлюються автоматично нашою системою за <b class="red">72 години</b> до початку турніру і залишаються незмінними протягом усього періоду турніру.</li>
                </ul>
            </div>
            <img src="/img/tournaments/non-stop/nonstop_tag.png" alt="nonstop_tag" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Загальні правила:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Playson залишає за собою право змінити, призупинити або скасувати Мережеву акцію в будь-який час, включаючи будь-які пов'язані з нею умови та положення.</li>
                    <li>Ми залишаємо за собою право анулювати очки або не виплачувати приз, якщо всі або частина результатів отримані внаслідок очевидної помилки, помилки або технічного збою (включаючи неправильні виплати в грі), викликаного як машиною, так і людською помилкою щодо будь-якої з участь у грах.</li>
                    <li>Ми також залишаємо за собою право анулювати очки або не виплачувати приз, якщо, на нашу думку, всі або частина результатів отримані внаслідок шахрайства або змови з іншими гравцями.</li>
                    <li>Ми залишаємо за собою право змінити або скасувати кампанію в будь-який час.</li>
                </ul>
            </div>
            <img src="/img/tournaments/non-stop/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Таблиця призів:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>150,000 EUR/ місяць</strong>, <b class="red">37,500 EUR/ тиждень</b></div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Приз</th>
                    <th>Кількість</th>
                    <th>Сума EUR</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>€ 5,000</td><td>1</td><td>€ 5,000</td></tr>
                <tr><td>€ 2,500</td><td>2</td><td>€ 5,000</td></tr>
                <tr><td>€ 1,000</td><td>4</td><td>€ 4,000</td></tr>
                <tr><td>€ 500</td><td>10</td><td>€ 5,000</td></tr>
                <tr><td>€ 250</td><td>20</td><td>€ 5,000</td></tr>
                <tr><td>€ 100</td><td>40</td><td>€ 4,000</td></tr>
                <tr><td>€ 75</td><td>50</td><td>€ 3,750</td></tr>
                <tr><td>€ 50</td><td>60</td><td>€ 3,000</td></tr>
                <tr><td>€ 25</td><td>70</td><td>€ 1,750</td></tr>
                <tr><td>€ 10</td><td>100</td><td>€ 1,000</td></tr>
                <tr>
                    <td><strong>Разом</strong></td>
                    <td><b class="red">357</b></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 37,500</span></td>
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
