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
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #8888af;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
    .tournament__image {
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tourn_stats{
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 24%;
        padding: 20px 0;
        border-radius: 10px;
    }
    .tourn_stat_itm h5{
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
    }
    .tourn_stat_itm h4{
        color: #FFC660;
        font-size: 16px
    }
    .tabs_tour{
        display: flex;
        background: #181833;
        margin-top: 30px;
        padding: 5px;
        justify-content: space-between;
    }
    .tabs_tour button{
        width: 32%;
        background: transparent;
        border: none;
        padding: 7px;
        border-radius: 5px;
        color: #8C90CA;
        cursor: pointer;
    }
    .tabs_tour button.active{
        background: #2B2F5B;
        color: #fff;
    }
    .tabs_tour button:hover{
        background: #2B2F5B;
        color: #fff;
    }
    .panes_tour{
        margin-top: 30px;
    }
    .panes_tour_itm{
        display: none;
    }
    .panes_tour_itm.active{
        display: block;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">
        <div class="tourn_stat_itm">
            <h5>Duração do torneio</h5>
            <h4>22.05.2024 - 27.05.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Fundo de prêmios</h5>
            <h4>€50,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Lance mínimo</h5>
            <h4>€0.5</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Min. número de giros</h5>
            <h4>100</h4>
        </div>
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">Jogos</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">Termos e Condições</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">Prêmios</button>
    </div>
    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
            <div class="tournament__games">
                <div class="tournament__games-head">
                    <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
                    <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
                </div>
                <div class="tournament__games-list">
                    @foreach($tournament->getAllowedGames() as $game)
                        @include('desktop.games.single-game', ['game' => $game])
                    @endforeach
                </div>
            </div>
        </div>
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                1. Ao participar do torneio, você concorda com os Termos e Condições aqui descritos e com os fornecidos pelo
                operador.<br>
                2. Em casos de emergência, a 3 Oaks Gaming reserva-se o direito de cancelar a promoção Coin Mania a qualquer momento, incluindo pagamentos de
                ganhos, se considerado necessário.<br>
                3. Somente jogadores qualificados podem reivindicar o prêmio (verifique a seção de detalhes da campanha).<br>
                4. Apenas rodadas com dinheiro real podem participar do torneio.<br>
                5. Além do preço para fazer um giro, não há custo adicional para entrar no torneio.<br>
                6. Cada jogador receberá um ID de torneio pessoal para a tabela de classificação.<br>
                7. Se dois ou mais jogadores estiverem empatados, os jogadores que obtiverem a pontuação primeiro desempatarão.<br>
                8. As regras do torneio podem ser alteradas a qualquer momento.<br>
                9. O mau funcionamento anula todos os pagamentos.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;">REGULAR</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                    <tr>
                        <th>Place</th>
                        <th>Prize</th>
                        <th>Sum</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>€ 1000</td><td>€ 1000</td></tr>
                    <tr><td>2</td><td>€ 750</td><td>€ 750</td></tr>
                    <tr><td>3</td><td>€ 600</td><td>€ 600</td></tr>
                    <tr><td>4</td><td>€ 500</td><td>€ 500</td></tr>
                    <tr><td>5</td><td>€ 400</td><td>€ 400</td></tr>
                    <tr><td>6 - 10</td><td>€ 200</td><td>€ 1000</td></tr>
                    <tr><td>11 - 20</td><td>€ 100</td><td>€ 1000</td></tr>
                    <tr><td>21 - 50</td><td>€ 75</td><td>€ 2250</td></tr>
                    <tr><td>51 - 100</td><td>€ 30</td><td>€ 1500</td></tr>
                    <tr><td>101 - 150</td><td>€ 200</td><td>€ 1000</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 10000 x 3 periods = € 30000</span></td>
                    </tr>
                    </tbody>
            </table>
            <h4 style="margin-top: 30px; color: #fff; margin-bottom: 15px;">SERIES</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0; ">
                <thead>
                    <tr>
                        <th>Place</th>
                        <th>Prize</th>
                        <th>Sum</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>€ 2000</td><td>€ 2000</td></tr>
                    <tr><td>2</td><td>€ 1500</td><td>€ 1500</td></tr>
                    <tr><td>3</td><td>€ 1000</td><td>€ 1000</td></tr>
                    <tr><td>4</td><td>€ 750</td><td>€ 750</td></tr>
                    <tr><td>5</td><td>€ 600</td><td>€ 600</td></tr>
                    <tr><td>6 - 9</td><td>€ 500</td><td>€ 2000</td></tr>
                    <tr><td>10 - 17</td><td>€ 400</td><td>€ 3200</td></tr>
                    <tr><td>18 - 25</td><td>€ 300</td><td>€ 2400</td></tr>
                    <tr><td>26 - 41</td><td>€ 100</td><td>€ 1600</td></tr>
                    <tr><td>42 - 66</td><td>€ 50</td><td>€ 1250</td></tr>
                    <tr><td>67 - 91</td><td>€ 40</td><td>€ 1000</td></tr>
                    <tr><td>92 - 143</td><td>€ 30</td><td>€ 1560</td></tr>
                    <tr><td>144 - 200</td><td>€ 20</td><td>€ 1140</td></tr>
                    <tr>
                        <td colspan="2"></td>
                        <td><span style="color: red; text-transform: uppercase; font-weight: bold">€ 20000</span></td>
                    </tr>
                    </tbody>
            </table>
        </div>
    </div>




    <script>
        document.getElementById("pt_games").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_games");
            pane.classList.add('active');

            let but = document.getElementById("pt_games");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
        document.getElementById("pt_terms").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_terms");
            pane.classList.add('active');

            let but = document.getElementById("pt_terms");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
        document.getElementById("pt_prizes").addEventListener("click", function(){
            console.log('asdasd');

            let allElements = document.querySelectorAll('.panes_tour_itm');
            allElements.forEach((element) => {
                element.classList.remove('active');
            });

            let allElement = document.querySelectorAll('.tabs_tourbtn');
            allElement.forEach((el) => {
                el.classList.remove('active');
            });

            let pane = document.getElementById("pn_prizes");
            pane.classList.add('active');

            let but = document.getElementById("pt_prizes");
            but.classList.add('active');
            //panes_tour_itm.pane_

        });
    </script>
    @include('desktop.footer')

</div>
