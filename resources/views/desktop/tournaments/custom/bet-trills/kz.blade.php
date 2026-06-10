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
        box-shadow: #0d8f1b 0 0 5px;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;
        font-weight: 500;
        text-align: center;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table tr:last-child td{
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__table th {
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__list strong{
        color: #00bcff;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Шарттар мен шарттар:</div>
                <ul class="tournament__list">
                    <li>Evoplay Bet Trills онлайн турнирі тек нақты режимде қолжетімді.</li>
                    <li>Evoplay Bet Trills онлайн турнирі сағат 00:01 <strong>2023 жылдың 15 мамыры және 2023 жылдың 24 мамыры</strong> мен UTC 23:59</li> аралығында өтеді.
                    <li>Турнир жүлдесі <strong>€10 000</strong> және көшбасшылар тақтасында ең көп ұпай жинаған <strong>100</strong> үздік ойыншы арасында таратылады.</li>
                    <li>Турнирге қатысу үшін талап етілетін ең аз ставка <strong>€0,20</strong> немесе жергілікті валютадағы баламасы. Көшбасшылар тақтасындағы алғашқы <strong>100</strong> ойыншы сыйлықтар алады.</li>
                    <li>Осы науқанның жүлде қоры мен ең төменгі ұсынысы <strong>EUR</strong> мөлшерінде белгіленген және валюта айырбастау ауытқуларына ұшырауы мүмкін.</li>
                    <li>Әр айналдыру ойыншының көшбасшылар тақтасында көрсетілетін жалпы ұпайына әсер етеді. Жалпы ұпай қатысушылар ойындарының барлық раундтарында жиналған ұпайлар негізінде есептеледі.</li>
                </ul>
            </div>

            <img src="/img/tournaments/bet-trills/girl1.png" alt="girl1" style="margin-left: .5rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 1;">
        </div>

        <div class="flex jc-center mt-1">
            <img src="/img/tournaments/bet-trills/evo.png" alt="girl1" style="margin-left: .5rem; position: relative; right: 0; bottom: 0; width: 30%; opacity: 1;">
        </div>

        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/bet-trills/girl2.png" alt="girl2" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 1;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Турнирде білікті механик бар: <strong>жалпы ставканың жинақталуы</strong>.</li>
                    <li><strong>Мысалы:</strong> Егер сіз €1 тіккен болсаңыз және 12,80 еуро ұтсаңыз, 12,8 ұпай аласыз. Ойынның екінші раундында 1 еуроға бәс тіккен болсаңыз және ұтыс € 7,50 болса, сіз жинаған 20,3 ұпай үшін 7,5 ұпай аласыз. Ең аз ставкасы бар кез келген ұтыстар <strong>ҰПАЛДАР</strong></li>ға түрлендіріледі
                    <li>Турнирде іріктеу ойындарының тізімі және жүлде қоры (EUR) бар, ол турнир ережелеріне сәйкес жеңімпаздар арасында бөлінеді.</li>
                    <li>Егер бірнеше қатысушы бірдей ұпай жинаса, бірінші орын алған қатысушы көшбасшылар тақтасында жоғарырақ орынға ие болады.</li>
                    <li>Ақшалай жүлделер науқан аяқталғаннан кейін алуға болатын қаражат түрінде казино ойыншыларының шоттарына аударылады.</li>
                    <li>Ұпайлардың барлығы немесе бір бөлігі машина немесе адам тудырған кез келген айқын қатенің, қатенің немесе техникалық ақаудың (соның ішінде қате ойын төлемдерінің) нәтижесі болса, Evoplay нәтижелерді жою немесе жүлделерді төлемеу құқығын өзіне қалдырады. . қатысушы ойындардың кез келгеніндегі қате.</li>
                    <li>Сондай-ақ, Evoplay ұпайлардың барлығын алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесі деп есептесе, ұпайларды жою немесе жүлдені төлемеу құқығын өзіне қалдырады.</li>
                </ul>
            </div>
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 50%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/bet-trills/tbg3.png"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Ойыншы</th>
                        <th>Жүлде</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>1 664 €</td>
                        <tr><td>2</td><td>1 270 €</td>
                        <tr><td>3</td><td>1 000 €</td>
                        <tr><td>4</td><td>667 €</td>
                        <tr><td>5</td><td>600 €</td>
                        <tr><td>6</td><td>539 €</td>
                        <tr><td>7</td><td>400 €</td>
                        <tr><td>8</td><td>300 €</td>
                        <tr><td>9</td><td>220 €</td>
                        <tr><td>10-19</td><td>133 €</td>
                        <tr><td>20-49</td><td>33 €</td>
                        <tr><td>50-100</td><td>20 €</td>
                        <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
                    </tbody>
                </table>
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
