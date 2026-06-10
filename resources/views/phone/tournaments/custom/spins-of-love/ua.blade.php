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
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Правила та умови:</div>
                <ul class="tournament__list">
                    <li>Evoplay Spins of Love Network Tournament доступний лише для гри на реальні гроші.</li>

                    <li>Instant Network Tournament Evoplay триває 12 днів у лютому 2022 року. Турнір пройде
                        в період з 0:01 UTC 8 лютого 2022 року до 19 лютого
                        2022 року, 23:59 UTC.</li>

                    <li>Загальна сума призу турніру становить 12000 євро і буде розподілена між
                        100 найкращих гравців, які набрали найбільшу кількість очок у таблиці лідерів.</li>

                    <li>Мінімальна ставка, необхідна для участі у турнірі, становить 0,20 євро або
                        еквівалент у місцевій валюті. Що більше очок, то вище піднімається гравець у таблиці лідерів.
                        Перші 100 гравців у таблиці лідерів отримають призи.</li>

                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в євро та може залежати від коливань курсу валюти.</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/spins-of-love/masks.png" alt="Gambling lady" class="tournament__image">

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Кожне обертання сприятиме загальному рахунку гравця, який відображається на
                        Лідер таблиці. Загальний бал розраховується шляхом складання набраних балів
                        у всіх раундах, зіграних в іграх, що беруть участь.</li>

                    <li>Турнір має свою кваліфіковану механіку: накопичення Total Win.</li>

                    <li>Турнір має список кваліфікаційних ігор та призовий фонд (євро), який
                        розподіляється між переможцями таблиці лідерів за правилами турніру.</li>

                    <li>Якщо кілька Учасників набирають однакову кількість балів у процесі
                        підрахунку результатів турніру, дається більш висока позиція у рейтингу
                        учаснику, який набрав кількість очок першим.</li>

                    <li>Грошові призи будуть зараховані на рахунки гравців як можливість виведення
                        кошти після закінчення турніру.</li>

                    <li>Evoplay залишає за собою право анулювати бали або не сплачувати приз, якщо всі або
                        частина рахунку є результатом будь-якої очевидної помилки, помилки чи технічної несправності
                        (включаючи неправильні ігрові виплати), будь то машина або людина щодо будь-якої з ігор, що беруть участь.</li>
                </ul>
            </div>
        </div>

        <img src="/img/tournaments/spins-of-love/sphere.png" alt="Gambling man" class="tournament__image">

        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay залишає за собою право змінювати, зупиняти або скасовувати турнір у будь-яке.
                        час та без попереднього повідомлення.</li>

                    <li>Система винагороди розраховується відповідно до призового фонду та
                        встановлюється відповідно до таблиці виграшів.</li>

                    <li><b>Участь в іграх:</b>
                        Midnight show, Candy Dreams, Anubis Moon, Indiana's quest,
                        Curse Of The Pharaon, Runes Of Destiny, Dragon's Tavern, Forest Dreams, Tree Of Light</li>

                    <li><b>Турнірний рейтинг:</b> накопичення загального виграшу.</li>

                    <li><b>Механіка:</b> Підсумовуються усі суми виграшів, де 1 очко = 1
                        грошова одиниця базової валюти турніру Конвертуються всі
                        значення виграшу в іншій валюті у валюту турніру.
                        Ставки тільки на суму більшу або рівну
                        мінімальної суми ставки турніру.</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/spins-of-love/box.png" alt="Gambling girl" class="tournament__image">

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>2 000 €</td></tr>
                    <tr><td>2nd</td><td>1 520 €</td></tr>
                    <tr><td>3rd</td><td>1 200 €</td></tr>
                    <tr><td>4th</td><td>800 €</td></tr>
                    <tr><td>5th</td><td>720 €</td></tr>
                    <tr><td>6th</td><td>640 €</td></tr>
                    <tr><td>7th</td><td>480 €</td></tr>
                    <tr><td>8th</td><td>352 €</td></tr>
                    <tr><td>9th</td><td>264 €</td></tr>
                    <tr><td>10th - 19th</td><td>160 €</td></tr>
                    <tr><td>20th - 49th</td><td>40 €</td></tr>
                    <tr><td>50th - 100th</td><td>24 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Усього 12 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/spins-of-love/girl.png" alt="Gambling man" class="tournament__image mt-2">


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
