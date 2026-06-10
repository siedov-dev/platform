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
        <div class="tournament__cond-title">L'evento Lucky Drops è in corso:</div>
        <div class="tournament__cond-text mb-1">Dal <strong>24 al 27 agosto 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="seaguls" style="width: 180px; height: 160px; margin-bottom: 10px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Periodo Caldo</span> - aumenta la possibilità del giocatore di vincere un premio:</div>
        <div class="tournament__cond-text">Inizia dal <span style="color: red; font-weight: bold">26 agosto 2023 0:00 UTC</span> fino alla fine della campagna <strong>Weekend Lucky Drops</strong></div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/cocktail3.png" alt="cocktail" style="width: 150px;">
        </div>

        <div class="tournament__cond-title">Termini e Condizioni del Torneo 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Partecipando al torneo accetti i Termini e le Condizioni indicate qui e quelli forniti dall'operatore.</li>
                <li>In caso di emergenza, 3 Oaks Gaming si riserva il diritto di annullare la promozione Lucky Drops in qualsiasi momento, inclusi i pagamenti per le vincite, se ritenuto necessario.</li>
                <li>Solo le partite con denaro reale possono partecipare alla campagna Lucky Drops.</li>
                <li>Oltre al costo per effettuare una giocata, non vi sono costi aggiuntivi per entrare nei Network Lucky Drops.</li>
                <li>Ogni giocatore riceverà un ID di torneo personale per la classifica.</li>
                <li>Le regole dei Network Lucky Drops possono essere cambiate in qualsiasi momento.</li>
                <li>Il malfunzionamento annulla tutte le vincite.</li>
            </ul>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacation" style="width: 250px;">
        </div>
        <div class="tournament__cond-title">Qualificazione:</div>
        <div class="flex">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Un giocatore deve unirsi o optare per uno dei giochi partecipanti.</li>
                    <li>La puntata minima richiesta per poter ricevere un LuckyDrop è di <span style="color: red; font-weight: bold">0,50 EUR</span> o l'equivalente importo nelle valute disponibili nei giochi partecipanti.</li>
                    <li>Ogni puntata qualificante effettuata su uno dei giochi partecipanti può attivare casualmente un premio dal montepremi durante il periodo promozionale.</li>
                    <li>Ogni giocatore può vincere un massimo di <span style="color: red; font-weight: bold">3 premi</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Meccanica delle Vittorie:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> offre ai giocatori la possibilità di vincere premi in denaro in qualsiasi momento durante il periodo dell'evento. I premi sono assegnati casualmente e tutti i giocatori hanno la stessa possibilità di vincere in ogni giocata qualificante.
            </div>
        </div>

        <img src="/img/tournaments/summer-lucky-drops/man_hammok.png" alt="beach" style="width: 100%;">

        <div class="tournament__cond-title">Giochi Partecipanti:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Tutti i giochi</span></div>
        <div class="tournament__cond-title">Montepremi:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Il Montepremi è di 15.000 EUR per questa Campagna (o l'equivalente nella valuta del giocatore) combinato da diversi pool secondo la seguente struttura:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15.000 EUR</span> per il periodo dal 24 agosto 2023 00:00 UTC al 27 agosto 2023 23:00 UTC</div>
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
