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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Evoplay Harvest Of Bets онлайн турнирін тек нақты режимде ойнауға болады.</li>
                    <li>Evoplay Harvest of Bets онлайн турнирі 2022 жылдың қыркүйек айында келісілген он екі (12) күнде өтеді. Турнир 2022 жылдың 19 қыркүйегінде және 2022 жылдың 30 қыркүйегінде UTC 00:01 UTC 23:59 дейін өтеді</li>
                    <li>Турнирдің жүлде қоры €12 000 және көшбасшылар тақтасында ең жоғары ұпай жинаған үздік 100 ойыншы арасында бөлінеді.</li>
                    <li>Турнирге қатысу үшін талап етілетін ең аз ставка – €0,50. немесе жергілікті валютадағы баламасы. Ұпайлар неғұрлым көп болса, ойыншы көшбасшылар тақтасында соғұрлым жоғары болады. Алғашқы 100 ойыншы сыйлықтарға ие болады.</li>
                    <li>Осы науқанның жүлде қоры мен ең төменгі ұсынысы еуромен белгіленген және валюта айырбастау ауытқуларына байланысты болуы мүмкін.</li>
                    <li>Әр айналдыру ойыншының көшбасшылар тақтасында көрсетілетін жалпы ұпайына әсер етеді. Жалпы ұпай қатысушы ойындарда ойналған барлық раундтарда жиналған ұпайлар арқылы есептеледі.</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/harvest-of-bets/rept2.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Турнирде білікті механик бар: жалпы ставканың жинақталуы. Турнир ұпайлары әрбір ставка үшін беріледі (ең аз ставка €0,50)</li>
                    <li>Мысал. 1 еуроға бәс тіксеңіз, ұтсаңыз да, ұтпасаңыз да, сіз бәрібір 1 ұпай аласыз. Ойынның екінші раундында 2 еуроға бәс тіккен болсаңыз, сіз 2 ұпай аласыз, ал жиналған ұпайлардың жалпы саны 3 болады. 1 € = 1 ҰПАЛ</li>
                    <li>Турнирде іріктеу ойындарының тізімі және жүлде қоры (EUR) бар, ол турнир ережелеріне сәйкес жеңімпаздар арасында бөлінеді.</li>
                    <li>Егер бірнеше қатысушы бірдей ұпай жинаса, бірінші орын алған қатысушы көшбасшылар тақтасында жоғарырақ орынға ие болады.</li>
                    <li>Ақшалай жүлделер науқан аяқталғаннан кейін алуға болатын қаражат түрінде казино ойыншыларының шоттарына аударылады.</li>
                    <li>Ұпайлардың барлығы немесе бір бөлігі машина немесе адам тудырған кез келген айқын қатенің, қатенің немесе техникалық ақаудың (соның ішінде қате ойын төлемдерінің) нәтижесі болса, Evoplay нәтижелерді жою немесе жүлделерді төлемеу құқығын өзіне қалдырады. . қатысушы ойындардың кез келгеніндегі қате.</li>
                    <li>Сондай-ақ, Evoplay ұпайлардың барлығын алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесі деп есептесе, ұпайларды жою немесе жүлдені төлемеу құқығын өзіне қалдырады.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/harvest-of-bets/evo.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 90%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/harvest-of-bets/tbg.jpg"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 985 €</td>
                    <tr><td>2</td><td>1 520 €</td>
                    <tr><td>3</td><td>1 200 €</td>
                    <tr><td>4</td><td>800 €</td>
                    <tr><td>5</td><td>720 €</td>
                    <tr><td>6</td><td>647 €</td>
                    <tr><td>7</td><td>480 €</td>
                    <tr><td>8</td><td>360 €</td>
                    <tr><td>9</td><td>264 €</td>
                    <tr><td>10-19</td><td>160 €</td>
                    <tr><td>20-49</td><td>40 €</td>
                    <tr><td>50-100</td><td>24 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 12 000 €</b></td></tr>
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
