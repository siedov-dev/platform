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
            <h5>Durée du tournoi</h5>
            <h4>15.05.2024 - 28.05.2024</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Fonds de prix</h5>
            <h4>€70,000</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Enchère minimum</h5>
            <h4>€0.4</h4>
        </div>
        <div class="tourn_stat_itm">
            <h5>Min. nombre de tours</h5>
            <h4>100</h4>
        </div>
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">Jeux</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">Termes et conditions</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">Prix</button>
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
                1. Evoplay Network Campaign est disponible pour jouer en mode réel uniquement et
 se composent de deux tournois qui se déroulent en séquence : Mai Tai Madness
 Tournoi et campagne de remise de prix Mai Tai Madness.<br>
 2. La campagne réseau Evoplay dure (14) jours convenus et a lieu
 entre 00h01 UTC le 15 mai 2024 et le 28 mai 2024 jusqu'à 23h59 UTC.<br>
 3. Le montant total du prix du tournoi est de 70 000 €.<br>
 4. Le classement est consolidé pour tous les opérateurs participant à ce
 campagne de réseau.<br>
 5. Tous les lots sont déduits des GGR avant facturation quel que soit le
 pourcentage de déduction du bonus selon le contrat.<br>
 6. La cagnotte et la mise minimale pour cette campagne sont fixées en EUR et
 peut être soumis à des fluctuations de conversion de devises.<br>
 7. La campagne comporte une liste de jeux qualifiés et une cagnotte (EUR), qui
 est réparti entre les gagnants selon le règlement du tournoi.<br>
 8. Chaque tour qualifié contribuera au score total du joueur qui est
 affiché dans le classement. Le score total est calculé avec les points
 gagnés au cours de tous les tours joués dans les jeux participants. Plus il y a de points, plus
 plus le joueur atteint le classement.<br>
 9. Evoplay se réserve le droit d'annuler les scores ou de ne pas payer un prix, lorsque tous
 ou une partie du score résulte d'une erreur évidente, d'une erreur ou d'un problème technique.
 faute (y compris des paiements de jeu incorrects) causée par une machine ou un humain
 erreur à l'un des jeux participants.<br>
 10. Evoplay se réserve en outre le droit d'annuler les scores ou de ne pas payer de prix.
 où, selon l'opinion, toute une partie du score résulte de tricherie ou de collusion
 avec d'autres joueurs.<br>
 11. Le système de rémunération est calculé en fonction de la cagnotte
 et définir le nombre de dirigeants participants.<br>
 12. Un casino doit soutenir le tournoi en mettant en œuvre la liste des
 activités de marketing indiquées ci-dessous.<br>
 13. Les joueurs recevront une annonce contextuelle concernant le début de la
 tournoi deux (2) jours avant le tournoi.<br>
 14. Soumettez votre participation au tournoi minimum sept (7) jours
 avant le début du tournoi.
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            <h4 style="color: #fff; margin-bottom: 15px;">Tournoi de la folie Mai Tai</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0;">
                <thead>
                <tr>
                    <th>Lieu</th>
                    <th>Prix</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 3500</td></tr>
                <tr><td>2</td><td>€ 2800</td></tr>
                <tr><td>3</td><td>€ 2450</td></tr>
                <tr><td>4</td><td>€ 2100</td></tr>
                <tr><td>5</td><td>€ 1750</td></tr>
                <tr><td>6</td><td>€ 1400</td></tr>
                <tr><td>7</td><td>€ 1155</td></tr>
                <tr><td>8</td><td>€ 980</td></tr>
                <tr><td>9</td><td>€ 910</td></tr>
                <tr><td>10</td><td>€ 455</td></tr>
                <tr><td>11-20</td><td>€ 280</td></tr>
                <tr><td>21-50</td><td>€ 140</td></tr>
                <tr><td>51-100</td><td>€ 105</td></tr>
                <tr><td>101-150</td><td>€ 70</td></tr>
                <tr><td>151-200</td><td>€ 35</td></tr>
                <tr>
                    <td colspan="1"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">TOTAL 35,000 EUR</span></td>
                </tr>
                </tbody>
            </table>
            <h4 style="margin-top: 30px; color: #fff; margin-bottom: 15px;">Campagne de remise des prix Mai Tai Madness</h4>
            <table class="tournament__table" style="box-shadow: #ffb500 0 0 7px; z-index: 2; background-color: #0B0D31C0; ">
                <thead>
                <tr>
                    <th>Quantité</th>
                    <th>Prix</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>€ 400</td></tr>
                <tr><td>2</td><td>€ 200</td></tr>
                <tr><td>7</td><td>€ 100</td></tr>
                <tr><td>10</td><td>€ 80</td></tr>
                <tr><td>20</td><td>€ 40</td></tr>
                <tr><td>60</td><td>€ 15</td></tr>
                <tr><td>100</td><td>€ 10</td></tr>
                <tr>
                    <td colspan="1"></td>
                    <td><span style="color: red; text-transform: uppercase; font-weight: bold">
                        TOTAL 35 000 EUR : 5 000 EUR/200 prix par jour</span></td>
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
