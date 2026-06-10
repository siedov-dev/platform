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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: initial;">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay June Pre-party Network Tournament доступний лише для гри в реальному режимі.</li>
                    <li>Evoplay June Pre-party Network Tournament відбувається протягом 12 (дванадцяти) днів.
                        у червні місяці 2022 року. Турнір відбудеться між 00:01 UTC.
                        15 червня 2022 р. та 26 червня 2022 р. до 23:59 UTC.</li>
                    <li>Сума призового фонду турніру становить €14 000 і буде розподілена між
                        100 найкращих гравців, які набрали найбільшу кількість очок у таблицях лідерів.</li>
                    <li>Мінімальна ставка, необхідна для участі у турнірі, становить 0,20 євро або
                        еквівалент у місцевій валюті. Чим більше очок, тим вище піднімається гравець
                        у таблиці лідерів. Перші 100 гравців у таблиці лідерів отримають призи.</li>
                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в євро та
                        можуть бути схильні до коливань конвертації валюти.</li>
                    <li>Кожен спін накопичуватиме загальний рахунок гравця, який відображається на
                        Лідер таблиці. Загальний бал розраховується з урахуванням балів, набраних під час всіх
                        раундів, зіграних в іграх, що беруть участь.</li>
                </ul>
            </div>

        </div>
        <img src="/img/tournaments/pre-party/3.png" alt="playson" class="tournament__image">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Турнір має свою кваліфіковану механіку: накопичення Total Win.</li>
                    <li>Турнірні очки нараховуються за кожну перемогу (мінімальна ставка 0,20 євро)</li>
                    <li>Турнір має список кваліфікаційних ігор та призовий фонд (євро),
                        який розподіляється між переможцями згідно з правилами турніру.</li>
                    <li>Якщо кілька учасників набирають однакову кількість балів, то вищий
                        місце у таблиці лідерів віддається учаснику, що набрав перше місце.</li>
                    <li>Грошові призи будуть зараховані на рахунки гравців як можливість виведення.
                        коштів казино після закінчення акції.</li>
                    <li>Evoplay залишає за собою право анулювати бали або не виплачувати приз у разі
                        вся оцінка або її частина є результатом будь-якої очевидної помилки, помилки чи технічного
                        помилка (включаючи неправильні ігрові виплати), спричинена машиною або людиною.</li>
                    <li>Evoplay також залишає за собою право скасувати бали або не сплачувати призи.
                        де, на думку, вся частина рахунку є результатом обману чи змови
                        з іншими гравцями.</li>
                </ul>
            </div>
        </div>
        <img src="/img/tournaments/pre-party/1.png" alt="Lion head" class="tournament__image">

        <div class="flex ai-center mt-2 mb-1">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>2 330 €</td>
                    <tr><td>2</td><td>1 770 €</td>
                    <tr><td>3</td><td>1 400 €</td>
                    <tr><td>4</td><td>934 €</td>
                    <tr><td>5</td><td>840 €</td>
                    <tr><td>6</td><td>746 €</td>
                    <tr><td>7</td><td>560 €</td>
                    <tr><td>8</td><td>410 €</td>
                    <tr><td>9</td><td>308 €</td>
                    <tr><td>10-19</td><td>186 €</td>
                    <tr><td>20-49</td><td>46 €</td>
                    <tr><td>50-100</td><td>28 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 14 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/pre-party/athena.png" alt="fly coins" class="tournament__image">

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

    @include('phone.footer')

</div>
