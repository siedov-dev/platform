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
        <div class="tournament__cond-title">Онлайн науқан кезеңі:</div>
        <div class="tournament__cond-text"><strong>2023 жылдың 15-28 мамыры</strong> аралығында (екі күнді қосқанда)</div>
        <div class="tournament__cond-text"><strong>1-апта:</strong> 15.05.2023-21.05.2023</div>
        <div class="tournament__cond-text"><strong>2-апта:</strong> 22.05.2023-28.05.2023</div>

        <div style="display: flex; margin-top: -150px; justify-content: flex-end;">
            <img src="/img/tournaments/spring-lucky-drops/leaves.png" alt="grass" style="width: 280px; height: 320px; margin-bottom: -180px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Ыстық кезең</span> - ойыншының жүлде ұтып алу мүмкіндігін арттырады:</div>
        <div class="tournament__cond-text"><strong>1-апта:</strong> Басталуы <span style="color: red; font-weight: bold">18.05.2023 0:00 UTC</span > <strong>Апталық бақытты тамшылар</strong></div> науқанының соңына дейін
        <div class="tournament__cond-text"><strong>2-апта:</strong> басталады <span style="color: red; font-weight: bold">25.05.2023 0:00 UTC</span > <strong>Апталық бақытты тамшылар</strong></div> науқанының соңына дейін

        <div class="tournament__cond-title">Oaks Network турнирінің 3 ережелері мен шарттары:</div>
        <div class="turnament_cond-text">
            <ul class="turnament_list">
                <li>Турнирге қатысу арқылы сіз осы жерде көрсетілген Шарттар мен шарттармен, сондай-ақ оператор ұсынған шарттармен келісесіз.</li>
                <li>Төтенше жағдайда 3 Oaks Gaming кез келген уақытта Lucky Drops науқанынан бас тарту құқығын өзіне қалдырады, соның ішінде қажет болған жағдайда ұтыстар төлеу.</li>
                <li>Бақытты тамшылар науқаны үшін тек нақты ақша раундтары ғана жарамды.</li>
                <li>Айналдыру ақысынан басқа Network Lucky Drops бағдарламасына қатысу үшін қосымша ақы жоқ.</li>
                <li>Әр ойыншы көшбасшылар тақтасы үшін жеке турнир идентификаторын алады.</li>
                <li>Lucky Drops желі ережелерін кез келген уақытта өзгертуге болады.</li>
                <li>Ақаулық барлық төлемдерді жарамсыз етеді.</li>
            </ul>
        </div>


        <div class="tournament__cond-title">Біліктілік:</div>
        <div class="flex">
            <div class="turnament_cond-text">
                <ul class="turnament_list">
                    <li>Ойыншы қатысатын ойындардың кез келгеніне қосылуы немесе қатысуы керек.</li>
                    <li>LuckyDrop алу үшін талап етілетін ең аз ставка <span style="color: red; font-weight: bold">€0,2</span> немесе қатысушы ойын(тардағы) қолжетімді валюталардағы баламасы.</li>
                    <li>Қатысушы ойындардың кез келгеніне қойылған әрбір іріктеу ставкасы науқан кезеңінде жүлде қорынан кездейсоқ жүлдені тудыруы мүмкін.</li>
                    <li>Әр ойыншы ең көбі 3 жүлде ұтып алады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/spring-lucky-drops/sakura2.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Жеңіс механик:</div>
        <div class="turnament_cond-text">
            <div class="turnament_list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> Бақытты тамшылар - </span> ойыншыларға оқиға кезінде кез келген уақытта ақшалай сыйлықтар ұтып алу мүмкіндігін ұсынады.
                кезең. Жүлделер кездейсоқ түрде беріледі және барлық ойыншылардың жеңу мүмкіндігі бірдей
                әрбір біліктілік айналдыру.
            </div>
        </div>
        <div class="tournament__cond-title">Қатысатын ойындар:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Барлық ойындар</span></div>
        <div class="tournament__cond-title">Жүлде қоры:</div>

        <ul class="turnament_list">
            <div class="tournament__cond-text">Бұл науқанда келесі бағалау бойынша бірнеше активтен біріктірілген €60 000 (немесе ойыншының валютасындағы баламасы) жүлде қоры бар:</div>
            <div class="tournament__cond-text"> <span style="color: red; font-weight: bold">60 000 еуро</span> (15.05.2023 00:00 UTC to 28.05.2023 23:00 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">€30 000</span> бар болғаны <span style="color: red; font-weight: bold" >Апта сайын</span></li>
        </ul>

        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>40</td><td>€ 200</td><td>€ 8,000</td></tr>
                <tr><td>50</td><td>€ 100</td><td>€ 5,000</td></tr>
                <tr><td>70</td><td>€ 75</td><td>€ 5,250</td></tr>
                <tr><td>70</td><td>€ 40</td><td>€ 2,800</td></tr>
                <tr><td>90</td><td>€ 30</td><td>€ 2,700</td></tr>
                <tr><td>250</td><td>€ 25</td><td>€ 6,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 30,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/spring-lucky-drops/grass.png" alt="grass" style="width: 100%; margin-top: 0px;">
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
