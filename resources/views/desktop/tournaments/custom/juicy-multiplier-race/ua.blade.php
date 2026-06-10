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
        white-space: nowrap;
        color: #e4b556;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Проходить турнір Juicy Multiplier Race:</div>
        <div class="tournament__cond-text">Від <strong>27.07.2023 9:00 UTC</strong> до <strong>31.07.2023 21:00 UTC</strong></div>
        <div class="tournament__cond-title">Умови та положення турніру 3 Oaks Network:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Беручи участь у турнірі, ви <strong>погоджуєтесь</strong> з умовами, викладеними тут, а також з умовами, наданими оператором.</li>
                    <li>У випадку надзвичайної ситуації <strong>3 Oaks Gaming</strong> залишає за собою право в будь-який час скасувати акцію , включаючи виплати виграшів, якщо це буде вважатись необхідним.</li>
                    <li>У акції Lможуть брати участь лише раунди з <strong>реальними грошима</strong>.</li>
                    <li>Крім <strong>вартості одного спіна</strong>, немає додаткових витрат для вступу до турніру.</li>
                    <li>Кожному гравцеві буде надано <strong>особистий ідентифікатор турніру</strong> для таблиці лідерів.</li>
                    <li>Правила турніру можуть бути змінені в будь-який час.</li>
                    <li>Помилка анулює всі виплати.</li>
                </ul>
            </div>
            <img src="/img/tournaments/juicy-multiplier-race/palm1.png" alt="palm" style="width: 320px; height: 380px; margin-bottom: 0px; margin-top: -100px;">
        </div>


        <div class="tournament__cond-title">Кваліфікація:</div>
        <div class="flex">
            <div style="margin-top: 0px;">
                <img src="/img/tournaments/juicy-multiplier-race/cocktail2.png" alt="cocktail" style="margin-left: 1rem; width: 240px; margin-top: 20px">
            </div>
            <div class="tournament__cond-text"> Щоб мати право на отримання призу, гравець повинен виконати наступні
                умови:
                <ol class="tournament__list">
                    <li><strong>Мінімальна ставка</strong> становить <span style="color: red; font-weight: bold">0.20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">та</span>
                    <li><strong>Мінімальна кількість</strong> спінів становить <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Всі набрані очки під час кваліфікаційного етапу зберігаються та підраховуються для гравця після
                проходження кваліфікації. Некваліфіковані гравці також відображаються у таблиці лідерів, але
                рядок для некваліфікованого гравця смугастий сірим до моменту їх кваліфікації.
            </div>
        </div>

        <div class="tournament__cond-title">Механіка перемоги:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА МУЛЬТИПЛИКАТОРІВ:</span> якщо гравець зробив загальну ставку в розмірі <strong>EUR 2</strong> і виграв <strong>EUR 120</strong>, гравець отримає 120(виграш)÷2(ставка) = множник 60x = <strong>60 очок</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Призовий фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Загальний призовий фонд турніру становить <span style="color: red; font-weight: bold">EUR 50,000</span> (або еквівалент валюти гравця).</div>
            <div class="tournament__cond-text">Таблиця розподілу призів в <span style="color: red; font-weight: bold">EUR</span>:</div>
        </ul>


        <div class="flex ai-center jc-center mt-2">
            <div style='width: 80%; background-image: url("/img/tournaments/juicy-multiplier-race/beach.png"); background-position: center; background-size: cover;'>

                <table class="tournament__table"
                       style='box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D3160; width: 100%; text-shadow: #0B0D31 2px 2px 2px; font-weight: 800;'>
                    <thead>
                    <tr>
                        <th>Місце</th>
                        <th>Приз</th>
                        <th>Сума</th>
                    </tr>
                    </thead>
                <tbody>
                <tr><td>1</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                <tr><td>2</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                <tr><td>3</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                <tr><td>4 - 5</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                <tr><td>6 - 10</td><td>€ 750</td><td>€ 3,750</td></tr>
                <tr><td>11 - 20</td><td>€ 500</td><td>€ 5,000</td></tr>
                <tr><td>21 - 50</td><td>€ 375</td><td>€ 11,250</td></tr>
                <tr><td>51 - 100</td><td>€ 175</td><td>€ 8,750</td></tr>
                <tr><td>101 - 150</td><td>€ 100</td><td>€ 5,000</td></tr>
                <tr><td>151 - 200</td><td>€ 45</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
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
