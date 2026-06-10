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
                    Прийми <span class="yellow-text">ВИКЛИК</span> популярних <span class="yellow-text">СЛОТІВ</span> ігрового проекту Kraken Casino.
                </p>
                <p>
                    Цей турнір триватиме <span class="yellow-text">14 днів</span>, в якому ти можеш щодня вигравати та отримувати призи. Кожен новий день новий виклик.
                </p>

                <div class="tournament__cond-title">Правила та умови:</div>
                <ul class="tournament__list">
                    <li>У турнірі перемагає той хто, зробить максимальну кількість ставок за момент проведення турніру <span class="yellow-text">14 днів</span>.</li>
                    <li>У турнірі беруть участь усі ставки від <span class="yellow-text">{{$tournament->getMinBetString()}}</span> і вище </li>
                    <li>Грошові нагороди приходять Вам на рахунок з вейджером <span class="yellow-text">1х</span>. Переможець визначається за допомогою автоматичного підрахунку. Мінімальна ставка для участі у турнірі <span class="yellow-text"> {{$tournament->getMinBetString()}} </span>.</li>
                    <li>Призи автоматично нараховуються на ігровий рахунок гравця після закінчення турніру.</li>
                    <li>Всі грошові призи після закінчення турніру містять вимогу щодо відіграшу у розмірі 1x від суми.</li>
                    <li>Всі бонуси та виграші, отримані в результаті перемоги в турнірі, необхідно відіграти протягом 7 днів з моменту видачі. У випадку, якщо умова щодо відіграшу бонусу не буде виконана протягом зазначеного терміну, сума бонусу, а також будь-які виграші, отримані в результаті відіграшу бонусу та призових грошей, будуть списані з ігрового рахунку через закінчення терміну дії.</li>
                    <li>Список переможців відображатиметься в таблиці після підрахунків.</li>
                    <li>Якщо наприкінці гонки обліковий запис гравця заблоковано або сам виключено - виграші не будуть виплачені</li>
                    <li>Якщо ви маєте будь-які питання щодо участі, звертайтеся до служби підтримки, яка доступна 24/7</li>
                    <li>Виграні гроші в турнірі не можна відіграти на інших видах ігор, крім слотів!</li>
                    <li>При спробах шахрайства та обходу правил, ігровий проект Kraken Casino має право відмовити гравцеві в подальшій участі у промо-акції.</li>
                    <li>Ігровий проект Kraken Casino може вносити зміни до умов та завершити змагання у будь-який час без вказівки причин.</li>
                    <li>Таблиця переможців оновлюватиметься в режимі реального часу, де учасники зможуть стежити за змінами. </li>
                    <li>Всі інші правила ігрового проекту Kraken Casino залишаються в силі. </li>
                </ul>
            </div>
        </div>

    </div>
</div>
