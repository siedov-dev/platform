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
        <div class="tournament__cond-title">Money Wonderwood läuft:</div>
        <div class="tournament__cond-text">Vom <strong>24.08.2023</strong> bis <strong>30.08.2023</strong></div>

        <img src="/img/tournaments/money-wonderwood/tree2.png" alt="bats" style="width: 100%;">

        <div class="tournament__cond-title">3 Oaks Network Turnierbedingungen:</div>
        <div class="flex">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>Durch die Teilnahme am Turnier stimmen Sie den hier aufgeführten Geschäftsbedingungen und den vom Betreiber bereitgestellten Bedingungen zu.</li>
                    <li>In Notfällen behält sich <strong>3 Oaks Gaming</strong> das Recht vor, das Promo jederzeit abzusagen, einschließlich Zahlungen für Gewinne, wenn dies für notwendig erachtet wird.</li>
                    <li>Nur Runden mit <strong>echtem Geld</strong> können an der Kampagne teilnehmen.</li>
                    <li>Abgesehen vom <strong>Preis für einen Spin</strong> entstehen keine zusätzlichen Kosten für die Teilnahme am Turnier.</li>
                    <li>Jeder Spieler erhält eine <strong>persönliche Turnier-ID</strong> für die Rangliste.</li>
                    <li>Turnierregeln können jederzeit geändert werden.</li>
                    <li>Funktionsstörungen führen zu einer Annullierung aller Auszahlungen.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Qualifikation:</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Turnier:</strong> Um sich zu qualifizieren und Anspruch auf den Preis zu haben, muss der Spieler folgende
                Bedingungen erfüllen:
                <ol class="tournament__list">
                    <li><strong>Mindesteinsatz</strong> beträgt <span style="color: red; font-weight: bold">0,20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">und</span>
                    <li><strong>Mindestanzahl</strong> der Spins beträgt <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Alle während der Qualifikationsphase gesammelten Punkte werden nach Bestehen der Qualifikation für einen Spieler gespeichert und gezählt. Nicht qualifizierte Spieler werden ebenfalls in der Rangliste angezeigt, jedoch wird die Zeile für den nicht qualifizierten Spieler grau gestreift, bis sie qualifiziert sind.</div>
        </div>

        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/owl_tree.png" alt="owl on branch" style="width: 100%; margin-top: -0px;">
        </div>

        <div class="tournament__cond-title">Gewinnmechanik:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: Uppercase; Font-Weight: Bold"> MULTIPLIER-RENNEN:</span> wenn der Spieler einen Gesamteinsatz von <strong>EUR 2</strong> platziert und <strong> gewonnen hat 120 EUR</strong>, der Spieler erhält 120 (Gewinn) ÷ 2 (Einsatz) = ein 60-facher Multiplikator = <strong>60 Punkte</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Preispool:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Progressiv ab <span class="hot">EUR 40.000</span></div>
        </ul>



        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 10px; width: 100%'>
                <thead>
                <tr>
                    <th>Ort</th>
                    <th>Preis</th>
                    <th>Summe</th>
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
