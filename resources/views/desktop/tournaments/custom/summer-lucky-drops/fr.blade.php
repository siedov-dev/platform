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
        <div class="tournament__cond-title">L'événement Lucky Drops se déroule :</div>
        <div class="tournament__cond-text mb-1">Du <strong>24 au 27 août 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="mouettes" style="width: 320px; height: 320px; margin-bottom: -160px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Période Chaude</span> - augmente les chances de gagner d'un joueur :</div>
        <div class="tournament__cond-text">Débute le <span style="color: red; font-weight: bold">26 août 2023 à 0h00 UTC</span> jusqu'à la fin de la campagne <strong>Weekend Lucky Drops</strong></div>

        <div class="tournament__cond-title">Termes et Conditions du Tournoi 3 Oaks Network :</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>En participant au tournoi, vous acceptez les Termes et Conditions énoncés ici et ceux fournis par l'opérateur.</li>
                <li>En cas d'urgence, 3 Oaks Gaming se réserve le droit d'annuler la promotion Lucky Drops à tout moment, y compris les paiements pour les gains, si cela est jugé nécessaire.</li>
                <li>Seules les parties en argent réel peuvent participer à la campagne Lucky Drops.</li>
                <li>En dehors du coût d'un tour, il n'y a pas de frais supplémentaires pour participer aux Network Lucky Drops.</li>
                <li>Chaque joueur recevra un ID de tournoi personnel pour le classement.</li>
                <li>Les règles des Network Lucky Drops peuvent être modifiées à tout moment.</li>
                <li>Toute défaillance annule tous les paiements.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Qualification :</div>
        <div class="flex">
            <div style="">
                <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacances" style="margin-right: 1rem; width: 250px;">
            </div>
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Un joueur doit rejoindre ou opter pour l'un des jeux participants.</li>
                    <li>La mise minimale requise pour pouvoir recevoir un LuckyDrop est de <span style="color: red; font-weight: bold">0,50 EUR</span> ou le montant équivalent dans les devises disponibles dans le(s) jeu(x) participant(s).</li>
                    <li>Chaque mise qualificative placée sur l'un des jeux participants peut déclencher un prix aléatoire à partir du pool de prix pendant la période de promotion.</li>
                    <li>Chaque joueur peut remporter un maximum de <span style="color: red; font-weight: bold">3 prix</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Mécanique de Gagne :</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> offre aux joueurs la possibilité de gagner des prix en espèces à tout moment pendant la période de l'événement. Les prix sont attribués de manière aléatoire, et tous les joueurs ont la même chance de gagner à chaque tour qualificatif.
            </div>
        </div>
        <div class="tournament__cond-title">Jeux participants :</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Tous les jeux</span></div>
        <div class="tournament__cond-title">Pool de Prix :</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Le Pool de Prix est de 15 000 EUR pour cette Campagne (ou l'équivalent dans la devise du joueur) combiné à partir de plusieurs pools selon la structure suivante :</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15 000 EUR</span> pour la période du 24 août 2023 à 00h00 UTC au 27 août 2023 à 23h00 UTC</div>
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
