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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Період мережевої кампанії:</div>
        <div class="tournament__cond-text">Від <strong>25.12.2023 00:00 UTC</strong> до <strong>01.01.2024 23:59 UTC</strong></div>


        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img src="/img/tournaments/winter-lucky-drops/pngegg.png" alt="grass" style="width: 280px; height: 320px; margin-bottom: -180px;">
        </div>


        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Гарячий період</span> - From 29.12.2023 15:00 UTC</div>

        <div class="tournament__cond-title">Умови та положення турніру 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <p>
                1. Беручи участь у мережі Lucky Drops, ви погоджуєтеся з положеннями та умовами, викладеними тут, і умовами, наданими
                 оператор.<br>
                 2. У екстрених випадках 3 Oaks Gaming залишає за собою право в будь-який час скасувати акцію Lucky Drops, включаючи платежі за
                 виграші, якщо це буде визнано необхідним.<br>
                 3. У кампанії Lucky Drops можуть брати участь лише раунди на справжні гроші.<br>
                 4. За участь у мережі Lucky Drops не стягується додаткова плата, окрім ціни за обертання.<br>
                 5. Правила мережі Lucky Drops можна будь-коли змінити.<br>
                 6. Несправність анулює всі виплати.
            </p>
        </div>


        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Гравець повинен приєднатися до будь-якої з ігор, що беруть участь, або взяти участь у ній.</li>
                     <li>Мінімальна ставка, необхідна для отримання LuckyDrop, становить <span style="color: red; font-weight: bold">0,50 євро</span> або еквівалентну суму в доступних валютах у грі-учаснику ( s).</li>
                     <li>Кожна кваліфікована ставка, зроблена на будь-яку з ігор-учасниць, може викликати випадковий приз із призового фонду протягом періоду акції.</li>
                     <li>Кожен гравець може виграти максимум 3 призи.</li>
                </ul>
            </div>
            <img src="/img/tournaments/winter-lucky-drops/snowman.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Механіка виграшу:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> пропонує гравцям можливість виграти грошові призи в будь-який час протягом періоду події. Призи надаються випадковим чином, і всі гравці мають однакові шанси на перемогу в кожному кваліфікаційному обертанні.
            </div>
        </div>
        <div class="tournament__cond-title">У іграх беруть участь:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Всі ігри</span></div>
        <div class="tournament__cond-title">Призовий фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Призовий фонд становить 30 000 євро для цієї кампанії (або еквівалент у валюті гравця), об’єднаних з кількох пулів за такою структурою:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">30,000 EUR</span> для (25.12.2023 00:00 UTC to 01.01.2023 23:59 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">30 000 EUR</span> <span style="color: red; font-weight: bold">Щотижня</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Кількість</th>
                     <th>Вартість премії</th>
                     <th>Сума</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>15</td><td>€ 200</td><td>€ 3,000</td></tr>
                    <tr><td>30</td><td>€ 100</td><td>€ 3,000</td></tr>
                    <tr><td>60</td><td>€ 75</td><td>€ 4,500</td></tr>
                    <tr><td>80</td><td>€ 60</td><td>€ 4,800</td></tr>
                    <tr><td>105</td><td>€ 50</td><td>€ 5,250</td></tr>
                    <tr><td>130</td><td>€ 30</td><td>€ 3,900</td></tr>
                    <tr><td>150</td><td>€ 20</td><td>€ 3,000</td></tr>
                    <tr><td>170</td><td>€ 15</td><td>€ 2,550</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
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
