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
    .tournament__games-list {
        display: grid;
        grid-template-columns: repeat(auto-fill,minmax(5rem,1fr));
        grid-gap: 1rem;
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
        <div class="tournament__cond-title mt-2 text-gold">Diga adeus ao verão com estilo!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> é a nova promoção da Wazdan durante a qual você poderá ganhar prêmios do maciço
            <strong>€3.000.000</strong> do prêmio. Jogue seus jogos favoritos da Wazdan e deixe o <span class="hot">Mystery Drop™</span> fazer maravilhas.</div>

        <div class="tournament__cond-title">Período da campanha:</div>
        <div class="tournament__cond-text">De <strong>15 de setembro a 15 de outubro de 2023</strong></div>

        <div class="tournament__cond-title">Termos e Condições:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>A campanha Mystery Drop ("A campanha") está disponível apenas no modo Jogue com dinheiro real.</li>
                    <li>Os jogadores participantes entrarão na campanha ao fazerem uma aposta qualificatória nos jogos participantes durante o período da campanha. <strong>Não há custo adicional</strong>.</li>
                    <li>Os jogadores podem ganhar mais de um prêmio aleatório durante a campanha.</li>
                    <li>Qualquer aposta qualificatória nos jogos participantes durante o período da campanha pode acionar um prêmio do prêmio total.</li>
                    <li>Uma aposta qualificatória pode ganhar apenas um prêmio. Os prêmios serão concedidos aleatoriamente ao longo do período da campanha.</li>
                    <li><strong>Sem exigência de tamanho mínimo de aposta</strong>. Todas as apostas são qualificadas para participar da campanha.</li>
                    <li>O número de prêmios disponíveis é atualizado instantaneamente.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Todos os prêmios ganhos serão creditados instantaneamente nas contas de cassino dos jogadores ao receberem a notificação de ganho.</li>
                    <li>Os prêmios serão pagos de acordo com a tabela de prêmios.</li>
                    <li>O valor total máximo dos prêmios em dinheiro é de <strong>€500.000</strong> divididos em <strong>5.000 Caixas Misteriosas</strong>.</li>
                    <li>Os prêmios em dinheiro <strong>não têm requisitos de apostas</strong>.</li>
                    <li>Prêmios pagos usando fundos de bônus serão pagos na forma de fundos de bônus.</li>
                    <li>Os prêmios em dinheiro serão pagos na moeda em que a aposta vencedora foi feita, de acordo com a conversão de moeda para Euro no momento da aposta vencedora.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Apenas jogadores de cassino verificados são elegíveis para ganhar os prêmios.</li>
                    <li>O cassino reserva-se o direito de não pagar um prêmio em dinheiro quando o ganho resulta de algum erro evidente, engano ou falha técnica (incluindo pagamentos incorretos de jogos), seja causado por erro de máquina ou humano em relação a qualquer um dos jogos participantes. O cassino reserva-se ainda o direito de não pagar um prêmio em dinheiro quando, em sua opinião, o ganho resulta de trapaça ou conluio com outros jogadores.</li>
                    <li>O cassino reserva-se o direito de modificar, alterar, interromper ou encerrar esta promoção a qualquer momento, por qualquer motivo, e reserva-se também o direito de alterar os termos e condições desta oferta a qualquer momento. Os termos e ofertas anteriores serão honrados para os jogadores que fizerem reclamações até o momento de qualquer alteração na oferta.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Jogos participantes:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Todos os jogos da Wazdan</span></div>

        <div class="tournament__cond-title">Montepremi:</div>
        <div class="tournament__cond-text">O valor total máximo dos prêmios em dinheiro é de <strong>€500.000</strong>. Há um total de <strong>5.000 Caixas Misteriosas</strong>. Cada Caixa Misteriosa pode recompensar um prêmio da seguinte lista:</div>
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
                @include('phone.games.single-game', ['game' => $game])
            @endforeach
        </div>
    </div>

    @include('phone.footer')

</div>
</div>
