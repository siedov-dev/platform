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
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Период турнира:</div>
        <div class="tournament__cond-text">С 23 по 27 марта (включая оба дня)</div>

        <div class="tournament__cond-title">3 Oaks Network Tournament Условия и положения:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Участвуя в турнире, вы соглашаетесь с Условиями, изложенными здесь, а также условиями, предоставленными оператором.</li>
                <li>В экстренных случаях 3 Oaks Gaming оставляет за собой право отменить промоакцию турнира в любое время, включая выплату выигрышей, если это будет сочтено необходимым.</li>
                <li>Только квалифицированные игроки могут претендовать на приз.</li>
                <li>В турнире могут участвовать только раунды на реальные деньги.</li>
                <li>Помимо платы за вращение, за участие в турнире не взимается дополнительная плата.</li>
                <li>Каждый игрок получит личный турнирный идентификатор для таблицы лидеров.</li>
                <li>Если у двух или более игроков ничья, те, кто раньше добьется результатов, получат лучшие призы.</li>
                <li>Правила турнира могут быть изменены в любое время.</li>
                <li>Неисправность аннулирует все выплаты.</li>
            </ul>
        </div>

        <div class="flex" style="justify-content: flex-end">
            <img src="/img/tournaments/gold-nuggets/goldbarrel.png" alt="pumpkin" style="margin-top: 1rem; height: 170px; filter: drop-shadow(0px 0px 7px #fcc339)">
        </div>

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Чтобы пройти квалификацию и получить право претендовать на приз, игрок должен выполнить следующие условия:
                <ol class="tournament__list">
                    <li><strong>Минимальная ставка</strong> составляет <span style="color: red; font-weight: bold">0,20 евро,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">и</span>
                    <li><strong>Минимальное количество вращений</strong>: <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Все очки, набранные в ходе квалификационного этапа, сохраняются и засчитываются игроку после
                прохождения квалификации. Неквалифицированные игроки также отображаются в таблице лидеров, но
                линия для неквалифицированного игрока отмечена серой полосой до тех пор, пока он не прошел квалификацию.
            </div>
        </div>


        <div class="tournament__cond-title">Подсчет очков в турнире (механика победы):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ВЫИГРЫШНАЯ ГОНКА:</span> игрок поставил 1 евро на общую ставку и выиграл 120 евро, игрок получит 120 очков
            </div>
        </div>


        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="flex ai-fe mt-4" style="position: relative;">
            <img src="/img/tournaments/gold-nuggets/oakgold.png" style="transform: scale(-1, 1); position: absolute; top: 0; width: 100%">

            <table class="tournament__table" style="width: 100%; background-color: #0c0c30A0; z-index: 2; border-radius: 10px;">
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
                    <tr><td>4 - 5</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                    <tr><td>6 - 10</td><td>€ 750</td><td>€ 3,750</td></tr>
                    <tr><td>11 - 20</td><td>€ 500</td><td>€ 5,000</td></tr>
                    <tr><td>21 - 50</td><td>€ 375</td><td>€ 11,250</td></tr>
                    <tr><td>51 - 100</td><td>€ 175</td><td>€ 8,750</td></tr>
                    <tr><td>101 - 150</td><td>€ 100</td><td>€ 5,000</td></tr>
                    <tr><td>151 - 200</td><td>€ 45</td><td>€ 2,250</td></tr>
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
