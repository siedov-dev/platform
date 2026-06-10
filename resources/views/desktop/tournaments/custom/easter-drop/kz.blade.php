@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }

    .tournament__list li {
        font-size: 1rem;
        color: #7a79b0;
        margin-top: 0.5rem;
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
        color: red;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Wazdan Пасха шығарылымына қосылыңыз және үлкен <span style="color: red; font-weight: bold">€250,000</span> ұтып алыңыз! </div>
        <div class="tournament__cond-title">Науқан кезеңі:</div>
        <div class="tournament__cond-text"><strong>3-16 сәуір, 2023 ж.</strong></div>

        <div class="tournament__cond-title">Шарттар мен шарттар:</div>
        <div class="flex ai-center">
            <div class="turnament_cond-text">
                <ul class="turnament_list">
                    <li> Mystery Box науқаны («Науқан») тек нақты ақшамен ойнауға болады.</li>
                    <li> Қатысушы ойыншылар науқан кезеңінде қатысушы ойындарға іріктеу ставкасын қою арқылы науқанға қатысады. <strong>Қосымша шығындар жоқ</strong>.</li>
                    <li> Науқан барысында ойыншылар бірден артық кездейсоқ жүлде ұтып алады.</li>
                    <li> Науқан кезеңінде жарамды ойындарға кез келген іріктеу ставкасы жүлде қорынан жүлде ұтысына әкелуі мүмкін.</li>
                    <li> Іріктеу ставкасы тек бір жүлде ұтып алады. Жүлделер науқан кезінде кездейсоқ түрде беріледі.</li>
                    <li> <strong>Ең төменгі ставка талаптары жоқ</strong>. Барлық ұсыныстар науқанға сәйкес келеді.</li>
                    <li> Қол жетімді жүлделер саны бірден жаңартылады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/bunny_1C.png" alt="bunny" style="width: 250px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe">
            <img src="/img/tournaments/easter-drop/MagicEggs_blue.png" alt="egg" style="width: 300px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Барлық ұтып алған жүлделер ұтыс туралы хабарланған кезде ойыншылардың казино шоттарына бірден аударылады.</li>
                    <li>Жүлделер жүлде қоры қойындысына сәйкес төленеді.</li>
                    <li> Ақшалай сыйлықтың максималды сомасы <strong>250 000€</strong>, <strong>2500</strong> құпия жәшікке бөлінеді.</li>
                    <li> Ақшалай жүлделер <strong>бәс тігу талап етілмейді</strong>.</li>
                    <li> Бонустық қаражат арқылы төленген жүлделер бонустық қаражатпен төленеді.</li>
                    <li> Ақшалай жүлделер ұтыс тігу енгізілген кездегі валютаны еуродан айырбастауға сәйкес ұтыс тігу енгізілген валютада төленеді.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            {{--            <img src="/img/tournaments/easter-drop/bunny_2B.png" alt="bunny" style="width: 250px;">--}}
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Тек расталған казино ойыншылары жүлде ұтып алады.</li>
                    <li> Казино ақшалай сыйлықты төлемеу құқығын өзіне қалдырады, егер ұтыстар кез келген көрінетін қатенің, қатенің немесе техникалық ақаулықтың (соның ішінде қате ойын төлемдерінің) нәтижесі болса, ол машина немесе адам қателігінің кез келгеніне қатысты болса да. қатысатын ойындар. Казино сонымен қатар, егер оның пікірінше, ұтыстар алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесінде болса, ақшалай сыйлықты төлемеу құқығын өзіне қалдырады.</li>
                    <li> Казино кез келген уақытта кез келген себеппен осы науқанды өзгерту, өзгерту, тоқтату немесе тоқтату құқығын өзіне қалдырады, сонымен қатар кез келген уақытта осы ұсыныстың шарттарын өзгерту құқығын өзіне қалдырады. Алдыңғы шарттар мен ұсыныстар ұсынысқа кез келген өзгерістер енгізілгенге дейін өтініш берген ойыншылар үшін жарамды болады.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MagicEggs_green.png" alt="egg" style="width: 300px; margin-right: 1rem; margin-bottom: -50px;">
        </div>

        <div class="tournament__cond-title">Қатысатын ойындар:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Барлық ваздан ойындары</span></div>

        <div class="tournament__cond-title">Жүлде қоры:</div>
        <div class="tournament__cond-text">Ақшалай жүлделердің максималды жалпы сомасы <strong>€250 000</strong>. Барлығы <strong>2500 құпия жәшік</strong> бар. Әрбір жұмбақ жәшікте келесі тізімнен жүлде алуға болады:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>75 EUR</li>
                    <li>50 EUR</li>
                    <li>25 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MysteryDrop_box.png" alt="bunny" style="width: 250px; margin-left: 100px;">
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
</div>
