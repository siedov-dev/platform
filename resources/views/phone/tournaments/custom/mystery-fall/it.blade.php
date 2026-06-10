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
        <div class="tournament__cond-title mt-2 text-gold">Dì addio all'estate con stile!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> è la nuova promozione di Wazdan durante la quale potrai vincere premi dal massiccio
            <strong>3.000.000 €</strong> di montepremi. Gioca ai tuoi giochi preferiti di Wazdan e lascia che il <span class="hot">Mystery Drop™</span> faccia magie.</div>

        <div class="tournament__cond-title">Periodo della campagna:</div>
        <div class="tournament__cond-text">Dal <strong>15 settembre al 15 ottobre 2023</strong></div>

        <div class="tournament__cond-title">Termini e Condizioni:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>La campagna Mystery Drop ("La Campagna") è disponibile solo in modalità gioco con denaro reale.</li>
                    <li>I giocatori partecipanti prenderanno parte alla campagna effettuando scommesse qualificanti nei giochi partecipanti durante il periodo della campagna. <strong>Non ci sono costi aggiuntivi</strong>.</li>
                    <li>I giocatori possono vincere più di un premio casuale durante la campagna.</li>
                    <li>Qualsiasi scommessa qualificante nei giochi partecipanti durante il periodo della campagna può attivare un premio dal montepremi.</li>
                    <li>Una scommessa qualificante può vincere solo un premio. I premi verranno assegnati casualmente durante il periodo della campagna.</li>
                    <li><strong>Nessun requisito di scommessa minima</strong>. Tutte le scommesse sono idonee a partecipare alla campagna.</li>
                    <li>Il numero di premi disponibili viene aggiornato istantaneamente.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Tutti i premi vinti verranno accreditati istantaneamente sui conti dei giocatori del casinò al ricevimento della notifica di vincita.</li>
                    <li>I premi verranno pagati secondo la tabella dei premi.</li>
                    <li>Il valore totale massimo dei premi in denaro è di <strong>500.000 €</strong> divisi in <strong>5.000 Scatole Misteriose</strong>.</li>
                    <li>I premi in denaro <strong>non hanno requisiti di scommessa</strong>.</li>
                    <li>I premi in denaro pagati utilizzando fondi bonus verranno erogati sotto forma di fondi bonus.</li>
                    <li>I premi in denaro verranno pagati nella valuta in cui è stata effettuata la scommessa vincente, in base al cambio in euro al momento della scommessa vincente.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Solo i giocatori di casinò verificati sono idonei a vincere i premi.</li>
                    <li>Il casinò si riserva il diritto di non pagare un premio in denaro in caso di vincita risultante da errori evidenti, errori tecnici (compresi pagamenti di gioco errati), che siano dovuti a un errore di macchina o umano in uno dei giochi partecipanti. Il casinò si riserva inoltre il diritto di non pagare un premio in denaro in caso di frode o collusione con altri giocatori, a suo giudizio.</li>
                    <li>Il casinò si riserva il diritto di modificare, alterare, interrompere o terminare questa promozione in qualsiasi momento e per qualsiasi motivo, nonché di modificare i termini e le condizioni di questa offerta in qualsiasi momento. I termini e le offerte precedenti saranno rispettati per i giocatori che li richiedono fino al momento di qualsiasi modifica all'offerta.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Giochi partecipanti:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Tutti i giochi di Wazdan</span></div>

        <div class="tournament__cond-title">Montepremi:</div>
        <div class="tournament__cond-text">Il valore totale massimo dei premi in denaro è di <strong>500.000 €</strong>. Ci sono un totale di <strong>5.000 Scatole Misteriose</strong>. Ogni Scatola Misteriosa può contenere un premio dalla seguente lista:</div>
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
