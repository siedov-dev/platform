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
        white-space: nowrap;
        color: #e4b556;
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
        <div class="tournament__cond-title">Money Wonderwood si svolge:</div>
        <div class="tournament__cond-text">Dal <strong>24.08.2023</strong> al <strong>30.08.2023</strong></div>

        <div class="tournament__cond-title">Termini e condizioni del Torneo 3 Oaks Network:</div>
        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/bats.png" alt="bats" style="width: 280px; height: 220px; margin-top: 10px;">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>Partecipando al torneo, accettate i Termini e le Condizioni qui elencati e quelli forniti dall'operatore.</li>
                    <li>In caso di emergenza, <strong>3 Oaks Gaming</strong> si riserva il diritto di annullare la promozione in qualsiasi momento, inclusi i pagamenti per le vincite, se ritenuto necessario.</li>
                    <li>Solo le giocate con <strong>denaro reale</strong> possono partecipare alla campagna.</li>
                    <li>Oltre al <strong>prezzo per una spin</strong>, non vi è alcun costo aggiuntivo per partecipare al torneo.</li>
                    <li>Ogni giocatore riceverà un <strong>ID personale del torneo</strong> per la classifica.</li>
                    <li>Le regole del torneo possono essere modificate in qualsiasi momento.</li>
                    <li>Un malfunzionamento annulla tutti i pagamenti.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Qualificazione:</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Torneo:</strong> Per essere qualificato e avere il diritto di reclamare il premio, il giocatore deve soddisfare le seguenti
                condizioni:
                <ol class="tournament__list">
                    <li>La <strong>puntata minima</strong> è di <span style="color: red; font-weight: bold">0,20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">e</span>
                    <li>Il <strong>numero minimo</strong> di spin è di <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Tutti i punti raccolti durante la fase di qualificazione vengono salvati e conteggiati per un giocatore dopo aver superato la qualificazione. I giocatori non qualificati sono comunque mostrati nella classifica, ma la riga per il giocatore non qualificato è strisciata in grigio fino a quando non sono qualificati.</div>
            <div>
                <img src="/img/tournaments/money-wonderwood/tree1.png" alt="leaves" style="width: 230px; margin-top: -80px; margin-left: -10px">
            </div>
        </div>

        <div class="tournament__cond-title">Meccanica delle Vincite:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> CORSA DEL MOLTIPLICATORE:</span> se il giocatore ha piazzato una puntata totale di <strong>EUR 2</strong> e ha vinto <strong> EUR 120</strong>, il giocatore otterrà 120(vincita)÷2(scommessa)= un moltiplicatore 60x = <strong>60 punti</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Montepremi:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Progressivo a partire da <span class="hot">40.000 EUR</span></div>
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
                <thead>
                <tr>
                    <th>Luogo</th>
                    <th>Premio</th>
                    <th>Somma</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>10%</td><td>10%</td></tr>
                <tr><td>2</td><td>8%</td><td>8%</td></tr>
                <tr><td>3</td><td>6%</td><td>6%</td></tr>
                <tr><td>4</td><td>5%</td><td>5%</td></tr>
                <tr><td>5</td><td>4%</td><td>4%</td></tr>
                <tr><td>6</td><td>2.5%</td><td>2.5%</td></tr>
                <tr><td>7</td><td>1.6%</td><td>1.6%</td></tr>
                <tr><td>8</td><td>0.9%</td><td>0.9%</td></tr>
                <tr><td>9</td><td>0.8%</td><td>0.8%</td></tr>
                <tr><td>10-20</td><td>0.7%</td><td>7.7%</td></tr>
                <tr><td>21-30</td><td>0.6%</td><td>6%</td></tr>
                <tr><td>31-40</td><td>0.5%</td><td>5%</td></tr>
                <tr><td>41-50</td><td>0.4%</td><td>4%</td></tr>
                <tr><td>51-100</td><td>0.3%</td><td>15%</td></tr>
                <tr><td>101-150</td><td>0.2%</td><td>10%</td></tr>
                <tr><td>151-200</td><td>0.15%</td><td>7.5%</td></tr>
                <tr><td>201-250</td><td>0.12%</td><td>6%</td></tr>
                <tr>
                    <td colspan="2"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">100%</span></td>
                </tr>
                </tbody>
            </table>

        </div>

        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/owl_tree.png" alt="owl on branch" style="width: 70%; margin-top: -0px;">
        </div>

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
