@php
/** @var \App\Models\Tournaments\OnedaygameTournament $tournament */
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
        color: #FFF;
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
    .yellow-text{
        color: #fcc339;
        font-weight: 500;
    }
    .article__text p{
        color: #FFFFFF;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px black;
        text-indent: 1rem;
        font-weight: 600;
    }
</style>
<div class="width-limiter">



    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <p>
                    Прими <span class="yellow-text">ВЫЗОВ</span> популярных <span class="yellow-text">СЛОТОВ</span>  игрового проекта Kraken Casino.
                </p>
                <p>
                    Данный турнир длиться 14 дней , в котором ты можешь ежедневно выигрывать и получать призы. Каждый новый день новый вызов от конкретной игры.
                    30 слотов на каждый  дней турнира, каждый день получай приз за победу над игрой.
                    Награда за победу в "Игре дня": <span class="yellow-text">3 000 $</span>
                    Выиграй более 10 раз в "игре дня" и получи супер приз <span class="yellow-text">15 000 $</span> в конце турнира!
                </p>

                <div class="tournament__cond-title">Правила и условия:</div>
                <ul class="tournament__list">
                    <li>В турнире побеждает тот кто, сделает максимальное количество ставок за сутки (24 часа) в "игре дня"  минимальна ставка которая идет в учет от <span class="yellow-text">{{$tournament->getMinBetString()}}</span> за спин  (суммируются все от {{$tournament->getMinBetString()}} сделанные в "Игре дня").</li>
                    <li>Денежные награды приходят Вам на счёт с вейджером <span class="yellow-text">10х</span>. Победитель определяется с помощью автоматического подсчета. </li>
                    <li><span class="yellow-text">14 слотов</span>  на каждый из дней, каждый день путешествуй участвуя в нашем турнире, каждый день получай призы. Минимальная ставка для участия в турнире <span class="yellow-text">{{$tournament->getMinBetString()}}</span>. (или эквивалентом в другой валюте)</li>
                    <li>Ежедневно определяется победитель. Турнир проводится с <span class="yellow-text">{{$tournament->getStartDateTime()->isoFormat('D MMMM')}} по {{$tournament->getEndDateTime()->isoFormat('D MMMM')}}</span></li>
                    <li>Призы автоматически начисляются на игровой счет игрока по окончанию дня для "Игры дня"в течение 24 часов.</li>
                    <li>Все турнирные  призы содержат требование по отыгрышу в размере <span class="yellow-text">10x от суммы</span>.</li>
                    <li>Все бонусы и выигрыши, полученные в результате победы в турнире  необходимо отыграть в течение <span class="yellow-text">7 дней</span>  с момента выдачи. В случае, если условие по отыгрышу бонуса не будет выполнено в течение указанного срока, сумма бонуса, а также любые выигрыши, полученные в результате отыгрыша бонуса и призовых денег, будут списаны с игрового  счета по причине истечения срока действия.</li>
                    <li>Список победителей  "Игры дня" будет отображаться в таблице после подсчетов.</li>
                    <li>Если в конце гонки аккаунт игрока заблокирован или само исключен - выигрыши не будут выплачены</li>
                    <li>Если у вас есть какие-либо вопросы об участии обращайтесь в службу поддержки, которая доступна <span class="yellow-text">24/7</span></li>
                    <li>Выигранные деньги в турнире <span class="yellow-text">нельзя отыграть в РУЛЕТКАХ!</span></li>
                    <li>При попытках мошенничества и обхода правил, игровой проект Kraken Casino имеет право отказать игроку в дальнейшем участии в промо-акции. </li>
                    <li>Игровой проект Kraken Casino может вносить изменения в условия и завершить соревнование в любое время без указания причин.</li>
                    <li>Таблица победителей будет обновляться в режиме реального времени, где участники смогут следить за изменениями. </li>
                    <li>Все остальные правила игрового проекта Kraken Casino остаются в силе.</li>
                </ul>
            </div>

        </div>

    </div>
</div>
