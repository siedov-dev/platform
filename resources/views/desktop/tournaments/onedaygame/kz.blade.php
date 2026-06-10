@php
/** @var \App\Models\Tournaments\OnedaygameTournament $tournament */
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
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
    }
    .article__text p{
        color: #FFFFFF;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px black;
        text-indent: 1rem;
        font-weight: 600;
    }
</style>
<div class="width-limiter">



    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <p>
                    Танымал <span class="yellow-text">SLOT</span> ойын жобасы Kraken Casino <span class="yellow-text">CHALLENGE</span> қабылдаңыз.
                </p>
                <p>
                    Бұл турнир 14 күнге созылады, онда сіз күн сайын ұтып, сыйлықтар ала аласыз. Әрбір жаңа күн - белгілі бір ойынның жаңа шақыруы.
                    Турнирдің әр күніне 30 слот, күн сайын ойынды жеңгені үшін сыйлық алыңыз.
                    Күннің үздік ойыны сыйлығы: <span class="yellow-text">3000$</span>
                    «Күн ойынында» 10 реттен артық ұтып, турнир соңында <span class="yellow-text">$15 000</span> супер жүлдесін алыңыз!
                </p>

                <div class="tournament__cond-title">Шарттар мен талаптар:</div>
                <ul class="tournament__list">
                    <li>Турнир жеңімпазы «күн ойынында» күніне (24 сағат) ставкалардың максималды санын, <span class="yellow-text"-тен ескерілетін ең аз ставканы жасаған адам болып табылады. >{{$tournament->getMinBetString() }}</span> әр айналдыруға («Күн ойынында» жасалған {{$tournament->getMinBetString()}} барлығы қорытындыланады).</li>
                    <li>Ақша сыйақылары <span class="yellow-text">10x</span> ставкасымен шотыңызға аударылады. Жеңімпаз автоматты есептеу арқылы анықталады. </li>
                    Әр күн үшін <li><span class="yellow-text">14 слот</span>, біздің турнирге қатысу арқылы күн сайын саяхаттаңыз, күн сайын сыйлықтар алыңыз. Турнирге қатысу үшін ең аз ставка — <span class="yellow-text">{{$tournament->getMinBetString()}}</span>. (немесе басқа валютадағы баламасы)</li>
                    <li>Жеңімпаз күн сайын анықталады. Турнир <span class="yellow-text">{{$tournament->getStartDateTime()->isoFormat('D MMMM')}} бастап {{$tournament->getEndDateTime()->isoFormat('D MMMM) аралығында өтеді. ')}}</span></li>
                    <li>Жүлделер автоматты түрде ойыншының ойын шотына күннің соңында "Күн ойыны" үшін 24 сағат ішінде аударылады.</li>
                    <li>Барлық турнир жүлделерінде <span class="yellow-text">10 есе көп</span> ставка талап етіледі.</li>
                    <li>Турнирде жеңіске жету нәтижесінде алынған барлық бонустар мен ұтыстар шығарылған күннен бастап <span class="yellow-text">7 күн</span> ішінде ойналуы керек. Белгіленген уақыт кезеңінде бонустық ставкалар талаптары орындалмаса, бонус сомасы, сондай-ақ бонус пен ақшалай жүлдені тігу нәтижесінде алынған кез келген ұтыстар жарамдылық мерзімінің аяқталуына байланысты ойыншының шотынан есептен шығарылады.</ li>
                    <li>"Күн ойыны" жеңімпаздарының тізімі есептеулерден кейін кестеде көрсетіледі.</li>
                    <li>Егер жарыстың соңында ойыншының есептік жазбасы бұғатталса немесе өзін-өзі алып тастаса, ұтыс төленбейді</li>
                    <li>Қатысу туралы сұрақтарыңыз болса, <span class="yellow-text">7/24/7 қолжетімді қолдау қызметіне хабарласыңыз.
                    <li><span class="yellow-text">турнирде жеңген ақшаны РУЛЕТТА ойнауға болмайды!</span></li>
                    <li>Ережелерді алдау және айналып өту әрекеті болған жағдайда, Kraken Casino ойын жобасы ойыншыны одан әрі жарнамаға қатысудан бас тартуға құқылы. </li>
                    <li>Kraken Casino ойын жобасы кез келген уақытта себептерді көрсетпестен шарттар мен шарттарды өзгертіп, жарысты аяқтауы мүмкін.</li>
                    <li>Жеңімпаздар кестесі нақты уақыт режимінде жаңартылады, онда қатысушылар өзгерістерді бақылай алады. </li>
                    <li>Kraken Casino ойын жобасының барлық басқа ережелері күшінде қалады.</li>
                </ul>
            </div>

        </div>

    </div>
</div>
