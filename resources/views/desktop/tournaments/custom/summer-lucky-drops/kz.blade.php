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

        <div class="tournament__cond-title">Lucky Drops аясында:</div>
        <div class="tournament__cond-text mb-1"><strong>2023 жылдың 24-27 тамызы</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="seaguls" style="width: 320px; height: 320px; margin-bottom: -160px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Hot Period</span> - ойыншының жеңімпаздық шансын арттырады.:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">26.08.2023 0:00 UTC</span> бастап <strong>Weekend Lucky Drops</strong> науқанының соңына дейін</div>

        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнирде қатысу арқылы сіз осы жерде көрсетілген Шарттарды және оператордың көрсеткіштерін қабылдауға келісесіз.</li>
                <li>Ау emergency халатында 3 Oaks Gaming Lucky Drops промосын уақытша, мәні анықталмаса, ойыншының шектеулерін жасауға құқылылықты тартады.</li>
                <li>Тек жаңа ақша ойындары кезінде қатысу мүмкін.</li>
                <li>Спин жасау құнынан басқа қосымша төлем жоқ.</li>
                <li>Әр одан кем емес қатынаушы бағдарламалық ойындықты бақылау үшін шақырылған адамдық турнир ID -сы бар.</li>
                <li>Network Lucky Drops ережелері өзгерту мүмкін.</li>
                <li>Ақша алмасу ақшалайтындайды.</li>
            </ul>
        </div>
        <div class="tournament__cond-title">Кілтіліктер:</div>
        <div class="flex">
            <div style="">
                <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacation" style="margin-right: 1rem; width: 250px;">
            </div>
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Ойыншы катылу немесе қатынаушы ойындардан біріне қосылуы керек.</li>
                    <li>ЛакиДроп алу үшін міндетті беттегі минималды ойын құны <span style="color: red; font-weight: bold">0.50 EUR</span> немесе қатынаушы ойыннан болжам болатын қолданылатын валюталардағы екі жеңілді ойыннан көлеміне тең болуы керек.</li>
                    <li>Қатынаушы ойыннан алтықыш аймақтың барлық қатынаулары растаушы беттегі барлық ойында қосымша жеңімпаздық мерейтойынды мүмкіндікке ие болады.</li>
                    <li>Әр бір ойыншы әрқайсысына <span style="color: red; font-weight: bold">3 жеңімпаздық</span> ала алады.</li>
                </ul>
            </div>
        </div>
        <div class="tournament__cond-title">Жеңу Механикасы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops</span> - ойыншыларға осы оқиғалар кезінде кеш жеңілдіктерді жеңу мүмкіндігін береді. Жеңілдіктер тасымалдаушылардан ала отырып, қатынаушы ойындарда өзара теңестікке ие болады.
            </div>
        </div>
        <div class="tournament__cond-title">Ойындар қатынаушы:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Барлық ойындар</span></div>
        <div class="tournament__cond-title">Жеңілдік пулы:</div>
        <ul class="tournament__list">
            <div class="tournament__cond-text">Жүлде қоры келесі құрылым бойынша бірнеше пулдан біріктірілген осы науқанға (немесе ойыншының валюталық баламасы) 15`000 евроны құрайды:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15`000 EUR</span> 24.08.2023 00:00 UTC және 27.08.2023 23 :00 UTC</div>
        </ul>

        <div class="flex ai-center">

        <img src="/img/tournaments/summer-lucky-drops/beach_luggage.png" alt="beach" style="margin-right: 1rem; width: 450px;">

        <table class="tournament__table"
               style="box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-left: -100px;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>15</td><td>€ 100</td><td>€ 1,500</td></tr>
                <tr><td>30</td><td>€ 60</td><td>€ 1,800</td></tr>
                <tr><td>45</td><td>€ 50</td><td>€ 2,250</td></tr>
                <tr><td>60</td><td>€ 40</td><td>€ 2,400</td></tr>
                <tr><td>80</td><td>€ 30</td><td>€ 2,400</td></tr>
                <tr><td>120</td><td>€ 20</td><td>€ 2,400</td></tr>
                <tr><td>150</td><td>€ 15</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15,000</span></td>
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
