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
        <div class="tournament__cond-title">Money Wonderwood se déroule :</div>
        <div class="tournament__cond-text">Du <strong>24.08.2023</strong> au <strong>30.08.2023</strong></div>

        <div class="tournament__cond-title">Conditions du Tournoi Réseau 3 Oaks :</div>
        <div class="flex">
            <img src="/img/tournaments/money-wonderwood/bats.png" alt="bats" style="width: 280px; height: 220px; margin-top: 10px;">
            <div class="tournament__cond-text ml-2">
                <ul class="tournament__list">
                    <li>En participant au tournoi, vous <strong>acceptez</strong> les conditions énoncées ici et celles fournies par l'opérateur.</li>
                    <li>En cas d'urgence, <strong>3 Oaks Gaming</strong> se réserve le droit d'annuler la promotion à tout moment, y compris les paiements des gains, si cela est jugé nécessaire.</li>
                    <li>Seules les parties en <strong>argent réel</strong> peuvent participer à la campagne.</li>
                    <li>En dehors du <strong>prix d'un tour</strong>, il n'y a aucun coût supplémentaire pour participer au tournoi.</li>
                    <li>Chaque joueur recevra un <strong>ID de tournoi personnel</strong> pour le classement.</li>
                    <li>Les règles du tournoi peuvent être modifiées à tout moment.</li>
                    <li>Un dysfonctionnement annule tous les paiements.</li>
                </ul>
            </div>
        </div>

        <div class="tournament__cond-title">Qualification :</div>
        <div class="flex">
            <div class="tournament__cond-text"><strong>Tournoi :</strong> Pour être qualifié et avoir le droit de réclamer le prix, le joueur doit remplir les conditions suivantes :
                <ol class="tournament__list">
                    <li>La <strong>mise minimale</strong> est de <span style="color: red; font-weight: bold">0,20 EUR,</span></li>
                    <span style="color: white; text-transform: uppercase; font-weight: bold">et</span>
                    <li>Le <strong>nombre minimal</strong> de rotations est de <span style="color: red; font-weight: bold">150</span>.</li>
                </ol>
                Tous les points collectés pendant la phase de qualification sont enregistrés et comptabilisés pour un joueur après avoir passé la qualification. Les joueurs non qualifiés sont également affichés dans le classement, mais la ligne pour le joueur non qualifié est rayée en gris jusqu'à ce qu'ils soient qualifiés.</div>
            <div>
                <img src="/img/tournaments/money-wonderwood/tree1.png" alt="leaves" style="width: 230px; margin-top: -80px; margin-left: -10px">
            </div>
        </div>

        <div class="tournament__cond-title">Mécanique de Gain :</div>
        <div class="tournament__cond-text">
            <div class="tournament__list">
                <span style="color: red; text-transform: uppercase; font-weight: bold"> COURSE MULTIPLICATEUR :</span> si le joueur a placé un pari total de <strong>EUR 2</strong> et a gagné <strong> 120 EUR</strong>, le joueur recevra 120(win)÷2(bet)= un multiplicateur de 60x = <strong>60 points</strong>.
            </div>
        </div>

        <div class="tournament__cond-title">Dotation :</div>

        <ul class="tournament__list">
            <div class="tournament__cond-text">Progressif à partir de <span class="hot">40 000 EUR</span></div>
        </ul>


        <div class="flex ai-center mt-2">

            <table class="tournament__table"
                   style='box-shadow: #00d637 0 0 7px; z-index: 2; background-color: #0B0D31C0; margin-top: 70px; width: 60%'>
                <thead>
                <tr>
                    <th>Lieu</th>
                    <th>Prix</th>
                    <th>Somme</th>
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
