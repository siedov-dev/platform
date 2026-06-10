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
        <div class="tournament__cond-title">Money Wonderwood se lleva a cabo:</div>
        <div class="tournament__cond-text">Desde el <strong>24.08.2023</strong> hasta el <strong>30.08.2023</strong></div>
        <div class="tournament__cond-text">La mecánica del <span class="hot">Período Caliente</span> aumenta la posibilidad de que el jugador gane un premio Lucky Drop.
            Comienza el <strong>19.08.2023 a las 15:00 UTC</strong> y continúa hasta que todos los premios Lucky Drops hayan sido otorgados.</div>

        <div class="tournament__cond-title">Términos y Condiciones del 3 Oaks Network Tournament:</div>
        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/bats.png" alt="bats" style="width: 280px; height: 220px; margin-top: 10px;">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>Al participar en el torneo, usted <strong>acepta</strong> los Términos y Condiciones aquí enumerados y los proporcionados por el operador.</li>
                    <li>En casos de emergencia, <strong>3 Oaks Gaming</strong> se reserva el derecho de cancelar la promoción en cualquier momento, incluidos los pagos por ganancias, si se considera necesario.</li>
                    <li>Solo las rondas con <strong>dinero real</strong> pueden participar en la campaña.</li>
                    <li>Además del <strong>precio para realizar una tirada</strong>, no hay costos adicionales para participar en el torneo.</li>
                    <li>Cada jugador recibirá un <strong>ID de torneo personal</strong> para la clasificación.</li>
                    <li>Las reglas del torneo pueden cambiar en cualquier momento.</li>
                    <li>Las fallas técnicas anulan todos los pagos.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Calificación:</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Torneo:</strong> Para calificar y tener derecho al premio, el jugador debe cumplir las siguientes
                condiciones:
                <ol class="tournament__list">
                    <li>La <strong>apuesta mínima</strong> es de <span style="color: red; font-weight: bold">0,20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">y</span>
                    <li>El <strong>número mínimo</strong> de giros es de <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Todos los puntos acumulados durante la fase de calificación se guardan y se cuentan para un jugador después de pasar la calificación. Los jugadores no calificados también se muestran en la clasificación, pero la línea para el jugador no calificado está rayada en gris hasta que estén calificados.</div>
            <div>
                <img src="/img/tournaments/money-wonderwood/tree1.png" alt="leaves" style="width: 230px; margin-top: -80px; margin-left: -10px">
            </div>
        </div>

        <div class="tournament__cond-title">Mecánica de Premios:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span class="hot">Torneo:</span> <strong>HSRM</strong> (Mayor Multiplicador de Ronda Única): los jugadores <strong>obtienen puntos</strong> en función de lograr el <strong>multiplicador más alto en una sola ronda</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Fondo de Premios:</div>

        <ul class="tournament__list">
            <span style="color: red; text-transform: uppercase; font-weight: bold"> CARRERA MULTIPLICADORA:</span> si el jugador realizó una apuesta total de <strong>2 EUR</strong> y ganó <strong> 120 EUR</strong>, el jugador obtendrá 120 (ganancia) ÷ 2 (apuesta) = un multiplicador de 60x = <strong>60 puntos</strong>.
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
                <thead>
                <tr>
                    <th>Lugar</th>
                    <th>Premio</th>
                    <th>Suma</th>
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
