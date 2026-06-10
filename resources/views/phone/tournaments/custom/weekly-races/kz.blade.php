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
    .article__text strong.gold{
        color: #d7b96b;
    }
    .tournament__list strong{
        color: #d7b96b;
    }
    .article__text b.red{
        color: red;
    }
    @keyframes gold-shine {
        from {filter: drop-shadow(0 0 0px goldenrod);}
        to {filter: drop-shadow(0 0 10px goldenrod);}
    }
    .shine{
        animation: gold-shine;
        animation-duration: 2s;
        filter: drop-shadow(0 0 10px goldenrod);
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-text">Турнир <strong class="gold">Weekly Races 40k</strong> <b class="red">2023 жылы 15 маусымдан 0:01 бұрын 2023 жылы 25 маусым 23:59 ге дейін</b> жүргізіледі және бірнеше фазаларға бөлінеді.</div>
        <div class="tournament__cond-text"><strong>Фаза 1:</strong> <b class="red">15.06.2023-18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>Фаза 2:</strong> <b class="red">22.06.2023-25.06.2023</b></div>

        <div style="display: flex; margin-top: -80px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/weekly-races/cup.png" alt="grass" style="height: 120px; margin-bottom: -40px;">
        </div>

        <div class="tournament__cond-title">Қатысу шарттары:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнир <strong>Weekly Races 40k</strong> тек жасырақ дегенге ойын режимінде қол жетімді.</li>
                <li>Қатысу үшін минималды бет қойу <b class="red">0,20 евро</b> немесе басқа валюта эквивалентінде болуы міндетті.</li>
                <li>Кездейсоқ ойнаушының <b class="red">Жалпы есептеу</b>-де көрсетіледі, ол <strong>Лидер тақтасында</strong> көрсетіледі. Жалпы есептеу қатары барлық турнирдегі ойындарда жиналған баллардың қосымшасымен есептеледі.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Марапаттар:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Ақша марапаттары аяқталғанда, олар келесі жұмыс күндерінен бастап салынады (<b class="red">72 сағат (3 жұмыс күні)</b>).</li>
                    <li>Марапат фондының өлшемі және осы әкімшілікке арналған минималды бет қойу евродан (EUR) белгіленген және валютаның ауысуларының әсеріне салынуы мүмкін.</li>
                    <li>Валюталық ауысу автоматты түрде бастап келеді және турнир басталуына (<b class="red">72 сағат</b>) дейін көлемдер дәл емес.</li>
                    <li>Егер толығымен немесе қатарындағы нәтижелер очевидті қате, қателер немесе техникалық ақаулар (ойындағы дұрыстардың ақысында қателіктерді және жетістіктерді көрсету де) қатарына дайындар болса, біз нәтижелерді бас тартуға, марапаттарды салуды қамтамасыз етуге құқылымымызды қорытындылар.</li>
                    <li>Біз де бас тартуға, марапаттарды салуды қамтамасыз етуге құқылымымызды қорытындылар, егер біздің көзқарасымызда толығымен немесе қатарындағы нәтижелер залалдық пен басқа ойыншылармен талқыласу пендік болса.</li>
                    <li>Біз кампанияны қажет кезде өзгерту немесе тастауға құқылымымызды қорытындылар.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center mt-1">
            <img src="/img/tournaments/weekly-races/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; height: 140px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Жеңу механикасы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <strong>Бет гонкасы (БАРЛЫҚ БЕТ КӨЛІКТЕРІ)</strong>: Баллар турнир кезінде ойындардың барлық раундтарында орналастырылады, әрбір <b class="red" style="white-space: nowrap;">1 евро бет 100 баллга тең</b>. Мысалы, 5 евро бет 500 балл жинауды құрайды.
            </div>
        </div>

        <div class="tournament__cond-title">Марапат фонды:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>40 000 евро</strong>, <b class="red">20 000 евро</b> барынша бөлуі турнирлер:</div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Орын</th>
                    <th>Марапат</th>
                    <th>Сомасы</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                <tr><td>2</td><td>€ 2,000</td><td>€ 2,000</td></tr>
                <tr><td>3</td><td>€ 1,000</td><td>€ 1,000</td></tr>
                <tr><td>4 - 10</td><td>€ 400</td><td>€ 2,800</td></tr>
                <tr><td>11 - 20</td><td>€ 150</td><td>€ 1,500</td></tr>
                <tr><td>21 - 50</td><td>€ 100</td><td>€ 3,000</td></tr>
                <tr><td>51 - 100</td><td>€ 50</td><td>€ 2,500</td></tr>
                <tr><td>101 - 150</td><td>€ 20</td><td>€ 1,000</td></tr>
                <tr><td>151 - 270</td><td>€ 10</td><td>€ 1,200</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 20,000</span></td>
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
