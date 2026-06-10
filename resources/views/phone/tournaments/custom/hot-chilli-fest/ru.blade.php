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
    ol.tournament__list  li {
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
        font-size: 0.875rem;
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div style="position: relative; height: 10rem">
            <div style="position: absolute; top: -1rem; z-index: 2;">
                <div class="tournament__cond-title">Турнирный период:</div>
                <div style="font-size: 0.875rem; font-weight: 600; margin-right: 100px; color: #FFFFFF; text-shadow: #cf2d17 0 0 4px; margin-top: 0.5rem;">С 14 по 20 октября 2022 года (включая оба дня)</div>
            </div>
            <img src="/img/tournaments/hot-chilli-fest/flames.png" alt="girl" class="mt-1" style="width: 100%; position: absolute; bottom: 0;">
        </div>


    <div class="tournament__cond-title" style="margin-top: 0px;">3 Oaks Network Tournament Условия и положения:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Участвуя в турнире, вы соглашаетесь с Условиями, изложенными здесь, а также условиями, предоставленными
                    оператор.</li>
                <li>3 Oaks имеет право отменить турнир на неопределенный срок без каких-либо выплат в соответствии с
                    таблица лидеров в экстренных случаях.</li>
                <li>Только квалифицированные игроки могут претендовать на приз (пожалуйста, проверьте раздел «Информация о кампании»).</li>
                <li>В турнире могут участвовать только раунды на реальные деньги.</li>
                <li>Помимо цены за спин, участие в турнире не требует дополнительных затрат.</li>
                <li>Каждый игрок получит личный идентификатор турнира для таблицы лидеров.</li>
                <li>Если у двух или более игроков ничья, те, кто добьется результатов раньше, получат лучшие призы.</li>
                <li>Правила турнира могут быть изменены в любое время.</li>
                <li>Неисправность аннулирует все выплаты.</li>
            </ul>
        </div>

        <img src="/img/tournaments/hot-chilli-fest/pepers.png" alt="girl" class="mt-1" style="width: 100%">

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Чтобы быть квалифицированным и иметь право претендовать на приз, игрок должен выполнить следующие
                условия:
                <ol class="tournament__list">
                    <li><strong>Минимальная ставка</strong> <span style="color: red; font-weight: bold">0.20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">И</span>
                    <li><strong>Минимальное количество</strong> спинов <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Все очки, набранные на квалификационном этапе, сохраняются и учитываются для игрока.
                после прохождения квалификации. Неквалифицированные игроки также отображаются в таблице лидеров.
                но раздетые и в сером, пока они не квалифицированы.
            </div>
        </div>


        <div class="tournament__cond-title">Подсчет очков в турнире (Победная механика):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА ВЫИГРЫШЕЙ:</span> игрок поставил 1 евро общей ставкой и выиграл 120 евро, игрок получит 120 очков
            </div>
        </div>


        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="flex ai-center">

            <table class="tournament__table" style="background: url('/img/tournaments/hot-chilli-fest/table_bg.png') no-repeat center; background-size: cover; box-shadow: #65050a 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Игрок</th>
                    <th>Приз</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                    <tr><td>2nd</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                    <tr><td>3rd</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                    <tr><td>4th - 5th</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                    <tr><td>6th - 10th</td><td>€ 750</td><td>€ 3,750</td></tr>
                    <tr><td>11th - 20th</td><td>€ 500</td><td>€ 5,000</td></tr>
                    <tr><td>21th - 50th</td><td>€ 375</td><td>€ 11,250</td></tr>
                    <tr><td>51st - 100th</td><td>€ 175</td><td>€ 8,750</td></tr>
                    <tr><td>101st - 150th</td><td>€ 100</td><td>€ 5,000</td></tr>
                    <tr><td>151st - 200th</td><td>€ 45</td><td>€ 2,250</td></tr>
                    <tr><td colspan="2"></td><td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td></tr>
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
