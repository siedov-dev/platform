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
        <div class="tournament__cond-title mt-2 text-gold">Попрощайтесь с летом с элегантностью!</div>

        <div class="tournament__cond-text"><span class="hot">Mystery Fall</span> - это новая акция от Wazdan, в рамках которой вы сможете выиграть призы из огромного призового фонда в размере
            <strong>3 000 000 евро</strong>. Играйте в ваши любимые игры Wazdan и дайте <span class="hot">Mystery Drop™</span> сделать свою магию.</div>

        <div class="tournament__cond-title">Период акции:</div>
        <div class="tournament__cond-text">С <strong>15 сентября по 15 октября 2023 года</strong></div>

        <div class="tournament__cond-title">Условия и положения:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Акция Mystery Drop ("Акция") доступна только в режиме игры на реальные деньги.</li>
                    <li>Участвующие игроки принимают участие в акции, размещая квалифицированную ставку в участвующих играх в течение периода акции. <strong>Дополнительных расходов нет</strong>.</li>
                    <li>Игроки могут выиграть более одного случайного приза в течение акции.</li>
                    <li>Любая квалифицированная ставка в участвующих играх в течение периода акции может запустить приз из призового фонда.</li>
                    <li>Одна квалифицированная ставка может выиграть только один приз. Призы будут раздаваться случайным образом в течение периода акции.</li>
                    <li><strong>Минимальная ставка не требуется</strong>. Все ставки имеют право на участие в акции.</li>
                    <li>Количество доступных призов обновляется мгновенно.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/chestnut1.png" alt="chestnut" style="width: 250px; margin-top: -10px;">
        </div>

        <div class="flex ai-fe mt-1">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>Все выигранные призы будут мгновенно начислены на счета игроков в казино после получения уведомления о выигрыше.</li>
                    <li>Призы будут выплачиваться согласно вкладке призового фонда.</li>
                    <li>Максимальная общая стоимость денежных призов составляет <strong>500 000 евро</strong>, разделенных на <strong>5000</strong> Секретных Коробок.</li>
                    <li>Денежные призы <strong>не имеют НИКАКИХ требований по отыгрышу</strong>.</li>
                    <li>Призы, выигранные при использовании бонусных средств, будут выплачиваться в виде бонусных средств.</li>
                    <li>Денежные призы будут выплачиваться в валюте, в которой была размещена выигрышная ставка, с учетом конвертации в евро на момент размещения выигрышной ставки.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/nut1.png" alt="nut" style="width: 150px; margin-right: 1rem">
        </div>

        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li>На выигрыш в призы имеют право только проверенные игроки казино.</li>
                    <li>Казино оставляет за собой право не выплачивать денежный приз, если выигрыш произошел вследствие очевидной ошибки, недоразумения или технического сбоя (включая неверные выплаты игры), вызванных как машинными, так и человеческими ошибками при участии в одной из участвующих игр. Казино также оставляет за собой право не выплачивать денежный приз, если, по мнению казино, выигрыш произошел вследствие мошенничества или сговора с другими игроками.</li>
                    <li>Казино оставляет за собой право в любое время по любой причине изменить, аннулировать, приостановить или завершить эту акцию, а также изменить условия данного предложения в любое время. Предыдущие условия и предложения будут действовать для игроков, утверждающих их до момента изменения предложения.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/mystery-fall/leaf1.png" alt="leaf" style="width: 300px; margin-right: 1rem">
        </div>

        <div class="tournament__cond-title">Участвующие игры:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Все игры Wazdan</span></div>

        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="tournament__cond-text">Максимальная общая стоимость денежных призов составляет <strong>500 000 евро</strong>. Всего <strong>5000 Секретных Коробок</strong>. Каждая Секретная Коробка может дарить призы из следующего списка:</div>

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
