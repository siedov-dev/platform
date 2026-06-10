@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .pad_left_20{
        padding-left: 40px;
    }
    .tournament__table{
        background-color: #0B0D31C0; 
        margin-top: 20px; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 5px;
        text-align: center;
    }
    .margintop_30{
        margin-top: 30px;
    }
    .just_center{
        justify-content: center
    }
    .text-center{
        text-align: center;
    }
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Smartsoft Жаңа жылдық турнирі – <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Smartoft ұсынған JetX, Balloon, FootballX, CricketX, PlinkoX, Multihot5, GeniesBonanza, MultiHotWays, BurningICE40 және басқа да ойындарды ойнаңыз, толық айналым және мультипликациялық миссияларды орындаңыз, көшбасшылар тақтасының дөңгелегін айналдырыңыз және жүлделер ұтып алыңыз!
        </div>
        <div class="tournament__cond-title">Науқан кезеңі:</div>
        <div class="tournament__cond-text">
            Желілік турнирлер сериясы<strong>01.12.2023 – 28.12.2023</strong> аралығында өтеді. Турнир екі кезеңге бөлінеді; Әр кезең үшін жүлде қоры <strong>25 000 еуро</strong>. Бірінші кезең <strong>12.01.2023</strong> 11:00 (+ 4 UTC) мен (соның ішінде) <strong>14.12.2023</strong> 11:00 (+ 4 UTC) және екінші кезең <strong>12.12.2023</strong> 11:00 (+ 4 UTC) мен (соның ішінде) <strong>28.12.2023</strong> 11:00 (+ 4 UTC) аралығында өтеді.
        </div>
        

        <div class="tournament__cond-title">Қалай қатысуға болады?</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                <p>
                    Науқанның басында ойыншылар қатысатын ойындардың тізімін тексеріп, жүгіру тапсырмаларын орындауы керек. Жүлделерді көшбасшылар тақтасындағы ең жақсы 500 ойыншының қатарына қосу арқылы ұтып алуға болады.
                </p>
                <p>
                    Көшбасшылар тақтасы ұпай жүйесімен жұмыс істейді. Ұпай алу үшін ойыншы көшбасшылар тақтасының дөңгелегін айналдыруы керек. Көшбасшылар тақтасының дөңгелегі 6 түрлі нүктеден тұрады: 1,2,10,25,50,100
                </p>
                <p>
                    Дөңгелекті айналдыру құқығын алу үшін ойыншы екі түрлі тапсырманың бірін орындауы керек.
                </p>
                <p>
                    Бірінші миссия «Айналым миссиясы» деп аталады. Ойыншының күн сайын өтуі керек 10 түрлі кезеңі болады және олардың әрқайсысын орындағаннан кейін ол көшбасшылар тақтасының дөңгелегінде 1 айналым алады. Айналым миссиясына қойылатын талаптар:
                </p>
                <ul class="tournament__list pad_left_20">
                    <li>Бірінші кезең ойыншыдан жалпы сомасы 0,10 еуроға ставка қоюды талап етеді;</li>
                    <li>Екінші кезең ойыншыдан жалпы сомасы 5,1 еуро болатын ставкаларды қоюды талап етеді</li>
                    <li>
                        Үшінші кезең ойыншыдан жалпы сомасы 15,1 еуро болатын ставкаларды қоюды талап етеді және келесі кезеңдерге осылай жалғаса береді.
                    </li>
                </ul>
                <table class="tournament__table mob_table" style="">
                    <thead>
                        <tr>
                            <td>№1</td>
                            <td>№2</td>
                            <td>№3</td>
                            <td>№4</td>
                            <td>№5</td>
                            <td>№6</td>
                            <td>№7</td>
                            <td>№8</td>
                            <td>№9</td>
                            <td>№10</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0.10 EURO</td>
                            <td>5 EURO</td>
                            <td>10 EURO</td>
                            <td>25 EURO</td>
                            <td>50 EURO</td>
                            <td>100 EURO</td>
                            <td>250 EURO</td>
                            <td>500 EURO</td>
                            <td>1000 EURO</td>
                            <td>1500 EURO</td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
            <img src="/img/tournaments/new-year/tree.png" alt="tree" style="width: 100%">
        </div>
        <img src="/img/tournaments/new-year/tile.jpg" alt="tree" class="margintop_30"  style="width: 100%">
        <div class="flex  margintop_30">
            <div class="tournament__cond-text">
                <p>
                    Екінші миссия «Көбейткіш миссия» деп аталады. Ойыншы X400 (немесе одан жоғары) мультипликаторына соққан сайын, ол ұпай дөңгелегінде 1 айналдыруға ие болады. Егер ойыншы 850X соққысын алса, ол дөңгелекте 2 айналым алады.
                </p>
                <p>
                    Күндізгі уақытта ойыншы екі миссиядан да айналдыруды ала алады, бірақ күнделікті шектеу шегі екеуінен немесе әрбір миссияның кез келгенінен барлығы 10 айналдыру болып табылады. Шектеуге жеткеннен кейін миссиялар беті жабылады.
                </p>
                <p>
                    Миссиялардың орындалу барысы күн сайын сағат 00:00-де (UTC:00) жаңартылады және ойыншылардың дөңгелек айналдыру мен ұпайлардың жаңа жинағын бастау мүмкіндігі болады.
                </p>
                <p>
                    Crash Games үшін қосымша ереже бар: ставка кем дегенде X1.5 немесе одан жоғары мультипликаторға жиналуы керек немесе жоғалуы керек.
                </p>
                <p>
                    Кейде промо-акцияның алдыңғы экранында доңғалақ барысы пайда болғанша 5 минутқа дейін уақыт кетуі мүмкін – бұл ретте дөңгелектің ішінде нәтижелер дереу жаңартылады.
                </p>
                <p>
                    Дөңгелекті айналдырғаннан кейін ойыншы бірден көшбасшылар тақтасын алады.
                </p>
                <p>
                    Егер көшбасшылар тақтасында 2 немесе одан да көп ойыншының ұпай саны бірдей болса, басқа бәсекелес ойыншыларға қарағанда соңғы ұпайларды кеш жинаған ойыншыға жақсырақ орын беріледі.
                </p>
            </div>
        </div>

        
        <div class="tournament__cond-title">Турнирге қатысу үшін ең төменгі ставка – 0,10 еуро.</div>
        
        <div class="flex ai-center margintop_30">
            <div class="tournament__cond-text" style="margin: 0;">
                <table class="tournament__table mob_table"  style="margin: 0; min-width: 300px;">
                    <thead>
                        <tr>
                            <td>Жүлде орны</td>
                            <td>Жүлделер</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>3,000 Euro</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2,500 Euro</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>2,000 Euro</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>1,500 Euro</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>1,000 Euro</td>
                        </tr>
                        <tr>
                            <td>6*10</td>
                            <td>500 Euro</td>
                        </tr>
                        <tr>
                            <td>11*20</td>
                            <td>300 Euro</td>
                        </tr>
                        <tr>
                            <td>21*50</td>
                            <td>150 Euro</td>
                        </tr>
                        <tr>
                            <td>51*100</td>
                            <td>50 Euro</td>
                        </tr>
                        <tr>
                            <td>101*200</td>
                            <td>10 Euro</td>
                        </tr>
                        <tr>
                            <td>201*500</td>
                            <td>5 Euro</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/new-year/new_year.jpg" alt="tree"style="width: 100%">
        </div>

       
        <div class="tournament__cond-title">Ережелер мен шарттар:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Толық веб-сайтта, мобильді немесе мобильді қолданба нұсқаларында нақты ақшаны пайдаланып орналастырылған ставкалар ғана қатысады
                 турнирде.<br>
                 2. Осы турнирге қатыса алатын ойындар: JetX, Balloon, FootballX, CricketX,
                 PlinkoX, SlicerX, Multihot5, GeniesBonanza, BurningICE BurningICE10, BurningICE40, Джунгли,
                 Вампирлер, Самурайлар, Викингтер, Joker's4Bonuses, TowerX, MultiHotWays, Magic Garden 40, Magic Garden
                 10, Сиқырлы бақ 5, ParaDice10, JetX3, Вавилон, Эльфтер, Ацтек, crazyape, ковбой, ай тасы,
                 fruitcoctail, Dark, Apollo, SummerCocktail, Evolution, Argo, Dark100, Zombies, WW2, CrazyHuntX.<br>
                 3. Нүктелер дөңгелегін ол пайда болғаннан кейін 30 секунд бойы айналдыра аласыз. Әрекетсіздік жағдайында 30-дан кейін
                 секундтан кейін дөңгелек автоматты түрде айналады және ойыншы кездейсоқ негізде марапатталады.<br>
                 4. Көшбасшылар тақтасының ұпайлары мен үлгерімі қатысушы ойындардың қабылдау бөлмесінде көрсетіледі.<br>
                 5. Ең көп ұпай жинаған ойыншы турнирді жеңеді.<br>
                 6. Акцияның соңында 3 жұмыс күні ішінде жеңімпаз ойыншылар сыйлықтар алады.<br>
                 7. Байланыстың жоғалуы немесе техникалық мәселе туындаса, барлық әсер еткен айналдырулар жарамсыз болып саналады және мүмкін
                 турнир нәтижелеріне қосылмайды.<br>
                 8. Казино және Smartsoft жинаған ұпайларынан бас тартуға немесе жүлделерді ұстап қалуға құқылы
                 Нәтиженің барлығы немесе бір бөлігі алаяқтық немесе басқа ойыншылармен сөз байласу нәтижесінде пайда болады деп есептеңіз.<br>
                 9. Smartsoft кез келген уақытта бұл ұсынысты өзгертуге, тоқтата тұруға немесе бас тартуға құқылы.<br>
                 10. Осы ұсынысқа қатысу арқылы тұтынушы осы шарттармен және жалпы талаптармен келіседі
                 Казино ережелері мен шарттары.<br>
                 11. Казино басшылығының барлық шешімдері түпкілікті болып табылады.
            </p>
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
