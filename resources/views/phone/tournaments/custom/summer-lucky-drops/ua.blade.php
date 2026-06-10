@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__cond-title{
        font-size: 1.2rem;
    }
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
    .tournament__cond-text{
        font-size: 0.875rem;
    }
    ul li::marker {
        background-color: #fec30c;
        color: #fec30c;
    }
    .tournament__games-list {
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
    }
    span{
        word-break: keep-all;
        white-space: nowrap;
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

        <div class="tournament__cond-title">Турнiр "Lucky Drops" триває:</div>
        <div class="tournament__cond-text">Від <strong>24 до 27 серпня 2023 року</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="seaguls" style="width: 260px; height: 260px; margin-bottom: -20px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Гарячий період</span> - збільшує шанси гравця на виграш призу:</div>
        <div class="tournament__cond-text">Починаючи з <span style="color: red; font-weight: bold">26.08.2023 0:00 UTC</span> і до закінчення кампанії <strong>Weekend Lucky Drops</strong></div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/cocktail3.png" alt="cocktail" style="width: 150px;">
        </div>

        <div class="tournament__cond-title">Умови та положення турніру 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Беручи участь у турнірі, ви погоджуєтеся з умовами та положеннями, викладеними тут, а також з тими, що надаються оператором.</li>
                <li>У випадках надзвичайних ситуацій, 3 Oaks Gaming залишає за собою право в будь-який момент скасувати акцію Lucky Drops, включаючи виплати виграшів, якщо це вважається необхідним.</li>
                <li>У кампанії Lucky Drops можуть брати участь тільки реальні грошові раунди.</li>
                <li>Крім вартості спіну, участь у Network Lucky Drops не вимагає додаткової оплати.</li>
                <li>Кожен гравець отримає персональний ідентифікатор турніру для лідерборда.</li>
                <li>Правила Network Lucky Drops можуть бути змінені в будь-який момент.</li>
                <li>Збій анулює всі виплати.</li>
            </ul>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacation" style="width: 250px;">
        </div>

        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Гравець повинен приєднатися або вибрати будь-яку зі співпрацюючих ігор.</li>
                    <li>Мінімальна ставка, необхідна для отримання LuckyDrop, становить <span style="color: red; font-weight: bold">0.50 EUR</span> або еквівалентну суму в доступних валютах участьових ігор.</li>
                    <li>Кожна кваліфікуюча ставка, зроблена в будь-якій зі співпрацюючих ігор, може спричинити випадковий приз з призового фонду протягом акційного періоду.</li>
                    <li>Кожен гравець може виграти максимум <span style="color: red; font-weight: bold">3 призи</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Механіка виграшу:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Lucky Drops -</span> дає гравцям можливість виграти грошові призи в будь-який момент протягом періоду події. Призи надаються випадковим чином, і у всіх гравців однакові шанси на виграш у кожному кваліфікуючому спіні.
            </div>
        </div>

        <img src="/img/tournaments/summer-lucky-drops/man_hammok.png" alt="beach" style="width: 100%;">

        <div class="tournament__cond-title">Ігри, що беруть участь:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Всі ігри</span></div>

        <div class="tournament__cond-title">Призовий фонд:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text">Призовий фонд для цієї кампанії становить 15 000 EUR (або еквівалентну суму валюти гравця) і складається з декількох пулів за такою структурою:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15 000 EUR</span> з 24.08.2023 00:00 UTC до 27.08.2023 23:00 UTC</div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>15</td><td>€ 100</td><td>€ 1,500</td></tr>
                <tr><td>30</td><td>€ 60</td><td>€ 1,800</td></tr>
                <tr><td>45</td><td>€ 50</td><td>€ 2,250</td></tr>
                <tr><td>60</td><td>€ 40</td><td>€ 2,400</td></tr>
                <tr><td>80</td><td>€ 30</td><td>€ 2,400</td></tr>
                <tr><td>120</td><td>€ 20</td><td>€ 2,400</td></tr>
                <tr><td>150</td><td>€ 15</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/summer-lucky-drops/water3.png" alt="wave" style="width: 100%; margin-top: -100%; z-index: -5; height: 120px;">
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
