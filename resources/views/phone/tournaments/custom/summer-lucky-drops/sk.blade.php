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
        color: #fff;
    }
    .hot{
        color: red;
        font-weight: bold;
        white-space: nowrap;
    }
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Podujatie Lucky Drops prebieha:</div>
        <div class="tournament__cond-text mb-1">Od <strong>24. do 27. augusta 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="seaguls" style="width: 180px; height: 160px; margin-bottom: 10px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Horúce obdobie</span> - zvyšuje šancu hráča na výhru ceny:</div>
        <div class="tournament__cond-text">Začína <span style="color: red; font-weight: bold">26. augusta 2023 o 0:00 UTC</span> až do konca kampane <strong>Weekend Lucky Drops</strong></div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/cocktail3.png" alt="cocktail" style="width: 150px;">
        </div>

        <div class="tournament__cond-title">Podmienky turnaja 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Účasťou v turnaji súhlasíte s tu uvedenými Podmienkami turnaja a podmienkami uvedenými operátorom.</li>
                <li>V prípade núdze si 3 Oaks Gaming vyhradzuje právo kedykoľvek zrušiť akciu Lucky Drops, vrátane platieb za výhry, ak je to považované za nevyhnutné.</li>
                <li>Len hry s reálnymi peniazmi môžu byť zapojené do kampane Lucky Drops.</li>
                <li>Okrem ceny za zatočenie neexistujú žiadne ďalšie náklady na vstup do siete Network Lucky Drops.</li>
                <li>Každý hráč dostane osobné turnajové ID pre žebrejovník.</li>
                <li>Pravidlá Network Lucky Drops sa môžu kedykoľvek zmeniť.</li>
                <li>Porucha zruší všetky výplaty.</li>
            </ul>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacation" style="width: 250px;">
        </div>

        <div class="tournament__cond-title">Kvalifikácia:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Hráč sa musí pripojiť alebo si vybrať jeden z účastníckych hier.</li>
                    <li>Minimálna stávka potrebná pre získanie LuckyDropu je <span style="color: red; font-weight: bold">0,50 EUR</span> alebo ekvivalentná suma v dostupných menách v účastníckych hrách.</li>
                    <li>Každá kvalifikovaná stávka na jednej z účastníckych hier môže počas obdobia propagácie spustiť náhodnú cenu zo zásobníka cien.</li>
                    <li>Každý hráč môže získať najviac <span style="color: red; font-weight: bold">3 ceny</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Mechanika výhry:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> ponúka hráčom možnosť získať v hotovosti ceny kedykoľvek počas obdobia udalosti. Ceny sa udeľujú náhodne a všetci hráči majú rovnakú šancu na výhru pri každom kvalifikovanom spustení.</div>
        </div>11

        <img src="/img/tournaments/summer-lucky-drops/man_hammok.png" alt="beach" style="width: 100%;">

        <div class="tournament__cond-title">Účastnícke hry:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Všetky hry</span></div>
        <div class="tournament__cond-title">Cena v hre:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Cena v hre je 15 000 EUR pre túto kampaň (alebo ekvivalent v mene hráča), kombinovaná z viacerých zásobníkov podľa nasledujúcej štruktúry:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15 000 EUR</span> pre obdobie od 24. augusta 2023 o 0:00 UTC do 27. augusta 2023 o 23:00 UTC</div>
        </ul>


        <div class="flex ai-center mt-1">

            <table class="tournament__table"
                   style="box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                <tr>
                    <th>Quantity</th>
                    <th>Prize Value</th>
                    <th>Sum</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>15</td><td>€ 100</td><td>€ 1,500</td></tr>
                <tr><td>30</td><td>€ 60</td><td>€ 1,800</td></tr>
                <tr><td>45</td><td>€ 50</td><td>€ 2,250</td></tr>
                <tr><td>60</td><td>€ 40</td><td>€ 2,400</td></tr>
                <tr><td>80</td><td>€ 30</td><td>€ 2,400</td></tr>
                <tr><td>120</td><td>€ 20</td><td>€ 2,400</td></tr>
                <tr><td>150</td><td>€ 15</td><td>€ 2,250</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 15,000</span></td>
                </tr>
                </tbody>
            </table>

        </div>
        <img src="/img/tournaments/summer-lucky-drops/water3.png" alt="wave" style="width: 100%; margin-top: -100%; z-index: -5; height: 120px;">
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
