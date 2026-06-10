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
        color: #8888af;
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Турнирный период:</div>
        <div class="tournament__cond-text">С 4 по 10 августа 2022 года (включая оба дня)</div>

    <div class="tournament__cond-title">3 Oaks Network Tournament Условия и положения:</div>
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

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="tournament__cond-text"> Чтобы быть квалифицированным и иметь право претендовать на приз, игрок должен выполнить следующие
            условия:
            <ol class="tournament__list">
                <li><strong>Минимальная ставка</strong> <span style="color: red; font-weight: bold">1.00 EUR,</span></li>
                <span style="color: white; text-transform: uppercase; font-weight: bold">И</span>
                <li><strong>Минимальное количество</strong> спинов <span style="color: red; font-weight: bold">150</span>.</li>
            </ol>
            Все очки, набранные на квалификационном этапе, сохраняются и учитываются для игрока.
            после прохождения квалификации. Неквалифицированные игроки также отображаются в таблице лидеров.
            но раздетые и в сером, пока они не квалифицированы.
        </div>

        <div class="tournament__cond-title">Подсчет очков в турнире (Победная механика):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА МНОЖИТЕЛЕЙ:</span> игрок поставил 2 евро на общую ставку и выиграл 120 евро, игрок получит
                120(выигрыш)/2(ставка)= множитель x60 - 60 очков
            </div>
        </div>


        <div class="tournament__cond-title">Прогрессивный призовой фонд:</div>
        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/gain-and-cocktails/blue_cocktail.png" alt="Gambling man" class="tournament__image" style="margin-right: 2rem;">

            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Игрок</th>
                    <th>Приз</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>10%</td><td>10%</td></tr>
                    <tr><td>2nd</td><td>8%</td><td>8%</td></tr>
                    <tr><td>3rd</td><td>6%</td><td>6%</td></tr>
                    <tr><td>4th</td><td>5%</td><td>5%</td></tr>
                    <tr><td>5th</td><td>4%</td><td>4%</td></tr>
                    <tr><td>6th</td><td>2.5%</td><td>2.5%</td></tr>
                    <tr><td>7th</td><td>1.6%</td><td>1.6%</td></tr>
                    <tr><td>8th</td><td>0.9%</td><td>0.9%</td></tr>
                    <tr><td>9th</td><td>0.8%</td><td>0.8%</td></tr>
                    <tr><td>10th - 20th</td><td>0.7%</td><td>7.7%</td></tr>
                    <tr><td>21th - 30th</td><td>0.6%</td><td>6%</td></tr>
                    <tr><td>31th - 40th</td><td>0.5%</td><td>5%</td></tr>
                    <tr><td>41st - 50th</td><td>0.4%</td><td>4%</td></tr>
                    <tr><td>51st - 100th</td><td>0.3%</td><td>15%</td></tr>
                    <tr><td>101st - 150th</td><td>0.2%</td><td>10%</td></tr>
                    <tr><td>151st - 200th</td><td>0.15%</td><td>7.5%</td></tr>
                    <tr><td>201st - 250th</td><td>0.12%</td><td>6%</td></tr>
                </tbody>
            </table>

            <img src="/img/tournaments/gain-and-cocktails/red_cocktail.png" alt="Gambling man" class="tournament__image" style="margin-left: 2rem;">
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
