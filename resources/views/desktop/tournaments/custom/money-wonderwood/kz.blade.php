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
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Money Wonderwood жүргізіледі:</div>
        <div class="tournament__cond-text"> <strong>24.08.2023</strong> күнінен <strong>30.08.2023</strong> күніне дейін</div>

        <div class="tournament__cond-title">3 Oaks Network Турнир Условиясы:</div>
        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/bats.png" alt="bats" style="width: 280px; height: 220px; margin-top: 10px;">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>Турнирде қатысу арқылы сіз бұл жерде көрсетілген Шарттар мен Шарттарға келісесіз.</li>
                    <li>Төтенше жағдайда, <strong>3 Oaks</strong> оператордың міндеттерімен бірден болу кезінде акцияны жоюға, барлық жеңілістерді алуға қажеттілігі болса, бір уақытта жеңіліс бойынша төлемдерді де қажеттілігі болса.</li>
                    <li>Кампанияға тек <strong>нақты ақшалар</strong> айнымалылары батыр.</li>
                    <li><strong>Бір спинні жасау құнын кейбір заттың бір спиніні жасау құнын</strong> кейбір затты жасау құнындан бас тартып, турнирге енгізу үшін қосымша құны жоқ.</li>
                    <li>Әр бір ойыншы атаулы турнир беті үшін <strong>жеке турнир ID</strong> алады.</li>
                    <li>Турнир талаптарын кез келген уақытта өзгерту мүмкін.</li>
                    <li>Мүмкін болатын барлық төмендетулер жеңілдіктерді болдырмауға ауыстырады.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Турнир:</strong> жеңіліс алуға құқық пайдалану үшін ойыншының қатарлы аймағын аяқтау керек және төленімді:
                <ol class="tournament__list">
                    <li><strong>Минималды бет</strong> <span style="color: red; font-weight: bold">0,20 EUR</span> болуы керек,</li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">және</span>
                    <li><strong>Минималды спин саны</strong> <span style="color: red; font-weight: bold">150</span> болуы керек.</li>
                </ol>
                Квалификация кезінде жиналған барлық баллдар игроктың квалификацияны алдында сақталып, есепке алынады. Квалификациясы жоқ ойыншылар да Лидербордда көрсетіледі, бірақ квалификациясы жоқ ойыншылар Қара таспаннан жапырып, сіз квалификация алған мезгілінше.</div>
            <div>
                <img src="/img/tournaments/money-wonderwood/tree1.png" alt="leaves" style="width: 230px; margin-top: -60px; margin-left: -10px">
            </div>
        </div>

        <div class="tournament__cond-title">Жеңімек Механикасы:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> МУЛЬТИПЛЕР ЖАРЫСЫ:</span>, егер ойыншы <strong>€2</strong> жалпы ставка жасап, жеңіп алса <strong >€120 </strong>, ойыншы 120(жеңіс)÷2(ставка) = 60x мультипликатор = <strong>60 ұпай</strong> алады.
            </div>
        </div>

        <div class="tournament__cond-title">Премия Фонды:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Прогрессивті бағасы <span class="hot">€40 000</span></div>
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
                <thead>
                <tr>
                    <th>Орын</th>
                    <th>Жүлде</th>
                    <th>Қосынды</th>
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

        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/owl_tree.png" alt="owl on branch" style="width: 70%; margin-top: -0px;">
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
