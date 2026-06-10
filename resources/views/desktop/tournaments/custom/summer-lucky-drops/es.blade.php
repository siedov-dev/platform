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
        <div class="tournament__cond-title">El evento Lucky Drops está en curso:</div>
        <div class="tournament__cond-text mb-1">Del <strong>24 al 27 de agosto de 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="gaviotas" style="width: 320px; height: 320px; margin-bottom: -160px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Período Caliente</span> - aumenta la posibilidad del jugador de ganar un premio:</div>
        <div class="tournament__cond-text">Comienza desde el <span style="color: red; font-weight: bold">26 de agosto de 2023 a las 0:00 UTC</span> hasta el final de la campaña <strong>Weekend Lucky Drops</strong></div>

        <div class="tournament__cond-title">Términos y Condiciones del Torneo 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Al participar en el torneo, aceptas los Términos y Condiciones aquí establecidos y los proporcionados por el operador.</li>
                <li>En casos de emergencia, 3 Oaks Gaming se reserva el derecho de cancelar la promoción Lucky Drops en cualquier momento, incluidos los pagos por ganancias, si se considera necesario.</li>
                <li>Solo las rondas con dinero real pueden participar en la campaña Lucky Drops.</li>
                <li>Además del costo para realizar una tirada, no hay costos adicionales para ingresar a los Network Lucky Drops.</li>
                <li>Cada jugador recibirá un ID de torneo personal para la clasificación.</li>
                <li>Las reglas de los Network Lucky Drops pueden cambiarse en cualquier momento.</li>
                <li>Fallas anulan todos los pagos.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Calificación:</div>
        <div class="flex">
            <div style="">
                <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="vacaciones" style="margin-right: 1rem; width: 250px;">
            </div>
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Un jugador debe unirse o optar por uno de los juegos participantes.</li>
                    <li>La apuesta mínima requerida para poder recibir un LuckyDrop es de <span style="color: red; font-weight: bold">0,50 EUR</span> o el equivalente en las monedas disponibles en los juegos participantes.</li>
                    <li>Cada apuesta que califique realizada en uno de los juegos participantes puede activar aleatoriamente un premio del pozo de premios durante el período de promoción.</li>
                    <li>Cada jugador puede ganar un máximo de <span style="color: red; font-weight: bold">3 premios</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Mecánica de Ganancia:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> ofrece a los jugadores la posibilidad de ganar premios en efectivo en cualquier momento durante el período del evento. Los premios se otorgan al azar, y todos los jugadores tienen la misma posibilidad de ganar en cada tirada que califica.
            </div>
        </div>
        <div class="tournament__cond-title">Juegos Participantes:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Todos los juegos</span></div>
        <div class="tournament__cond-title">Premio en Juego:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">El Premio en Juego es de 15.000 EUR para esta Campaña (o su equivalente en la moneda del jugador), combinado de varios pozos según la siguiente estructura:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15.000 EUR</span> para el período del 24 de agosto de 2023 a las 00:00 UTC al 27 de agosto de 2023 a las 23:00 UTC</div>
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
