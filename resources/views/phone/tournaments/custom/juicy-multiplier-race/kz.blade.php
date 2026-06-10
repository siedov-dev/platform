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
        <div class="tournament__cond-title">Juicy Multiplier Race турнирі өтеді:</div>
        <div class="tournament__cond-text"><strong>27.07.2023 9:00 UTC</strong> ден <strong>31.07.2023 21:00 UTC</strong> ге дейін</div>
        <div class="tournament__cond-title">3 Oaks Network Tournament Ережелері:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Турнирдегі қатысу арқылы, сіз мәліметтері бұл жерде көрсетілген Ережелермен және оператордың көрсеткіштерімен келісесіз.</li>
                    <li>Төменгілердегі, 3 Oaks Gaming, өтілетін промосыны барлық уақытта жоюға қаңдай болса, мынадай өтілетін беру арқылы табылған ақша айырбастауын білдіреді.</li>
                    <li>Лақтырмалы бойынша тек <strong>жалғыз ақша</strong> түріндегі дәулетті раундтар қатыса алады.</li>
                    <li><strong>Спин жасау құнынан басқа</strong>, турнирге қатысу үшін қосымша қаржы өткізу керек емес..</li>
                    <li>Әр бір ойыншы бақылаушы бойынша <strong>жеке турнир ID</strong> алады.</li>
                    <li>Турнир ережелеріді кез келген уақытта өзгерту мүмкін.</li>
                    <li>Техникалық ақау ақша төлемдерді қанағаттандырады.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center" style="margin-top: 0px;">
            <img src="/img/tournaments/juicy-multiplier-race/island.png" alt="palm" style="width: 300px; height: 200px; margin-bottom: 0px; margin-top: -10px;">
        </div>


        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text">Жеңілуі және тауарды талап ету үшін ойыншының қатысу құқығын қалыптастыру міндеттемесін орындау керек:
                <ol class="tournament__list">
                    <li><strong>Міндетті бет</strong> <span style="color: red; font-weight: bold">0.20 EUR</span>-ге тең,</li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">және</span>
                    <li><strong>Міндетті спин саны</strong> <span style="color: red; font-weight: bold">150</span>-ге тең.</li>
                </ol>
                Квалификациялық кезеңдегі барлық баллдар жиналып, ойыншы квалификациядан өттілгеннен кейін қатынасушыге беріледі. Квалификацияланған ойыншыларды да Лидердейтін бойынша көрсетіледі, бірақ Неквалификацияланған ойыншы Қара шегінен сызықпайтын сызықтанады.
            </div>
        </div>
        <div class="flex jc-center" style="margin-top: 0px;">
            <img src="/img/tournaments/juicy-multiplier-race/cocktail2.png" alt="cocktail" style="margin-left: 1rem; width: 240px; margin-top: 20px">
        </div>

        <div class="tournament__cond-title">Жеңу құралдары:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> МУЛЬТИПЛИКАТОРЛАР ГОНКАСЫ:</span> өткізгіш бет қойған жүктеме <strong>EUR 2</strong> жинаған жағдайда <strong>EUR 120</strong> жеңген болса, ойыншы 120(жеңген)÷2(бет) = 60x множител = <strong>60 балл</strong> алады.
            </div>
        </div>

        <div class="tournament__cond-title">Приз топтары:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Турнирдің жалпы приз құны <span style="color: red; font-weight: bold">EUR 50,000</span> (немесе ойыншының валютасының эквиваленті) болып табылады.</div>
            <div class="tournament__cond-text">Приздік тарату кестесі <span style="color: red; font-weight: bold">EUR</span> бойынша:</div>
        </ul>


        <div class="flex ai-center jc-center mt-2">
            <div style='width: 100%; background-image: url("/img/tournaments/juicy-multiplier-race/beach.png"); background-position: center; background-size: cover;'>

                <table class="tournament__table"
                       style='box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D3160; width: 100%; text-shadow: #0B0D31 2px 2px 2px; font-weight: 800;'>
                    <thead>
                    <tr>
                        <th>Орын</th>
                        <th>Приз</th>
                        <th>Сома</th>
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
