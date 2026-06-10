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
    .tournament__list strong{
        color: #00b8ff;
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
                    <li>Мережевий турнір Evoplay Goal Hitting Network доступний лише у реальному режимі.</li>

                    <li>Мережевий турнір Evoplay Goal Hitting Network Tournament проводиться протягом (7) семи погоджених днів у листопаді 2022 р. Турнір проходитиме з 00:01 UTC 17 листопада 2022 р. до 23 листопада 2022 р. до 23:59 UTC. </li>

                    <li>Сума призового фонду турніру складає <strong>10 000 євро</strong> і буде розподілена між 100 найкращими гравцями, які набрали найбільшу кількість очок у списках лідерів.</li>

                    <li>Мінімальна ставка, необхідна для участі в турнірі, становить <strong>0,50 євро</strong> або еквівалент у місцевій валюті. Що більше очок, то вище гравець піднімається в таблиці лідерів. Перші 100 гравців у таблиці лідерів отримають призи. </li>

                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в <strong>євро</strong> і можуть залежати від коливань конвертації валюти.</li>
                </ul>
            </div>

        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/ball.png" alt="Gambling lady" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff)">
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Кожен спин впливає на загальний рахунок гравця, який відображається у таблиці лідерів. Загальний рахунок розраховується на основі очок, набраних у всіх раундах ігор, що беруть участь.</li>
                    <li>У турніру є кваліфікована механіка: накопичення загального виграшу.</li>

                    <li>У турнірі є <strong>список кваліфікаційних ігор та призовий фонд</strong> (євро), який розподіляється між переможцями таблиці лідерів відповідно до цих правил турніру.</li>

                    <li>Якщо кілька Учасників набирають однакову кількість очок у процесі підрахунку результатів турніру, вища позиція в рейтингу віддається Учаснику, який набрав перше місце.</li>

                    <li>Кошти будуть зараховані на рахунки гравців у вигляді коштів, які можуть бути зняті казино після закінчення турніру.</li>

                    <li>Evoplay залишає за собою право анулювати бали або не сплачувати приз, якщо всі або частина балів є результатом якоїсь очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), спричиненої машинною або людською помилкою. стосовно будь-якої з ігор, що беруть участь.</li>

                    <li>Evoplay також залишає за собою право анулювати бали або не сплачувати приз, якщо, на думку, вся частина рахунку є результатом шахрайства або змови з іншими гравцями.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/cup.png" alt="Gambling man" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff);">
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">

                    <li><strong>Турнірний рейтинг:</strong> загальна кількість перемог.</li>
                    <li><strong>Турнірні критерії:</strong> турнірні очки нараховуються за кожну перемогу (мінімальна ставка 0,50 євро)</li>

                    <li><strong>Приклад:</strong> якщо ви поставите 1 євро і ваш виграш становитиме 12,80 євро, ви заробите 12,8 бала. Якщо у другому раунді гри ви поставите 1 євро, а виграш становитиме 7,50 євро, то ви заробите 7,5 очок, а загальна кількість зароблених очок становитиме 20,3. Усі виграші з мінімальною ставкою конвертуються в БАЛИ</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/goal-hitting/evo.png" alt="Gambling lady" class="tournament__image" style="filter: drop-shadow(0 0 10px #00b8ff);">
        </div>



        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/goal-hitting/stadium.png') no-repeat center; background-size: cover; border-radius: 2rem;" >
            <div class="tournament__cond-text" style="width: 20rem; padding-bottom: 5rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style=" background: linear-gradient(#8f173399, #4d4ab599);">
                    <thead>
                    <tr>
                        <th>Гравець</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>1 664 €</td></tr>
                    <tr><td>2nd</td><td>1 270 €</td></tr>
                    <tr><td>3rd</td><td>1 000 €</td></tr>
                    <tr><td>4th</td><td>667 €</td></tr>
                    <tr><td>5th</td><td>600 €</td></tr>
                    <tr><td>6th</td><td>539 €</td></tr>
                    <tr><td>7th</td><td>400 €</td></tr>
                    <tr><td>8th</td><td>300 €</td></tr>
                    <tr><td>9th</td><td>220 €</td></tr>
                    <tr><td>10th - 19th</td><td>133 €</td></tr>
                    <tr><td>20th - 49th</td><td>33 €</td></tr>
                    <tr><td>50th - 100th</td><td>20 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
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
