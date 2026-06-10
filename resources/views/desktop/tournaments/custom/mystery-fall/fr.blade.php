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
        <div class="tournament__cond-title mt-2 text-gold">Dites au revoir à l'été avec style !</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> est la nouvelle promotion de Wazdan au cours de laquelle vous pourrez remporter des récompenses du massif
            <strong>3 000 000 €</strong> de cagnotte. Jouez à vos jeux préférés de Wazdan et laissez le <span class="hot">Mystery Drop™</span> faire des merveilles.</div>

        <div class="tournament__cond-title">Période de la campagne :</div>
        <div class="tournament__cond-text">Du <strong>15 septembre au 15 octobre 2023</strong></div>

        <div class="tournament__cond-title">Termes et conditions :</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>La campagne Mystery Drop ("La campagne") est uniquement disponible en mode argent réel.</li>
                    <li>Les joueurs participants prendront part à la campagne en plaçant une mise éligible dans les jeux participants pendant la période de la campagne. <strong>Aucun coût supplémentaire n'est requis</strong>.</li>
                    <li>Les joueurs peuvent remporter plus d'un prix aléatoire pendant la campagne.</li>
                    <li>Toute mise éligible dans les jeux participants pendant la période de la campagne peut déclencher un prix dans la cagnotte.</li>
                    <li>Une mise éligible ne peut remporter qu'un seul prix. Les prix seront attribués de manière aléatoire tout au long de la période de la campagne.</li>
                    <li><strong>Aucune exigence de mise minimale</strong>. Toutes les mises sont éligibles pour participer à la campagne.</li>
                    <li>Le nombre de prix disponibles est mis à jour instantanément.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 300px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe mt-1">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 200px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Tous les prix gagnés seront crédités instantanément sur les comptes des joueurs de casino dès réception de la notification de gain.</li>
                    <li>Les prix seront payés selon le tableau des prix.</li>
                    <li>La valeur totale maximale des prix en espèces est de <strong>500 000 €</strong> répartis en <strong>5 000 boîtes mystères</strong>.</li>
                    <li>Les prix en espèces <strong>ne sont soumis à AUCUNE condition de mise</strong>.</li>
                    <li>Les prix en espèces payés en utilisant des fonds bonus seront payés sous forme de fonds bonus.</li>
                    <li>Les prix en espèces seront payés dans la devise dans laquelle la mise gagnante a été placée, selon la conversion en euros au moment de la mise gagnante.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Seuls les joueurs de casino vérifiés sont éligibles pour remporter les prix.</li>
                    <li>Le casino se réserve le droit de ne pas verser de prix en espèces en cas de gain résultant d'une erreur évidente, d'une erreur technique (y compris des paiements de jeu incorrects), qu'elle soit due à une erreur de machine ou humaine, dans le cadre de l'un des jeux participants. Le casino se réserve également le droit de ne pas verser de prix en espèces en cas de tricherie ou de collusion avec d'autres joueurs, selon son avis.</li>
                    <li>Le casino se réserve le droit de modifier, de modifier, de suspendre ou de mettre fin à cette promotion à tout moment et pour quelque raison que ce soit, et se réserve également le droit de modifier les termes et conditions de cette offre à tout moment. Les termes et offres précédents seront honorés pour les joueurs qui les réclament jusqu'au moment de tout changement de l'offre.</li>
                </ul>
            </div>
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Jeux participants :</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Tous les jeux de Wazdan</span></div>

        <div class="tournament__cond-title">Cagnotte :</div>
        <div class="tournament__cond-text">La valeur totale maximale des prix en espèces est de <strong>500 000 €</strong>. Il y a un total de <strong>5 000 boîtes mystères</strong>. Chaque boîte mystère peut récompenser un prix de la liste suivante :</div>
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
