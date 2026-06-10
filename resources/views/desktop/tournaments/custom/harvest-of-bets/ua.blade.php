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
        box-shadow: #0d8f1b 0 0 5px;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;
        font-weight: 500;
        text-align: center;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table tr:last-child td{
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__table th {
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
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
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Мережевий турнір Evoplay Harvest Of Bets доступний для гри лише в реальному режимі.</li>
                    <li>Мережевий турнір Evoplay Harvest of Bets проводиться протягом 12 (дванадцяти) узгоджених днів у вересні місяці 2022 року. Турнір відбудеться о 00:01 UTC 19 вересня 2022 р. та 30 вересня 2022 р. до 23:59 UTC</li>
                    <li>Сума призового фонду турніру складає 12 000 євро і буде розподілена серед 100 найкращих гравців, які мають найвищі бали у списку лідерів.</li>
                    <li>Мінімальна ставка, необхідна для участі у турнірі, становить 0,50 євро. чи еквівалент у місцевій валюті. Що більше балів, то вище гравець потрапляє до таблиці лідерів. Перші 100 гравців отримають призи.</li>
                    <li>Призовий фонд та мінімальна ставка для цієї кампанії встановлюються в євро і можуть бути піддані коливанням конвертації валюти.</li>
                    <li>Кожен спин впливає на загальний рахунок гравця, який відображається у таблиці лідерів. Загальний рахунок розраховується за допомогою очок, набраних у всіх раундах, зіграних в іграх, що беруть участь.</li>
                </ul>
            </div>

            <img src="/img/tournaments/harvest-of-bets/cat2.png" alt="Lion head" style="margin-left: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/harvest-of-bets/rept2.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>У турніру є кваліфікована механіка: накопичення загальної ставки. Турнірні очки нараховуються за кожну ставку (мінімальна ставка 0,50 євро)</li>
                    <li>Приклад. Якщо ви поставите 1 євро, неважливо, чи ви виграєте ні, ви все одно отримаєте 1 очко. Якщо у другому раунді гри ви поставите €2, то отримаєте 2 очки, а загальна кількість зароблених очок дорівнює 3. 1 € = 1 ОЧКО</li>
                    <li>У турніру є список кваліфікаційних ігор та призовий фонд (євро), який розподіляється між переможцями відповідно до правил турніру.</li>
                    <li>Якщо кілька учасників набирають однакову кількість балів, то вищу позицію в таблиці лідерів отримує учасник, який набрав перше місце.</li>
                    <li>Грошові призи будуть зараховані на рахунки гравців казино у вигляді коштів, які можна вивести після закінчення акції.</li>
                    <li>Evoplay залишає за собою право анулювати результати або не виплачувати призи у випадку, якщо всі або частина очок є результатом якоїсь очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), спричиненої машиною або людиною. помилка в будь-якій з ігор, що беруть участь.</li>
                    <li>Evoplay також залишає за собою право анулювати бали або не сплачувати приз, якщо, на думку, вся частина рахунку є результатом шахрайства або змови з іншими гравцями.</li>
                </ul>
            </div>
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 70%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/harvest-of-bets/tbg.jpg"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 985 €</td>
                    <tr><td>2</td><td>1 520 €</td>
                    <tr><td>3</td><td>1 200 €</td>
                    <tr><td>4</td><td>800 €</td>
                    <tr><td>5</td><td>720 €</td>
                    <tr><td>6</td><td>647 €</td>
                    <tr><td>7</td><td>480 €</td>
                    <tr><td>8</td><td>360 €</td>
                    <tr><td>9</td><td>264 €</td>
                    <tr><td>10-19</td><td>160 €</td>
                    <tr><td>20-49</td><td>40 €</td>
                    <tr><td>50-100</td><td>24 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 12 000 €</b></td></tr>
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
