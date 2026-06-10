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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Умови та положення:</div>
                <ul class="tournament__list">
                    <li>Турнір SpinMania 30k доступний лише для гри в реальному режимі</li>

                    <li>Турнір SpinMania 30k відбувається між 0:01 UTC 26 травня 2022 року та 29 травня 2022 року.
                        травня 2022 р. до 23:59 UTC.</li>

                    <li>Мінімальна ставка, потрібна для участі, становить 0,40 євро або еквівалент в іншій валюті.</li>

                    <li>Кожен спін у розмірі 0,40 євро або більше накопичуватиме загальний рахунок гравця, який буде показаний
                        у таблиці лідерів. Загальний бал розраховується шляхом складання балів, набраних під час
                        всіх спинів зроблених у іграх, що беруть участь під час проведення турніру.</li>

                    <li>Бали нараховуються раунд за раундом, при цьому кожен оплачений спін дорівнює 1 балу. Загальний бал =
                        загальна кількість спінів від 0,40 євро та більше.</li>

                    <li>Грошові призи будуть зараховані на рахунки гравців у вигляді коштів, що виводяться протягом 72
                        годин (3 робочі дні) після закінчення акції.</li>

                </ul>
            </div>

            <img src="/img/tournaments/spin-mania/lion-head.png" alt="Lion head" class="tournament__image" style="margin-left: 3rem;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image" style="margin-right: 3rem;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлені в євро та можуть залежати
                        від коливань конвертації валюти.</li>

                    <li>Курси конвертації валют встановлюються нашою системою автоматично протягом 72 годин.
                        до початку турніру, а значення залишаться незмінними протягом усього
                        Турнірного періоду.</li>

                    <li>Ми залишаємо за собою право анулювати бали або не сплачувати приз, якщо всі або частина балів
                        є результатом будь-якої очевидної помилки чи технічного збою (включаючи неправильні ігрові виплати)
                        будь то машинна або людська помилка щодо будь-якої з ігор, що беруть участь.</li>

                    <li>Ми також залишаємо за собою право анулювати бали або не виплачувати приз, якщо, на нашу думку, всі
                        або частина рахунку є результатом шахрайства чи змови з іншими гравцями.</li>

                    <li>Ми залишаємо за собою право змінити або скасувати кампанію будь-коли.</li>

                </ul>
            </div>
        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                        <th>Сумма</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>2</td><td>2 5000 €</td><td>2 500 €</td></tr>
                    <tr><td>3</td><td>1 500 €</td><td>1 500 €</td></tr>
                    <tr><td>4 -10</td><td>500 €</td><td>3 500 €</td></tr>
                    <tr><td>11 -25</td><td>250 €</td><td>3 750 €</td></tr>
                    <tr><td>26 - 50</td><td>100 €</td><td>2 500 €</td></tr>
                    <tr><td>51 - 100</td><td>75 €</td><td>3 750 €</td></tr>
                    <tr><td>101 - 200</td><td>50 €</td><td>5 000 €</td></tr>
                    <tr><td>201 - 300</td><td>25 €</td><td>2 500 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b>Grand Total 30 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/spin-mania/fly-coins.png" alt="fly coins" class="tournament__image" style="margin-left: 3rem;">
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
