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
        <div class="tournament__cond-title mt-2 text-gold">¡Dile adiós al verano con estilo!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> es la nueva promoción de Wazdan durante la cual podrás ganar recompensas del masivo
            <strong>€3.000.000</strong> de premio. Juega tus juegos favoritos de Wazdan y deja que el <span class="hot">Mystery Drop™</span> haga maravillas.</div>

        <div class="tournament__cond-title">Período de la campaña:</div>
        <div class="tournament__cond-text">Desde el <strong>15 de septiembre hasta el 15 de octubre de 2023</strong></div>

        <div class="tournament__cond-title">Términos y condiciones:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>La campaña Mystery Drop ("La Campaña") solo está disponible en el modo Jugar con dinero real.</li>
                    <li>Los jugadores participantes formarán parte de la campaña al realizar una apuesta calificativa en los juegos participantes durante el período de la campaña. <strong>No hay costos adicionales</strong>.</li>
                    <li>Los jugadores pueden ganar más de un premio al azar durante la campaña.</li>
                    <li>Cualquier apuesta calificativa en los juegos participantes durante el período de la campaña puede desencadenar un premio del premio total.</li>
                    <li>Una apuesta calificativa solo puede ganar un premio. Los premios se otorgarán de forma aleatoria durante el período de la campaña.</li>
                    <li><strong>Sin requisitos de tamaño mínimo de apuesta</strong>. Todas las apuestas califican para participar en la campaña.</li>
                    <li>El número de premios disponibles se actualiza al instante.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 300px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe mt-1">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 200px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Todos los premios ganados se acreditarán instantáneamente en las cuentas de casino de los jugadores al recibir la notificación de ganancia.</li>
                    <li>Los premios se pagarán según la tabla de premios.</li>
                    <li>El valor total máximo de los premios en efectivo es de <strong>€500.000</strong> divididos en <strong>5.000 Cajas Misteriosas</strong>.</li>
                    <li>Los premios en efectivo <strong>no tienen requisitos de apuesta</strong>.</li>
                    <li>Los premios pagados con fondos de bonificación se entregarán en forma de fondos de bonificación.</li>
                    <li>Los premios en efectivo se pagarán en la moneda en la que se realizó la apuesta ganadora, según la conversión a euros en el momento de la apuesta ganadora.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Solo los jugadores de casino verificados son elegibles para ganar los premios.</li>
                    <li>El casino se reserva el derecho de no pagar un premio en efectivo cuando la ganancia resulta de algún error evidente, equivocación o fallo técnico (incluyendo pagos incorrectos de juegos), ya sea causado por error de máquina o humano en relación con cualquiera de los juegos participantes. El casino se reserva también el derecho de no pagar un premio en efectivo cuando, en su opinión, la ganancia resulta de trampas o colaboración con otros jugadores.</li>
                    <li>El casino se reserva el derecho de modificar, alterar, interrumpir o finalizar esta promoción en cualquier momento y por cualquier motivo, y se reserva también el derecho de cambiar los términos y condiciones de esta oferta en cualquier momento. Se respetarán los términos y ofertas anteriores para los jugadores que hagan reclamaciones hasta el momento de cualquier cambio en la oferta.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Juegos participantes:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Todos los juegos de Wazdan</span></div>

        <div class="tournament__cond-title">Premio total:</div>
        <div class="tournament__cond-text">El valor total máximo de los premios en efectivo es de <strong>€500.000</strong>. Hay un total de <strong>5.000 Cajas Misteriosas</strong>. Cada Caja Misteriosa puede recompensar un premio de la siguiente lista:</div>
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
                @include('desktop.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('desktop.footer')

</div>
</div>
