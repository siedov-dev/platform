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

    .tournament__list ul li::marker {
        color: #e4b556;
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
    .article__text ul li::before {
        content: '';
        display: inline-block;
        width: .5rem;
        height: .5rem;
        border-radius: .5rem;
        background-color: #fec30c;
        margin-right: .5rem;
        top: 1px;
        position: relative;
    }
    .article__text ul{
        list-style: none;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">China CashFall проводится:</div>
        <div class="tournament__cond-text">С <strong>20.07.2023 9:00 UTC</strong> по <strong>24.07.2023 21:00 UTC</strong></div>
        <div class="tournament__cond-title">Условия турнира 3 Oaks Network:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Участвуя в турнире, вы <strong>соглашаетесь</strong> с Условиями и положениями, описанными здесь, а также с предоставленными оператором.</li>
                    <li>В случае чрезвычайных ситуаций <strong>3 Oaks Gaming</strong> имеет право в любое время отменить промоакцию , включая выплаты выигрышей, если это будет считаться необходимым.</li>
                    <li>Только раунды на <strong>реальные деньги</strong> могут принимать участие в акции .</li>
                    <li>Помимо <strong>стоимости одного спина</strong>, нет дополнительных затрат для участия в турнире.</li>
                    <li>Каждому игроку будет присвоен <strong>личный идентификатор турнира</strong> для списка лидеров.</li>
                    <li>Правила турнира могут быть изменены в любое время.</li>
                    <li>Неисправность аннулирует все выплаты.</li>
                </ul>
            </div>
        </div>
        <img src="/img/tournaments/china-cash-fall/chinese1.webp" alt="china house" style="width: 100%;">


        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Чтобы иметь право на получение приза, игрок должен выполнить следующие
                условия:
                <ol class="tournament__list">
                    <li><strong>Минимальная ставка</strong> составляет <span style="color: red; font-weight: bold">1,00 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">и</span>
                    <li><strong>Минимальное количество</strong> спинов - <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Все очки, набранные во время квалификационного этапа, сохраняются и учитываются для игрока после
                прохождения квалификации. Неквалифицированные игроки также отображаются в таблице лидеров, но
                строка для неквалифицированного игрока заштрихована серым цветом до их квалификации.
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/china-cash-fall/bamboo.webp" alt="bamboo" style="margin-left: 1rem; width: 200px; margin-top: 20px">
        </div>

        <div class="tournament__cond-title">Механика выигрыша:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА ПОБЕД:</span> если игрок сделал общую ставку на сумму <strong>1 EUR</strong> и выиграл <strong>120 EUR</strong>, игрок получит <strong>120 очков</strong>.
            </div>
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> ГОНКА МУЛЬТИПЛИКАТОРОВ:</span> если игрок сделал общую ставку на сумму <strong>2 EUR</strong> и выиграл <strong>120 EUR</strong>, игрок получит 120(выигрыш)÷2(ставка)= множитель 60x = <strong>60 очков</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Призовой фонд:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Общий призовой фонд турнира - прогрессивный, начиная от <span style="color: red; font-weight: bold">40 000 EUR</span> (или эквивалент валюты игрока).</div>
            <div class="tournament__cond-text">Прогрессивный призовой фонд растет во время турнира с вкладом процента ставок участников.</div>
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
                <thead>
                <tr>
                    <th>Место</th>
                    <th>Приз</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>10%</td><td>10%</td></tr>
                <tr><td>2</td><td>8%</td><td>8%</td></tr>
                <tr><td>3</td><td>6%</td><td>6%</td></tr>
                <tr><td>4</td><td>5%</td><td>5%</td></tr>
                <tr><td>5</td><td>4%</td><td>4%</td></tr>
                <tr><td>6</td><td>2.5%</td><td>2.5%</td></tr>
                <tr><td>7</td><td>1.6%</td><td>1.6%</td></tr>
                <tr><td>8</td><td>0.9%</td><td>0.9%</td></tr>
                <tr><td>9</td><td>0.8%</td><td>0.8%</td></tr>
                <tr><td>10-20</td><td>0.7%</td><td>7.7%</td></tr>
                <tr><td>21-30</td><td>0.6%</td><td>6%</td></tr>
                <tr><td>31-40</td><td>0.5%</td><td>5%</td></tr>
                <tr><td>41-50</td><td>0.4%</td><td>4%</td></tr>
                <tr><td>51-100</td><td>0.3%</td><td>15%</td></tr>
                <tr><td>101-150</td><td>0.2%</td><td>10%</td></tr>
                <tr><td>151-200</td><td>0.15%</td><td>7.5%</td></tr>
                <tr><td>201-250</td><td>0.12%</td><td>6%</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">100%</span></td>
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
