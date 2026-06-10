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
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: white;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: yellow;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
    .tournament__image {
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__title-text{
        margin-top: 1rem;
        /*color: #8184BC;*/
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
    }
    .yellow-text{
        color: #fcc339;
        text-transform: uppercase;
    }
    .ai-fe{
        align-items: flex-end;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__title-text mt-2">
            <p>
                Біздің екінші онлайн науқанымыз ойыншыларға <span class="yellow-text">€70 000 CASH</span> 1 000 жүлде береді.
                Кездейсоқ сәттілік дөңгелегін айналдырыңыз 29 қыркүйек пен 9 қазанды қоса алғанда, күн сайын <span class="yellow-text">ҮЛКЕН ЖЕҢІСТЕР</span> алыңыз.
            </p>
            <p>
                Betsoft-тың жүлделі портфолиосынан 40-тан астам слоттардан таңдаңыз және ұтыс ойынына дайын болыңыз. Жүлделерді айналдыруды ойынның кез келген нүктесінде ойынды үзбей іске қосуға болады, бұл ойынға толқу мен салқындық қосу үшін жеңіске жетеді.
            </p>
        </div>
        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Шарттар мен шарттар:</div>
                <ul class="turnament_list">
                    <li><span class="yellow-text">Жүлде алу</span> Онлайн науқан күн сайын бейсенбі, 29 қыркүйек, сағат 10:00-ден 9 қазан CET күні сағат 10:00-ге дейін өтеді.</li>
                    <li>Әр күндік науқан тәулігіне 24 сағат CET 10:00-ден 10:00-ге дейін жарамды.</li>
                    <li>BETSOFT Gaming кез келген уақытта науқанды өзгерту, тоқтата тұру немесе одан бас тарту құқығын өзіне қалдырады.</li>
                    <li>Жалпы жүлде қоры: <span class="yellow-text">70 000 еуро ақшалай</span></li>
                    <li>Күн сайынғы жүлде қоры: <span class="yellow-text">€7000</span> 100 жүлдеден ақшалай</li>
                    <li>Барлық науқанға 1000 ақшалай сыйлық</li>
                </ul>
            </div>
            <img src="/img/tournaments/take-the-prize/dog.png" alt="dog" class="tournament__image" style="margin-left: 3rem; width: 200px;">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/take-the-prize/Thief.png" alt="Thief" class="tournament__image" style="margin-right: 3rem; width: 180px;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Жеңіске жету жолы:</div>
                <ul class="turnament_list">
                    <li>Біліктілік ойындарының кез келгенінде бір ғана нақты ақша айналымы арқылы сәттілікке ие болыңыз.</li>
                    <li>Салыстыратын ең аз ұсыныс: €0,50</li>
                </ul>
            </div>

        </div>


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Жүлделер саны</th>
                        <th>Күніне берілетін жүлделер мөлшері</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>€ 1,000.00</td></tr>
                    <tr><td>2</td><td>€ 500.00</td></tr>
                    <tr><td>4</td><td>€ 300.00</td></tr>
                    <tr><td>13</td><td>€ 100.00</td></tr>
                    <tr><td>30</td><td>€ 50.00</td></tr>
                    <tr><td>50</td><td>€ 20.00</td></tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/take-the-prize/wheel_coins.png" alt="wheel coins" class="tournament__image" style="margin-left: -5rem; width: 500px;">
        </div>




    </div>



    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
            <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
