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
        <div class="tournament__cond-title mt-2 text-gold">Verabschieden Sie sich mit Stil vom Sommer!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> ist die neue Promotion von Wazdan, bei der Sie Preise aus dem riesigen
            <strong>3.000.000 Euro</strong> Preispool gewinnen können. Spielen Sie Ihre Lieblingsspiele von Wazdan und lassen Sie sich vom <span class="hot">Mystery Drop™</span> verzaubern.</div>

        <div class="tournament__cond-title">Kampagnenzeitraum:</div>
        <div class="tournament__cond-text">Vom <strong>15. September bis zum 15. Oktober 2023</strong></div>

        <div class="tournament__cond-title">Allgemeine Geschäftsbedingungen:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Die Mystery Drop-Kampagne ("Die Kampagne") ist nur im Echtgeldspielmodus verfügbar.</li>
                    <li>Teilnehmende Spieler nehmen an der Kampagne teil, indem sie während des Aktionszeitraums qualifizierte Wetten in teilnehmenden Spielen platzieren. <strong>Es entstehen keine zusätzlichen Kosten</strong>.</li>
                    <li>Spieler können während der Kampagne mehr als einen zufälligen Preis gewinnen.</li>
                    <li>Jede qualifizierte Wette in teilnehmenden Spielen während des Aktionszeitraums kann einen Preis aus dem Preispool auslösen.</li>
                    <li>Eine qualifizierte Wette kann nur einen Preis gewinnen. Preise werden während des Aktionszeitraums zufällig vergeben.</li>
                    <li><strong>Keine Mindesteinsatzanforderung</strong>. Alle Einsätze sind zur Teilnahme an der Kampagne berechtigt.</li>
                    <li>Die Anzahl der verfügbaren Preise wird sofort aktualisiert.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Alle gewonnenen Preise werden sofort auf die Casino-Konten der Spieler gutgeschrieben, sobald die Gewinnbenachrichtigung eingegangen ist.</li>
                    <li>Preise werden gemäß der Preispool-Tabelle ausgezahlt.</li>
                    <li>Der maximale Gesamtwert der Bargeldpreise beträgt <strong>500.000 Euro</strong>, aufgeteilt in <strong>5.000 Mystery Boxes</strong>.</li>
                    <li>Bargeldpreise <strong>haben KEINE Umsatzbedingungen</strong>.</li>
                    <li>Preise, die während der Verwendung von Bonusgeldern gewonnen werden, werden in Bonusgeldern ausgezahlt.</li>
                    <li>Bargeldpreise werden in der Währung ausgezahlt, in der die Gewinnwette platziert wurde, basierend auf der Währungsumrechnung von Euro zum Zeitpunkt der Platzierung der Gewinnwette.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Nur verifizierte Casino-Spieler sind berechtigt, Preise zu gewinnen.</li>
                    <li>Das Casino behält sich das Recht vor, einen Bargeldpreis nicht auszuzahlen, wenn der Gewinn auf offensichtlichen Fehlern, Irrtümern oder technischen Störungen (einschließlich falscher Spielzahlungen) beruht, unabhängig davon, ob sie durch einen Maschinen- oder menschlichen Fehler bei einem der teilnehmenden Spiele verursacht wurden. Das Casino behält sich auch das Recht vor, einen Bargeldpreis nicht auszuzahlen, wenn seiner Meinung nach der Gewinn durch Betrug oder Absprachen mit anderen Spielern erfolgt ist.</li>
                    <li>Das Casino behält sich das Recht vor, diese Promotion jederzeit aus beliebigen Gründen zu ändern, einzustellen, zu beenden oder zu beenden, und behält sich auch das Recht vor, die Bedingungen dieses Angebots jederzeit zu ändern. Frühere Bedingungen und Angebote werden für Spieler, die bis zu dem Zeitpunkt, zu dem eine Änderung des Angebots erfolgt, Ansprüche geltend machen, eingehalten.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Teilnehmende Spiele:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Alle Wazdan-Spiele</span></div>

        <div class="tournament__cond-title">Preispool:</div>
        <div class="tournament__cond-text">Der maximale Gesamtwert der Bargeldpreise beträgt <strong>500.000 Euro</strong>. Es gibt insgesamt <strong>5.000 Mystery Boxes</strong>. Jede Mystery Box kann einen Preis aus der folgenden Liste enthalten:</div>
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
