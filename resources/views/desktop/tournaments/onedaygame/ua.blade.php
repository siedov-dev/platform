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
                    Цей турнір триває 14 днів, в якому ти можеш щодня вигравати та отримувати призи. Кожен новий день новий виклик від конкретної гри.
                    30 слотів на кожен день турніру, щодня отримуй приз за перемогу над грою.
                    Нагорода за перемогу в "Грі дня": <span class="yellow-text">3 000 $</span>
                    Виграй більше 10 разів у "грі дня" і отримай супер приз <span class="yellow-text">15 000 $</span> наприкінці турніру!
                </p>

                <div class="tournament__cond-title">правила та умови:</div>
                <ul class="tournament__list">
                    <li>У турнірі перемагає той хто, зробить максимальну кількість ставок за добу (24 години) у "грі дня" мінімальна ставка, яка йде в облік від <span class="yellow-text">{{$tournament->getMinBetString() }}</span> за спин (сумуються всі від {{$tournament->getMinBetString()}} зроблені в "Грі дня").</li>
                    <li>Грошові нагороди приходять Вам на рахунок з вейджером <span class="yellow-text">10х</span>. Переможець визначається за допомогою автоматичного підрахунку. </li>
                    <li><span class="yellow-text">14 слотів</span> на кожен день, кожен день подорожуй беручи участь у нашому турнірі, щодня отримуй призи. Мінімальна ставка для участі у турнірі <span class="yellow-text">{{$tournament->getMinBetString()}}</span>. (або еквівалентом в іншій валюті)</li>
                    <li>Щодня визначається переможець. Турнір проводиться з <span class="yellow-text">{{$tournament->getStartDateTime()->isoFormat('D MMMM')}} за {{$tournament->getEndDateTime()->isoFormat('D MMMM ')}}</span></li>
                    <li>Призи автоматично нараховуються на ігровий рахунок гравця після закінчення дня для "Ігри дня" протягом 24 годин.</li>
                    <li>Всі турнірні призи містять вимогу щодо відіграшу у розмірі <span class="yellow-text">10x від суми</span>.</li>
                    <li>Всі бонуси та виграші, отримані в результаті перемоги в турнірі, необхідно відіграти протягом <span class="yellow-text">7 днів</span> з моменту видачі. У випадку, якщо умова щодо відіграшу бонусу не буде виконана протягом зазначеного терміну, сума бонусу, а також будь-які виграші, отримані в результаті відіграшу бонусу та призових грошей, будуть списані з ігрового рахунку через закінчення терміну дії.</li>
                    <li>Список переможців "Ігри дня" відображатиметься в таблиці після підрахунків.</li>
                    <li>Якщо наприкінці гонки обліковий запис гравця заблоковано або сам виключено - виграші не будуть виплачені</li>
                    <li>Якщо у Вас виникли запитання щодо участі, зверніться до служби підтримки, яка доступна <span class="yellow-text">24/7</span></li>
                    <li>Виграні гроші у турнірі <span class="yellow-text">не можна відіграти в РУЛЕТКАХ!</span></li>
                    <li>При спробах шахрайства та обходу правил, ігровий проект Kraken Casino має право відмовити гравцеві в подальшій участі у промо-акції. </li>
                    <li>Ігровий проект Kraken Casino може вносити зміни до умов та завершити змагання у будь-який час без вказівки причин.</li>
                    <li>Таблиця переможців оновлюватиметься в режимі реального часу, де учасники зможуть стежити за змінами. </li>
                    <li>Всі інші правила ігрового проекту Kraken Casino залишаються чинними.</li>
                </ul>
            </div>

        </div>

    </div>
</div>
