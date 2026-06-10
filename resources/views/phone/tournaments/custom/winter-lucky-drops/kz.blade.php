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
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Желілік науқан кезеңі:</div>
        <div class="tournament__cond-text">бастап <strong>25.12.2023 00:00 UTC</strong> дейін <strong>01.01.2024 23:59 UTC</strong></div>


        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Ыстық кезең</span> - From 29.12.2023 15:00 UTC</div>

        <div class="tournament__cond-title">3 Oaks Network турнирінің ережелері мен шарттары:</div>
        <div class="tournament__cond-text">
            <p>
                1. Network Lucky Drops бағдарламасына қатысу арқылы сіз осы жерде баяндалған және ұсынған шарттармен келісесіз.
                 оператор.<br>
                 2. Төтенше жағдайларда 3 Oaks Gaming кез келген уақытта Lucky Drops науқанынан бас тартуға құқылы, соның ішінде
                 қажет болған жағдайда ұтыстар.<br>
                 3. «Бақытты тамшылар» науқанына тек нақты ақша раундтары қатыса алады.<br>
                 4. Айналдыру бағасынан басқа желідегі бақытты тамшыларға кіру үшін қосымша ақы жоқ.<br>
                 5. Желілік Lucky Drops ережелерін кез келген уақытта өзгертуге болады.<br>
                 6. Ақаулық барлық төлемдерді жояды.
            </p>
        </div>


        <div class="tournament__cond-title">Біліктілік:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Ойыншы қатысушы ойындардың кез келгеніне қосылуы немесе қосылуы керек.</li>
                     <li>LuckyDrop алу үшін талап етілетін ең аз ставка <span style="color: red; font-weight: bold">EUR 0,50</span> немесе қатысушы ойында қолжетімді валюталардағы баламалы сома( s).</li>
                     <li>Қатысушы ойындардың кез келгеніне қойылған әрбір іріктеу ставкасы науқан кезеңінде жүлде қорынан кездейсоқ жүлдені тудыруы мүмкін.</li>
                     <li>Әр ойыншы ең көбі 3 жүлде ұтып алады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/winter-lucky-drops/snowman.png" alt="girl" style="margin-left: 1rem; width: 300px;">
        </div>

        <div class="tournament__cond-title">Жеңіс механикасы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> ойыншыларға оқиға кезеңінде кез келген уақытта ақшалай сыйлықтар ұтып алу мүмкіндігін ұсынады. Жүлделер кездейсоқ түрде беріледі және барлық ойыншылардың әрбір біліктілік айналымында ұту мүмкіндігі бірдей.
            </div>
        </div>
        <div class="tournament__cond-title">Қатысатын ойындар:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Барлық ойындар</span></div>
        <div class="tournament__cond-title">Жүлде қоры:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Жүлде қоры осы науқан үшін (немесе ойыншының валюталық баламасы) келесі құрылым бойынша бірнеше пулдардан біріктірілген 30 000 еуроны құрайды:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">30,000 EUR</span> үшін (25.12.2023 00:00 UTC to 01.01.2023 23:59 UTC)</div>
            <li class="tournament__cond-text"><span style="color: red; font-weight: bold">30 000 EUR</span> үшін барлығында <span style="color: red; font-weight: bold">Әр апта сайын</span></li>
        </ul>


        <div class="flex ai-center">

            <table class="tournament__table"
                   style="background: url('/img/tournaments/spring-lucky-drops/1.png') no-repeat center; background-size: cover; box-shadow: #30ff00 0 0 7px; z-index: 2;">
                <thead>
                <tr>
                    <th>Саны</th>
                    <th>Жүлде құны</th>
                    <th>Қосынды</th>
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
