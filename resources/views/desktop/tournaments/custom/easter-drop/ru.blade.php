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

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Примите участие в пасхальном выпуске Wazdan и выиграйте часть огромного призового фонда <span style="color: red; font-weight: жирный">€250 000</span>! </div>
        <div class="tournament__cond-title">Период кампании:</div>
        <div class="tournament__cond-text">С <strong>3 – 16 апреля 2023 г.</strong></div>

        <div class="tournament__cond-title">Положения и условия:</div>
        <div class="flex ai-центр">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Кампания Mystery Box («Кампания») доступна только для игры на реальные деньги.</li>
                    <li> Участвующие игроки примут участие в кампании, разместив квалификационную ставку в участвующих играх в течение периода кампании. <strong>Дополнительных расходов нет</strong>.</li>
                    <li> Во время кампании игроки могут выиграть более одного случайного приза.</li>
                    <li> Любая квалификационная ставка в участвующих играх в течение периода кампании может привести к розыгрышу приза из призового фонда.</li>
                    <li> Квалификационная ставка может принести только один приз. Призы будут присуждаться случайным образом в течение всего периода кампании.</li>
                    <li> <strong>Нет требований к минимальному размеру ставки</strong>. Все ставки подходят для участия в кампании.</li>
                    <li> Количество доступных призов обновляется мгновенно.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/bunny_1C.png" alt="bunny" style="width: 250px; margin-top: -80px;">
        </div>

        <div class="flex ai-fe">
            <img src="/img/tournaments/easter-drop/MagicEggs_blue.png" alt="egg" style="width: 300px; margin-right: 1rem">
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Все выигранные призы будут мгновенно зачислены на счета игроков в казино после получения уведомления о выигрыше.</li>
                    <li> Призы будут выплачиваться в соответствии с вкладкой призового фонда.</li>
                    <li> Максимальная общая сумма денежных призов составляет <strong>250 000 евро</strong>, разделенных на <strong>2500</strong> Mystery Box.</li>
                    <li> Денежные призы <strong>не требуют отыгрыша</strong>.</li>
                    <li> Призы, выплаченные при использовании бонусных средств, будут выплачены бонусными средствами.</li>
                    <li> Денежные призы будут выплачены в валюте, в которой была сделана выигрышная ставка, в соответствии с конвертацией валюты из евро на момент размещения выигрышной ставки.</li>
                </ul>
            </div>
        </div>

        <div class="flex ai-fe">
            {{--            <img src="/img/tournaments/easter-drop/bunny_2B.png" alt="bunny" style="width: 250px;">--}}
            <div class="tournament__cond-text">
                <ul class="tournament__list">
                    <li> Только проверенные игроки казино могут выиграть призы.</li>
                    <li> Казино оставляет за собой право не выплачивать денежный приз, если выигрыш является результатом какой-либо очевидной ошибки, ошибки или технической неисправности (включая неправильные игровые выплаты), будь то машинная или человеческая ошибка в отношении любого из участвующих игры. Казино также оставляет за собой право не выплачивать денежный приз, если, по его мнению, выигрыш является результатом мошенничества или сговора с другими игроками.</li>
                    <li> Казино оставляет за собой право модифицировать, изменять, прекращать или прекращать эту акцию в любое время по любой причине, а также оставляет за собой право изменять условия этого предложения в любое время. Предыдущие условия и предложения будут действительны для игроков, подавших заявку, до момента внесения каких-либо изменений в предложение.</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MagicEggs_green.png" alt="egg" style="width: 300px; margin-right: 1rem; margin-bottom: -50px;">
        </div>

        <div class="tournament__cond-title">Участвующие игры:</div>
        <div class="tournament__cond-text"><span style="color: red; font-weight: bold">Все игры wazdan</span></div>

        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="tournament__cond-text">Максимальная общая сумма денежных призов составляет <strong>250 000 евро</strong>. Всего имеется <strong>2500 Mystery Box</strong>. В каждой Mystery Box можно получить приз из следующего списка:</div>
        <div class="flex ai-center">
            <div class="tournament__cond-text ml-3">
                <ul class="tournament__list">
                    <li>100 EUR</li>
                    <li>75 EUR</li>
                    <li>50 EUR</li>
                    <li>25 EUR</li>
                    <li>10 EUR</li>
                    <li>5 EUR</li>
                </ul>
            </div>
            <img src="/img/tournaments/easter-drop/MysteryDrop_box.png" alt="bunny" style="width: 250px; margin-left: 100px;">
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
