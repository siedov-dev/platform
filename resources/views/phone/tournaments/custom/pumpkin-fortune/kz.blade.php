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
        font-size: 0.875rem;
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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Турнир кезеңі:</div>
        <div style="font-size: 0.875rem; font-weight: 600; color: #FFFFFF; text-shadow: #cf2d17 0 0 4px; margin-top: 0.5rem;">2022 жылдың 31 қазанынан 1 қарашасына дейін (екі күнді қоса алғанда)</div>
        <div class="flex mt-1" style="justify-content: space-evenly;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
            <img src="/img/tournaments/pumpkin-fortune/candle.png" alt="gates" style="filter: drop-shadow(0 0 5px #fcc339); height: 25px;">
        </div>


    <div class="tournament__cond-title">3 Oaks Network Tournament Ережелер мен шарттар:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнирге қатысу арқылы сіз осы жерде көрсетілген шарттармен, сондай-ақ берілген шарттармен келісесіз.
                    оператор.</li>
                <li>3 Oaks шартқа сәйкес ешқандай төлемсіз турнирді шектеусіз тоқтатуға құқылы.
                    төтенше жағдайда көшбасшылар тақтасы.</li>
                <li>Тек білікті ойыншылар жүлдені талап ете алады (Науқан туралы ақпарат бөлімін тексеріңіз).</li>
                <li>Турнирге тек нақты ақша раундтары қатыса алады.</li>
                <li>Бір айналдыру құнына қоса, турнирге қатысу үшін қосымша ақы жоқ.</li>
                <li>Әр ойыншы көшбасшылар тақтасы үшін жеке турнир идентификаторын алады.</li>
                <li>Егер екі немесе одан да көп ойыншы тең болса, бірінші ұпай жинағандар ең жақсы жүлделерге ие болады.</li>
                <li>Турнир ережелері кез келген уақытта өзгеруі мүмкін.</li>
                <li>Ақаулық барлық төлемдерді жарамсыз етеді.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Біліктілік:</div>
        <div class="flex">
            <div class="tournament__cond-text"> Білікті және жүлдеге ие болу үшін ойыншы төмендегілерді орындауы керек
                шарттар:
                <ol class="tournament__list">
                    <li><strong>Минималды ставка</strong> <span style="color: red; font-weight: bold">1.00 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">И</span>
                    <li><strong>Ең аз сома</strong> spins <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                <div class="flex" style="justify-content: flex-end">
                    <img src="/img/tournaments/pumpkin-fortune/pumpkin08.png" alt="pumpkin" style="width: 50%; filter: drop-shadow(0 0 5px #fcc339); margin-top: -8rem;">
                </div>
                Іріктеу кезеңінде жиналған барлық ұпайлар сақталады және ойыншы үшін есепке алынады.
                біліктіліктен кейін. Біліктілігі жоқ ойыншылар да көшбасшылар тақтасында көрсетіледі.
                бірақ олар білікті болғанға дейін жалаңаш және сұр түсті.
            </div>
        </div>


        <div class="tournament__cond-title">Турнирдегі ұпай саны (жеңімпаз механик):</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> HSRM:</span> ойыншылар бір раундта ең жоғары мультипликатор үшін ұпай алады
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/pumpkin-fortune/broom1.png" alt="gates" style="filter: drop-shadow(0 0 5px #0a0a17)">
        </div>

        <div class="tournament__cond-title">Жүлде қоры:</div>
        <div class="flex ai-center">

            <table class="tournament__table" style="width: 100%; background: #0a0a17A0; background-size: cover; z-index: 2; border-radius: 10px;">
                <thead>
                <tr>
                    <th>Player</th>
                    <th>Prize</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                    <tr><td>1st</td><td>€ 5,000</td><td>€ 5,000</td></tr>
                    <tr><td>2nd</td><td>€ 3,500</td><td>€ 3,500</td></tr>
                    <tr><td>3rd</td><td>€ 2,500</td><td>€ 2,500</td></tr>
                    <tr><td>4th - 5th</td><td>€ 1,500</td><td>€ 3,000</td></tr>
                    <tr><td>6th - 10th</td><td>€ 750</td><td>€ 3,750</td></tr>
                    <tr><td>11th - 20th</td><td>€ 500</td><td>€ 5,000</td></tr>
                    <tr><td>21th - 50th</td><td>€ 375</td><td>€ 11,250</td></tr>
                    <tr><td>51st - 100th</td><td>€ 175</td><td>€ 8,750</td></tr>
                    <tr><td>101st - 150th</td><td>€ 100</td><td>€ 5,000</td></tr>
                    <tr><td>151st - 200th</td><td>€ 45</td><td>€ 2,250</td></tr>
                    <tr><td colspan="2"></td><td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 50,000</span></td></tr>
                </tbody>
            </table>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/pumpkin-fortune/h_gates2.png" alt="gates" style="width: 90%; margin-top: 1rem; filter: drop-shadow(0 0 10px #0a0a17)">
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
