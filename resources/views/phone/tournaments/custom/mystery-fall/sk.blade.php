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
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
    .text-gold{
        color: #ffb500;
    }
    .tournament__cond-text strong{
        font-weight: 500;
        color: #e4b556;
        white-space: nowrap;
    }
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2 text-gold">Zbohom letu so štýlom!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> je nová promoakcia od Wazdan, počas ktorej budete môcť vyhrať odmeny z masívneho
            <strong>3 000 000 €</strong> balenia cien. Hrajte svoje obľúbené hry od Wazdan a nechajte <span class="hot">Mystery Drop™</span> urobiť svoje kúzla.</div>

        <div class="tournament__cond-title">Obdobie kampane:</div>
        <div class="tournament__cond-text">Od <strong>15. septembra do 15. októbra 2023</strong></div>

        <div class="tournament__cond-title">Podmienky a ustanovenia:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Kampaň Mystery Drop ("Kampaň") je dostupná len v reálnom režime hry.</li>
                    <li>Zúčastnení hráči sa zúčastnia kampane tým, že vložia kvalifikačnú stávku do zapojených hier počas obdobia kampane. <strong>Žiadne dodatočné náklady nie sú vyžadované</strong>.</li>
                    <li>Hráči môžu vyhrať viac než jednu náhodnú cenu počas kampane.</li>
                    <li>Akákoľvek kvalifikačná stávka v zapojených hrách počas obdobia kampane môže spustiť cenu z ceny.</li>
                    <li>Kvalifikačná stávka môže vyhrať len jednu cenu. Ceny budú udelené náhodne počas obdobia kampane.</li>
                    <li><strong>Žiadny minimálny rozsah stávky</strong>. Všetky stávky sú kvalifikované na účasť v kampani.</li>
                    <li>Počet dostupných cien sa okamžite aktualizuje.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Všetky vyhrané ceny budú okamžite pripísané na hráčske účty v kasíne po obdržaní oznamu o výhre.</li>
                    <li>Ceny budú vyplácané podľa tabuľky cien.</li>
                    <li>Maximálna celková hodnota hotovostných cien je <strong>500 000 EUR</strong> rozdelená do <strong>5 000 Záhadných Boxov</strong>.</li>
                    <li>Hotovostné ceny <strong>nevyžadujú ŽIADNE stávkové požiadavky</strong>.</li>
                    <li>Ceny vyplatené pri použití bonusových prostriedkov budú vyplatené vo forme bonusových prostriedkov.</li>
                    <li>Hotovostné ceny budú vyplatené v mene, v ktorej bola vyhraná stávka, podľa prevodového kurzu na euro v čase vyhranej stávky.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Iba overení hráči kasína majú nárok na výhru cien.</li>
                    <li>Kasíno si vyhradzuje právo nevyplatiť hotovostnú cenu v prípade, že výhra vyplynie z akéhokoľvek zjavného chybného alebo technického zlyhania (vrátane nesprávnych výplat hier), či už spôsobené strojovou alebo ľudskou chybou v súvislosti s akoukoľvek zo zapojených hier. Kasíno si ďalej vyhradzuje právo nevyplatiť hotovostnú cenu v prípade, že podľa jeho názoru výhra vyplynie z podvádzania alebo spolupráce s inými hráčmi.</li>
                    <li>Kasíno si vyhradzuje právo kedykoľvek a z akéhokoľvek dôvodu upraviť, zmeniť, prerušiť alebo ukončiť túto promóciu a má tiež právo meniť podmienky tejto ponuky kedykoľvek. Predchádzajúce podmienky a ponuky budú platné pre hráčov, ktorí ich budú nárokovat do doby akýchkoľvek zmien v ponuke.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Zapojené hry:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Všetky hry od Wazdan</span></div>

        <div class="tournament__cond-title">Balenie cien:</div>
        <div class="tournament__cond-text">Maximálna celková hodnota hotovostných cien je <strong>500 000 EUR</strong>. Celkovo je k dispozícii <strong>5 000 Záhadných Boxov</strong>. Každý Záhadný Box môže odmeniť cenu z nasledujúceho zoznamu:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>25 EUR</li>
                    <li>20 EUR</li>
                    <li>15 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/MysteryDrop_box.png" alt="bunny" style="width: 200px; margin-left: 10px;">
        </div>

    <div class="tournament__games">
        <div class="tournament__games-head">
            <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
        <!--<a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>-->
        </div>
        <div class="tournament__games-list">
            @foreach($tournament->getAllowedGames() as $game)
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
</div>
