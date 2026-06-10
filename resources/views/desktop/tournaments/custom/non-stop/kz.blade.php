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
        white-space: nowrap;
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
        <div class="tournament__cond-text"><strong class="gold">Non-stop Drop маусымы 150 000 евро</strong> турнирі <b class="red">01.06.2023 0:01 UTC</b> мен <b class="red">30.06.2023 UTC 23:59</b> аралығында өтеді және апта сайынғы кезеңдерге бөлінеді.</div>
        <div class="tournament__cond-text"><strong>1-кезең:</strong> <b class="red">01.06.2023 - 11.06.2023</b></div>
        <div class="tournament__cond-text"><strong>2-кезең:</strong> <b class="red">12.06.2023 - 18.06.2023</b></div>
        <div class="tournament__cond-text"><strong>3-кезең:</strong> <b class="red">19.06.2023 - 25.06.2023</b></div>
        <div class="tournament__cond-text"><strong>4-кезең:</strong> <b class="red">26.06.2023 - 30.06.2023</b></div>
        <div style="display: flex; margin-top: -80px; justify-content: flex-end;">
            <img class="shine" src="/img/tournaments/non-stop/cup.png" alt="grass" style="height: 230px; margin-bottom: -120px; margin-right: 50px;">
        </div>
        <div class="tournament__cond-text" style="padding-right: 300px;">
            <b class="red">Cash Blast</b> мүмкіндігі ойыншыларға оқиға кезеңінде кез келген уақытта ақшалай сыйлықтар ұтып алу мүмкіндігін ұсынады. Жүлделер кездейсоқ түрде ұсынылады және әрбір айналдырудан кейін әр ойыншының ұту мүмкіндігі бірдей.
        </div>

        <div class="tournament__cond-title">Жарыс:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li><strong>Non-Stop Drop 1 000 000 EUR</strong> тек нақты ойын режимінде қолжетімді.</li>
                <li>Қатысу үшін ең аз ставка <b class="red">0,20 EUR</b> немесе басқа валютадағы баламасы.</li>
                <li>Қатысушы ойын автоматтарындағы кез келген айналдыру кездейсоқ <b class="red">Cash Blast</b> іске қосуы мүмкін.</li>
                <li>Кез келген жеке ойыншы жарнамалық кезеңде бірнеше <b class="red">Cash Blast</b> жүлделерін ұтып ала алады.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Жүлде:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li><strong>Ақшалай сыйлықтар</strong> ұтқаннан кейін бірден ойыншылардың шоттарына автоматты түрде түседі. Берілгеннен кейін жүлде жүлде қорынан алынып тасталады. Қалған жүлделердің сомасы нақты уақыт режимінде <b class="red">Cash Blast</b> виджетінде немесе <b class="red">Жүлделер</b> бөлімінде көрсетіледі.</li>
                    <li>Осы науқанның жүлде қоры мен ең төменгі ұсынысы <b class="red">EUR</b> деңгейінде белгіленген және айырбас бағамының өзгеруіне ұшырауы мүмкін.</li>
                    <li>Валюта бағамдары турнир басталардан <b class="red">72 сағат</b> бұрын жүйеміз арқылы автоматты түрде орнатылады және бүкіл турнир кезеңінде өзгеріссіз қалады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/non-stop/nonstop_tag.png" alt="nonstop_tag" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Жалпы ережелер:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Playson желілік науқанды кез келген уақытта, соның ішінде кез келген байланысты шарттар мен шарттарды өзгертуге, тоқтата тұруға немесе одан бас тартуға құқылы.</li>
                    <li>Нәтижелердің барлығы немесе бір бөлігі анық қатенің, қателіктің немесе техникалық ақаудың (ойындағы қате төлемдерді қоса алғанда) машина мен адам қателігінің нәтижесі болса, ұпайларды жою немесе жүлдені төлемеу құқығын өзімізде қалдырамыз. қатысатын ойындардың кез келгеніне қатысты.</li>
                    <li>Сондай-ақ, егер біздің ойымызша, нәтижелердің барлығы немесе бір бөлігі алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесі болса, ұпайларды жою немесе жүлдені төлемеу құқығын өзімізде қалдырамыз.</li>
                    <li>Біз кез келген уақытта науқанды өзгерту немесе тоқтату құқығын сақтаймыз.</li>
                </ul>
            </div>
            <img src="/img/tournaments/non-stop/playson.png" alt="playson" style="margin-left: 2rem; width: 300px; align-self: center;">
        </div>

        <div class="tournament__cond-title">Жүлде кестесі:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text"><strong>айына 150 000 еуро</strong>, <b class="red">аптасына 37 500 евро</b></div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #ff0000 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Жүлде</th>
                    <th>Нөмір</th>
                    <th>EUR сомасы</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>€ 5,000</td><td>1</td><td>€ 5,000</td></tr>
                <tr><td>€ 2,500</td><td>2</td><td>€ 5,000</td></tr>
                <tr><td>€ 1,000</td><td>4</td><td>€ 4,000</td></tr>
                <tr><td>€ 500</td><td>10</td><td>€ 5,000</td></tr>
                <tr><td>€ 250</td><td>20</td><td>€ 5,000</td></tr>
                <tr><td>€ 100</td><td>40</td><td>€ 4,000</td></tr>
                <tr><td>€ 75</td><td>50</td><td>€ 3,750</td></tr>
                <tr><td>€ 50</td><td>60</td><td>€ 3,000</td></tr>
                <tr><td>€ 25</td><td>70</td><td>€ 1,750</td></tr>
                <tr><td>€ 10</td><td>100</td><td>€ 1,000</td></tr>
                <tr>
                    <td><strong>Барлығы</strong></td>
                    <td><b class="red">357</b></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 37,500</span></td>
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
