@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }
    .tournament__list ul li {
        font-size: 0.875rem;
        color: #000000;
    }
    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #000000;
    }
    .article__text ul li::before{
        background-color: #000000;
    }
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid black;
        background-color: #00000033;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFFFFF;
        text-shadow: 0 0 2px black;
        border-bottom: 1px solid black;
        white-space: nowrap;
        font-weight: 600;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        /*background-color: #3c3b89;*/
        border-bottom: 2px solid black;
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
    .article__text p{
        color: #FFFFFF;
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px black;
        text-indent: 1rem;
    }
    .red-text{
        color: red;
        font-weight: bold;
    }
    .tournament__cond-text{
        background-image: url("/img/tournaments/fortune-quest/paper_03.png");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 100% 100%;
        padding: 4rem 6rem;
        color: #000000;
    }

    .article__text ul{
        color: black;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <article class="article__text">

        <div style="position: relative; padding-right: 340px;">
            <p class="mt-2">
                Вибери свою отруту! Yggdrasil запрошує вас приєднатися до веселощів і захоплень мережевого турніру «Spooky Fortune Quest».
            </p>
            <p>
                <span class="red-text">Розігрується загальний грошовий призовий фонд у розмірі 50 000 євро!</span> Грайте в будь-яку з ігор, щоб отримати шанс стати одним із переможців випадкового розіграшу призів!
            </p>
            <p class="mb-2">
                Турнір триватиме з <span class="red-text">28 жовтня до 6 листопада 2022 року.</span>
                Нехай настануть часи упирів!
            </p>
            <img src="/img/tournaments/spooky-fortune-quest/pyramids.png" alt="ship" class="tournament__image" style="position: absolute; bottom: 0; right: 0; width: 400px; filter: drop-shadow(1px 1px 2px black);">
        </div>

        <div class="flex ai-center" style="position: relative;">
            <div class="tournament__cond-text">
                <img src="/img/tournaments/spooky-fortune-quest/d.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; top: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
                <div class="tournament__cond-title" style="color: black;">Правила та умови:</div>
                <ul class="tournament__list" style="padding-bottom: 80px;">
                    <li><span class="red-text">Рейтинги та бали відсутні. Ідея проста - всі призи генеруються випадково.</span> Для участі в акції не потрібна мінімальна ставка.</li>
                    <li>Щоб брати участь у кампанії, гравці повинні підтвердити своє бажання взяти участь у кампанії, натиснувши «приєднатися зараз» у спливаючому вікні. Учасники братимуть участь у кампанії, зробивши ставку в іграх, що беруть участь, протягом періоду кампанії. Для участі в кампанії додаткова оплата не потрібна.</li>
                    <li>Будь-яка ставка, розміщена в <span class="red-text">будь-який із ігор</span>, запускає випадковий загадковий приз із призового фонду.</li>
                    <li>Залишок призів оновлюється кожні 60 секунд.</li>
                    <li>Призи будуть присуджені протягом усього періоду кампанії. Мінімум один із трьох найцінніших призів («головний приз») буде присуджено протягом заключного періоду кампанії, такий період дорівнює 5% від загального періоду кампанії (наприклад, якщо період кампанії становить 100 годин). , протягом останніх 5 годин буде присуджено щонайменше один головний приз).</li>
                    <li>Кожна ставка може принести лише один випадковий приз.</li>
                    <li>Під час кампанії гравці можуть виграти понад 1 випадковий приз.</li>
                    <li>Yggdrasil залишає за собою право анулювати очки або не виплачувати приз, якщо всі або частина очок є результатом якоїсь очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), спричиненої помилкою машини або людини. щодо будь-якої з ігор, що беруть участь. Крім того, ми залишаємо за собою право анулювати результати або не сплачувати приз, якщо, на нашу думку, всі або частина результатів є результатом шахрайства або змови з іншими гравцями. </li>
                    <li>В акції беруть участь лише ставки, зроблені в реальному режимі гри.</li>
                </ul>
                <img src="/img/tournaments/spooky-fortune-quest/skull.png" alt="compass" class="tournament__image"
                     style="margin-left: 2rem; position: absolute; bottom: 90px; right: 70px; filter: invert(1) opacity(0.3); width: 150px;">
            </div>
        </div>

        <div class="flex jc-center mt-1 mb-1">
            <img src="/img/tournaments/spooky-fortune-quest/wrecked.png" alt="compass" class="tournament__image">
        </div>
        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/fortune-quest/map2.png') no-repeat center; background-size: contain;" >
            <div class="" style="padding-bottom: 10rem; padding-top: 10rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style="">
                    <thead>
                    <tr>
                        <th>Winners</th>
                        <th>EUR</th>
                        <th>RUB</th>
                        <th>INR</th>
                        <th>UAH</th>
                        <th>KZT</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>10 000 €</td><td>600 000 ₽</td><td>800 000 ₹</td><td>400 000 ₴</td><td>4 700 000 ₸</td></tr>
                    <tr><td>2-3</td><td>5 000 €</td><td>300 000 ₽</td><td>400 000 ₹</td><td>200 000 ₴</td><td>2 350 000 ₸</td></tr>
                    <tr><td>4-8</td><td>1 000 €</td><td>60 000 ₽</td><td>80 000 ₹</td><td>40 000 ₴</td><td>470 000 ₸</td></tr>
                    <tr><td>9-18</td><td>500 €</td><td>30 000 ₽</td><td>40 000 ₹</td><td>20 000 ₴</td><td>235 000 ₸</td></tr>
                    <tr><td>19-69</td><td>100 €</td><td>6 000 ₽</td><td>8 000 ₹</td><td>4 000 ₴</td><td>47 000 ₸</td></tr>
                    <tr><td>69-168</td><td>50 €</td><td>3 000 ₽</td><td>4 000 ₹</td><td>2 000 ₴</td><td>23 500 ₸</td></tr>
                    <tr><td>169-668</td><td>20 €</td><td>1 200 ₽</td><td>1 000 ₹</td><td>800 ₴</td><td>9 400 ₸</td></tr>
                    <tr style="font-weight: bold;"><td>668 winners</td><td>50 000 €</td><td>3 000 000 ₽</td><td>4 000 000 ₹</td><td>2 000 000 ₴</td><td> 23 500 000 ₸</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </article>



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

    @include('desktop.footer')

</div>
