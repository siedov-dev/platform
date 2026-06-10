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
        color: #FFFFFF;
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

                <div class="tournament__cond-title">Правила та умови:</div>
                <ul class="tournament__list">
                    <li>Мережевий турнір Evoplay Hot Celebration доступний лише у реальному режимі.</li>

                    <li>Мережевий турнір Evoplay Hot Celebration проводиться на 12 (дванадцять) осіб за погодженням
                        днів у серпні місяці 2022 року. Турнір відбудеться між
                        00:01 UTC 11 серпня 2022 р. та 22 серпня 2022 р. до 23:59 UTC.</li>

                    <li>Сума призового фонду турніру складає 15 000 євро і буде розподілена
                        серед 100 найкращих гравців, які набрали найвищі бали у списках лідерів.</li>

                    <li>Мінімальна ставка, необхідна для участі у турнірі, становить 0,20 євро або
                        еквівалент у місцевій валюті. Чим більше очок, тим вище піднімається гравець
                        Таблиця лідерів. Перші 100 гравців у таблиці лідерів отримають призи. </li>

                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в євро та
                        можуть бути схильні до коливань конвертації валюти.</li>
                </ul>
            </div>

            <img src="/img/tournaments/spins-of-love/masks.png" alt="Gambling lady" class="tournament__image" style="margin-left: 3rem;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/hot-celebration/lady.png" alt="Gambling man" class="tournament__image" style="margin-right: 3rem;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Кожен спин впливає на загальний рахунок гравця, який відображається на
                        Лідер таблиці. Загальний бал розраховується з урахуванням балів, набраних під час всіх
                        раундів, зіграних в іграх, що беруть участь.</li>
                    <li>У турніру є кваліфікована механіка: накопичення загального виграшу.</li>

                    <li>У турніру є список кваліфікаційних ігор та призовий фонд (євро), який
                        розподіляється між переможцями таблиці лідерів за даними турніру
                        правила.</li>

                    <li>Якщо кілька Учасників набирають однакову кількість балів у процесі
                        при підрахунку результатів турніру дається більш висока позиція у рейтингу
                        Учаснику, який набрав першу кількість очок.</li>

                    <li>Грошові призи будуть зараховані на рахунки гравців у вигляді коштів, які можна зняти.
                        казино після закінчення турніру.</li>

                    <li>Evoplay залишає за собою право анулювати результати або не сплачувати приз, якщо всі або
                        частина рахунку є результатом будь-якої очевидної помилки, помилки чи технічної несправності
                        (включаючи неправильні ігрові виплати), будь то машина чи людина
                        помилка щодо будь-якої з ігор, що беруть участь.</li>
                </ul>
            </div>
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay також залишає за собою право анулювати бали або не сплачувати призи.
                        де, на думку, вся частина рахунку є результатом обману чи змови
                        з іншими гравцями.</li>

                    <li>Система винагороди розраховується відповідно до призового фонду та
                        встановити кількість лідерів, що беруть участь.</li>

                    <li><b>Участь в іграх:</b>
                        Північне шоу, Candy Dreams, Anubis Moon, Індіана квест,
                        Прокляття Фараона, Руни Долі, Таверна Дракона, Лісові Сни, Древо Світла</li>

                    <li>Турнірний рейтинг: загальна кількість перемог.</li>
                    <li>Критерії турніру: турнірні очки нараховуються за
                        кожний виграш (мінімальна ставка 0,20 EUR)</li>

                    <li><b>Приклад:</b> якщо ви ставите 1 євро і ваш виграш становить 12,80 євро, то
                        ви заробляєте 12,8 балів. Якщо у другому раунді гри ви
                        постав €1 і виграй €7,50, то заробиш 7,5
                        балів, а загальна кількість зароблених балів становитиме 20,3.
                        Будь-який виграш з мінімальною ставкою конвертується в
                        БАЛИ</li>
                </ul>
            </div>

            <img src="/img/tournaments/hot-celebration/guy3.png" alt="Gambling lady" class="tournament__image" style="margin-left: 3rem;">
        </div>



        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/hot-celebration/cr.png') no-repeat center; background-size: contain;" >
            <div class="tournament__cond-text" style="width: 20rem; padding-bottom: 5rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style=" background: linear-gradient(#5f32c699, #32187899);">
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>2 481 €</td></tr>
                    <tr><td>2nd</td><td>1 900 €</td></tr>
                    <tr><td>3rd</td><td>1 500 €</td></tr>
                    <tr><td>4th</td><td>1 000 €</td></tr>
                    <tr><td>5th</td><td>900 €</td></tr>
                    <tr><td>6th</td><td>809 €</td></tr>
                    <tr><td>7th</td><td>600 €</td></tr>
                    <tr><td>8th</td><td>450 €</td></tr>
                    <tr><td>9th</td><td>330 €</td></tr>
                    <tr><td>10th - 19th</td><td>200 €</td></tr>
                    <tr><td>20th - 49th</td><td>50 €</td></tr>
                    <tr><td>50th - 100th</td><td>30 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 15 000 €</b></td></tr>
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
