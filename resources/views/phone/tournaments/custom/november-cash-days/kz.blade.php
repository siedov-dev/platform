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
        color: #FFF;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: initial;">
    </div>


    <div class="article__text flex column">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Ережелер мен шарттар:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">November CashDays 70k</span> тек тікелей эфирде ойнауға болады</li>

                    <li>November CashDays 70k 1 қарашада <span class="yellow-text">0:01 UTC аралығында жұмыс істейді.
                             2022 және 2022 жылдың 8 қарашасы UTC 23:59 дейін</span></li>

                    <li>Ұтыс ойынына қатысу үшін талап етілетін ең аз ставка - <span class="yellow-text">€0,20</span> немесе валюталық баламасы.</li>

                    <li>Кез келген ұтыстар ойыншының жалпы шотында жинақталады, олар келесіде көрсетіледі:
                        Көшбасшылар тақтасы. Жиынтық ұпай жинаған ұпайларды қосу арқылы есептеледі
                        турнир кезеңінде қатысушы ойындарда ойналған барлық раундтарда.</li>

                    <li>Ұпайлар біртіндеп беріледі, әрбір 1 еуро (немесе басқа валютадағы баламасы)
                        жеңіс 10 ұпай. <span class="yellow-text">Мысалы, 10 еуро ұту 100 ұпайға тең.</span></li>

                    <li>Ақшалай сыйлықтар ойыншылардың шоттарына алынуы мүмкін қаражат түрінде аударылады.
                        науқан аяқталғаннан кейін 72 сағат (3 жұмыс күні) ішінде.</li>

                </ul>

            </div>

        </div>
        <img src="/img/tournaments/november-cash-days/cup.png" alt="Lion head" class="tournament__image" style="filter: drop-shadow(0 0 5px gold);">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Осы науқанның жүлде қоры мен ең төменгі ұсынысы <span class="yellow-text">EUR</span> болып орнатылған және
                        валюта айырбастау ауытқуларына байланысты.</li>

                    <li>Валюталарды айырбастау бағамдарын біздің жүйе 72 көмегімен автоматты түрде орнатады
                        турнирдің басталуына бірнеше сағат қалғанда, мәндер сол уақытта сақталады
                        турнирдің бүкіл кезеңі.</li>

                    <li>Ұпайларды қайтарып алу немесе жүлдені толығымен немесе ішінара төлемеу құқығын өзімізде қалдырамыз
                        кез келген айқын қатенің, қатенің немесе техникалық ақаудың (соның ішінде
                        ойын төлемдері), кез келгеніне қатысты машина немесе адам қатесі туындағанына қарамастан
                        қатысатын ойындар.</li>

                    <li>Сонымен қатар, егер біздің ойымызша, ұпайларды қайтарып алу немесе жүлдені төлемеу құқығымыз бар.
                        есептік жазбаның барлығы немесе бір бөлігі алаяқтықтың немесе басқа ойыншылармен сөз байласуының нәтижесі деген пікірде.</li>

                    <li>Біз кез келген уақытта науқанды өзгерту немесе тоқтату құқығын сақтаймыз.</li>
                </ul>
            </div>
        </div>
        <img src="/img/tournaments/spin-mania/playson.png" alt="playson" class="tournament__image" style="filter: drop-shadow(0 0 5px white);">


        <div class="flex ai-center mb-1">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/november-cash-days/tbg.png"); background-size: cover; box-shadow: #0C0328 0 0 5px;'>
                    <thead>
                    <tr>
                        <th>Ойыншы</th>
                        <th>Жүлде</th>
                        <th>Сома</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>10 000 €</td><td>10 000 €</td></tr>
                    <tr><td>2nd</td><td>5 000 €</td><td>5 000 €</td></tr>
                    <tr><td>3rd</td><td>3 000 €</td><td>3 000 €</td></tr>
                    <tr><td>4th -10th</td><td>1 000 €</td><td>7 000 €</td></tr>
                    <tr><td>11th -25th</td><td>500 €</td><td>7 500 €</td></tr>
                    <tr><td>26th - 50th</td><td>400 €</td><td>10 000 €</td></tr>
                    <tr><td>51th - 100th</td><td>200 €</td><td>10 000 €</td></tr>
                    <tr><td>101th - 200th</td><td>100 €</td><td>10 000 €</td></tr>
                    <tr><td>201th - 300th</td><td>50 €</td><td>7 500 €</td></tr>
                    <tr><td colspan="3" style="text-align: center"><b><span class="yellow-text">Grand Total 70 000 €</span></b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>


        <img src="/img/tournaments/november-cash-days/fly-coins.png" alt="fly coins" class="tournament__image" style="filter: drop-shadow(0 0 5px gold);">


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

    @include('phone.footer')

</div>
