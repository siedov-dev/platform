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
        <div class="tournament__cond-title">O evento Lucky Drops está em andamento:</div>
        <div class="tournament__cond-text mb-1">De <strong>24 a 27 de agosto de 2023</strong></div>

        <div style="display: flex; margin-top: -200px; justify-content: flex-end;">
            <img src="/img/tournaments/summer-lucky-drops/sky_seaguls.png" alt="gaivotas" style="width: 320px; height: 320px; margin-bottom: -160px;">
        </div>

        <div class="tournament__cond-title"><span style="color: red; font-weight: bold">Período Quente</span> - aumenta a chance do jogador de ganhar um prêmio:</div>
        <div class="tournament__cond-text">Começa em <span style="color: red; font-weight: bold">26 de agosto de 2023, às 0h00 UTC</span> até o final da campanha <strong>Weekend Lucky Drops</strong></div>

        <div class="tournament__cond-title">Termos e Condições do Torneio 3 Oaks Network:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Ao participar do torneio, você concorda com os Termos e Condições aqui estabelecidos e com os fornecidos pelo operador.</li>
                <li>Em casos de emergência, a 3 Oaks Gaming reserva-se o direito de cancelar a promoção Lucky Drops a qualquer momento, incluindo pagamentos por ganhos, se considerado necessário.</li>
                <li>Apenas rodadas com dinheiro real podem participar da campanha Lucky Drops.</li>
                <li>Além do custo para realizar uma rodada, não há custos adicionais para entrar nos Network Lucky Drops.</li>
                <li>Cada jogador receberá um ID de torneio pessoal para a classificação.</li>
                <li>As regras dos Network Lucky Drops podem ser alteradas a qualquer momento.</li>
                <li>Falhas anulam todos os pagamentos.</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Qualificação:</div>
        <div class="flex">
            <div style="">
                <img src="/img/tournaments/summer-lucky-drops/vacation.png" alt="férias" style="margin-right: 1rem; width: 250px;">
            </div>
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Um jogador deve ingressar ou optar por um dos jogos participantes.</li>
                    <li>A aposta mínima necessária para poder receber um LuckyDrop é de <span style="color: red; font-weight: bold">0,50 EUR</span> ou o valor equivalente nas moedas disponíveis nos jogos participantes.</li>
                    <li>Cada aposta qualificatória feita em um dos jogos participantes pode acionar aleatoriamente um prêmio do pool de prêmios durante o período da promoção.</li>
                    <li>Cada jogador pode ganhar no máximo <span style="color: red; font-weight: bold">3 prêmios</span>.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Mecânica de Ganho:</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold">Lucky Drops -</span> oferece aos jogadores a possibilidade de ganhar prêmios em dinheiro a qualquer momento durante o período do evento. Os prêmios são concedidos aleatoriamente, e todos os jogadores têm a mesma chance de ganhar em cada rodada qualificatória.
            </div>
        </div>
        <div class="tournament__cond-title">Jogos Participantes:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Todos os jogos</span></div>
        <div class="tournament__cond-title">Prêmio em Jogo:</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">O Prêmio em Jogo é de 15.000 EUR para esta Campanha (ou o equivalente na moeda do jogador), combinado de vários pools de acordo com a seguinte estrutura:</div>
            <div class="tournament__cond-text"><span style="color: red; font-weight: bold">15.000 EUR</span> para o período de 24 de agosto de 2023, às 00h00 UTC, a 27 de agosto de 2023, às 23h00 UTC</div>
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
