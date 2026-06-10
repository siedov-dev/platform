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
            <div class="turnament_cond-text">
                <p>
                    Танымал <span class="yellow-text">SLOT</span> ойын жобасы Kraken Casino <span class="yellow-text">CHALLENGE</span> қабылдаңыз.
                </p>
                <p>
                    Бұл турнир <span class="yellow-text">14 күнге</span> созылады, мұнда күн сайын ұтып, жүлделер алуға болады. Әрбір жаңа күн жаңа сынақ.
                </p>

                <div class="tournament__cond-title">Шарттар мен шарттар:</div>
                <ul class="turnament_list">
                    <li>Турнир жеңімпазы - <span class="yellow-text"> 14 күн</span> ішінде турнир барысында ең көп ставка жасаған адам.</li>
                    <li><span class="yellow-text">{{$tournament->getMinBetString()}}</span> және одан жоғары барлық ставкалар </li>
                    <li>Ақша сыйақылары <span class="yellow-text">1х</span> ставкасымен шотыңызға аударылады. Жеңімпаз автоматты есептеу арқылы анықталады. <span class="yellow-text"> {{$tournament->getMinBetString()}} </span> турниріне қатысуға арналған минималды ставка.</li>
                    <li>Жүлделер турнирдің соңында ойыншының ойын шотына автоматты түрде аударылады.</li>
                    <li>Турнирдің соңындағы барлық ақшалай сыйлықтардың 1 есе сомасын құрайтын бәс тігу талабы бар.</li>
                    <li>Турнирде жеңіске жету нәтижесінде алынған барлық бонустар мен ұтыстар шығарылған күннен бастап 7 күн ішінде ойналуы керек. Белгіленген уақыт кезеңінде бонустық ставкалар талаптары орындалмаса, бонус сомасы, сондай-ақ бонус пен ақшалай жүлдені тігу нәтижесінде алынған кез келген ұтыс жарамдылық мерзімінің аяқталуына байланысты ойыншының шотынан есептен шығарылады.</ li>
                    <li>Жеңімпаздар тізімі есептеуден кейін кестеде көрсетіледі.</li>
                    <li>Егер жарыстың соңында ойыншының есептік жазбасы бұғатталса немесе өзін-өзі алып тастаса, ұтыс төленбейді</li>
                    <li>Қатысу туралы сұрақтарыңыз болса, тәулік бойы жұмыс істейтін қолдау көрсету тобына хабарласыңыз.</li>
                    <li>Турнирде ұтқан ақшаны слоттардан басқа ойын түрлеріне қоюға болмайды!</li>
                    <li>Ережелерді алдау және айналып өту әрекеті болған жағдайда, Kraken Casino ойын жобасы ойыншының жарнамаға одан әрі қатысуынан бас тартуға құқылы.</li>
                    <li>Kraken Casino ойын жобасы кез келген уақытта себептерді көрсетпестен шарттар мен шарттарды өзгертіп, жарысты аяқтауы мүмкін.</li>
                    <li>Жеңімпаздар кестесі нақты уақыт режимінде жаңартылады, онда қатысушылар өзгерістерді бақылай алады. </li>
                    <li>Kraken Casino ойын жобасының барлық басқа ережелері күшінде қалады. </li>
                </ul>
            </div>
        </div>

    </div>
</div>
