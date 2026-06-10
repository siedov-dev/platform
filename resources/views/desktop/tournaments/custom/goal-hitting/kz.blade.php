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
        color: #FFFFFF;
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
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__list strong{
        color: #00b8ff;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: 30rem;">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Шарттар мен шарттар:</div>
                <ul class="tournament__list">
                    <li>Evoplay Goal Hitting Network тек нақты режимде қол жетімді.</li>

                    <li>Evoplay Goal Hitting Network турнирі 2022 жылдың қараша айында (7) келісілген жеті күн ішінде өтеді. Турнир 2022 жылдың 17 қарашасы 00:01 UTC мен 2022 жылдың 23 қарашасы 23:59 UTC аралығында өтеді. </li>

                    <li>Турнирдің жүлде қоры <strong>€10 000</strong> және көшбасшылар тақтасында ең көп ұпай жинаған 100 үздік ойыншы арасында бөлінеді.</li>

                    <li>Турнирге қатысу үшін талап етілетін ең аз ставка <strong>€0,50</strong> немесе жергілікті валютадағы баламасы. Ұпайлар неғұрлым көп болса, ойыншы көшбасшылар тақтасында соғұрлым жоғары болады. Лидерлер тақтасындағы алғашқы 100 ойыншы сыйлықтарға ие болады. </li>

                    <li>Осы науқанның жүлде қоры мен ең төменгі ұсынысы <strong>EUR</strong> мөлшерінде белгіленген және валюта айырбастау ауытқуларына ұшырауы мүмкін.</li>
                </ul>
            </div>

            <img src="/img/tournaments/goal-hitting/ball.png" alt="Gambling lady" class="tournament__image" style="margin-left: 2rem; filter: drop-shadow(0 0 10px #00b8ff)">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/goal-hitting/cup.png" alt="Gambling man" class="tournament__image" style="margin-right: 3rem; filter: drop-shadow(0 0 10px #00b8ff);">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Әр айналдыру ойыншының көшбасшылар тақтасында көрсетілетін жалпы ұпайына әсер етеді. Жалпы ұпай қатысушылар ойындарының барлық раундтарында жиналған ұпайлар негізінде есептеледі.</li>
                    <li>Турнирде білікті механик бар: жалпы ұтыстардың жинақталуы.</li>

                    <li>Турнирде <strong>іріктеу ойындарының тізімі және жүлде қоры</strong> (Еуро) бар, ол осы турнир ережелеріне сәйкес көшбасшылар тақтасының жеңімпаздары арасында бөлінеді.</li>

                    <li>Егер бірнеше Қатысушылар турнирдің ұпай жинау процесінде бірдей ұпай жинаса, жоғарырақ орын бірінші орын алған Қатысушыға беріледі.</li>

                    <li>Ақшалай жүлделер ойыншылардың шоттарына турнир аяқталғаннан кейін казино алуға болатын қаражат түрінде аударылады.</li>

                    <li>Егер ұпайлардың барлығы немесе бір бөлігі машина немесе адам қателігінен туындаған кез келген айқын қатенің, қатенің немесе техникалық ақаудың (соның ішінде дұрыс емес ойын төлемдерінің) нәтижесі болса, Evoplay ұпайларды жою немесе жүлдені төлемеу құқығын өзіне қалдырады. қатысатын ойындардың кез келгеніне қатысты.</li>
                </ul>
            </div>
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Сонымен қатар, Evoplay ұпайлардың барлығын алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесі деп есептесе, ұпайларды қайтарып алу немесе жүлдені төлемеу құқығын өзіне қалдырады.</li>


                    <li><strong>Турнир рейтингі:</strong>жеңістердің жалпы саны.</li>
                    <li><strong>Турнир критерийлері:</strong> Турнир ұпайлары әрбір жеңіс үшін беріледі (ең аз ставка €0,50)</li>

                    <li><strong>Мысалы:</strong> Егер сіз € 1 бәс тігіп, 12,80 еуро ұтсаңыз, 12,8 ұпай аласыз. Ойынның екінші раундында 1 еуро ұтыс тігетін болсаңыз және ұтыс 7,50 евро болса, сіз жинаған 20,3 ұпай үшін 7,5 ұпай аласыз. Ең аз ставкасы бар барлық ұтыстар POINTS ға түрлендіріледі</li>
                </ul>
            </div>

            <img src="/img/tournaments/goal-hitting/owl.png" alt="Gambling lady" class="tournament__image" style="margin-left: 3rem;  filter: drop-shadow(0 0 10px #00b8ff);">
        </div>



        <div class="flex jc-center ai-center mt-2">
            <div style="background: url('/img/tournaments/goal-hitting/stadium.png') no-repeat center; background-size: cover; border-radius: 2rem; width: 50rem;">
                <div class="tournament__cond-text" style="width: 30rem; padding: 3rem; margin-right: auto; margin-left: auto;">
                    <table class="tournament__table mt-2" style=" background: linear-gradient(#8f173399, #4d4ab599);">
                        <thead>
                        <tr>
                            <th>Ойыншы</th>
                            <th>Жүлде</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td>1st</td><td>1 664 €</td></tr>
                        <tr><td>2nd</td><td>1 270 €</td></tr>
                        <tr><td>3rd</td><td>1 000 €</td></tr>
                        <tr><td>4th</td><td>667 €</td></tr>
                        <tr><td>5th</td><td>600 €</td></tr>
                        <tr><td>6th</td><td>539 €</td></tr>
                        <tr><td>7th</td><td>400 €</td></tr>
                        <tr><td>8th</td><td>300 €</td></tr>
                        <tr><td>9th</td><td>220 €</td></tr>
                        <tr><td>10th - 19th</td><td>133 €</td></tr>
                        <tr><td>20th - 49th</td><td>33 €</td></tr>
                        <tr><td>50th - 100th</td><td>20 €</td></tr>
                        <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
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
