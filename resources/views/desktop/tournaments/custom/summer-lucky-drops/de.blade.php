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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title">Das Lucky Drops Event läuft:</div>
        <div class="tournament__cond-text mb-1">Vom <strong>24. bis zum 27. August 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="Möwen" style="width: 320px; height: 320px; margin-bottom: -160px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Heißer Zeitraum</span> - erhöht die Gewinnchance für Spieler:</div>
        <div class="tournament__cond-text">Beginnt am <span style="color: red; font-weight: bold">26.08.2023 0:00 UTC</span> und läuft bis zum Ende der <strong>Weekend Lucky Drops</strong> Kampagne</div>

        <div class="tournament__cond-title">3 Oaks Netzwerk Turnierbedingungen:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Durch die Teilnahme am Turnier stimmen Sie den hier aufgeführten Bedingungen sowie den vom Betreiber bereitgestellten Bedingungen zu.</li>
                <li>Im Notfall behält sich 3 Oaks Gaming das Recht vor, die Lucky Drops Aktion jederzeit abzusagen, einschließlich der Auszahlung von Gewinnen, falls dies für notwendig erachtet wird.</li>
                <li>Nur Echtgeldrunden können an der Lucky Drops Aktion teilnehmen.</li>
                <li>Neben dem Preis für einen Dreh entstehen keine zusätzlichen Kosten für die Teilnahme an den Network Lucky Drops.</li>
                <li>Jeder Spieler erhält eine persönliche Turnier-ID für die Rangliste.</li>
                <li>Die Regeln der Network Lucky Drops können jederzeit geändert werden.</li>
                <li>Bei Fehlfunktionen erlischt jegliche Auszahlung.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Qualifikation:</div>
        <div class="flex">
            <div style="">
                <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="Urlaub" style="margin-right: 1rem; width: 250px;">
            </div>
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Ein Spieler muss einem der teilnehmenden Spiele beitreten oder sich dafür entscheiden.</li>
                    <li>Der Mindesteinsatz, um einen LuckyDrop zu erhalten, beträgt <span style="color: red; font-weight: bold">0,50 EUR</span> oder der entsprechende Betrag in den verfügbaren Währungen der teilnehmenden Spiele.</li>
                    <li>Jede qualifizierende Wette in einem der teilnehmenden Spiele kann während des Aktionszeitraums einen zufälligen Preis aus dem Preispool auslösen.</li>
                    <li>Jeder Spieler kann maximal <span style="color: red; font-weight: bold">3 Preise</span> gewinnen.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Gewinnmechanik:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> bietet den Spielern die Möglichkeit, während des Aktionszeitraums jederzeit Bargeldpreise zu gewinnen. Die Preise werden zufällig vergeben, und alle Spieler haben die gleiche Chance auf einen Gewinn bei jedem qualifizierenden Spin.
            </div>
        </div>
        <div class="tournament__cond-title">Teilnehmende Spiele:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Alle Spiele</span></div>
        <div class="tournament__cond-title">Preispool:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Der Preispool für diese Kampagne beträgt 15.000 EUR (oder der entsprechende Betrag in der Währung des Spielers) und setzt sich aus mehreren Pools nach folgender Struktur zusammen:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15.000 EUR</span> für den Zeitraum vom 24.08.2023 00:00 UTC bis zum 27.08.2023 23:00 UTC</div>
        </ul>


        <div class="flex ai-center">

            <img src="/img/tournaments/summer-lucky-drops/beach_luggage.png" alt="beach" style="margin-right: 1rem; width: 450px;">

            <table class="tournament__table"
                   style="box-shadow: #00bcff 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-left: -100px;">
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
{{--        <img src="/img/tournaments/summer-lucky-drops/water3.png" alt="wave" style="width: 100%; margin-top: -100%; z-index: -5">--}}
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
