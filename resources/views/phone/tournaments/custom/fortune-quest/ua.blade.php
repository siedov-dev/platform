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
        background-color: #9d8b53;
    }
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        /*border: 1px solid black;*/
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
        color: #FFF;
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
        font-size: 1rem;
        font-weight: 500;
        text-shadow: 0 0 2px black;
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
        padding: 1.4rem;
        color: #000000;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <article class="article__text">

        <div style="position: relative;">
            <p class="mt-2">
                Прощавай літо! Привіт, осінь! Yggdrasil запрошує вас приєднатися до веселощів та азарту мережевого турніру «Grand Fortune Quest».
            </p>
            <p>
                Загальний грошовий призовий фонд у розмірі <span class="red-text">30 000 євро</span> буде розіграний! Зіграйте в будь-яку з ігор, що беруть участь, щоб отримати шанс опинитися серед випадкових переможців розіграшу призів!
            </p>
            <p class="mb-2">
                Турнір проходить з <span class="red-text">з 19 по 25 вересня 2022 р.</span>.
                Час перевернути новий аркуш!
            </p>
            <img src="/img/tournaments/fortune-quest/ship_01.png" alt="ship" class="tournament__image" style="position: absolute; bottom: 1rem; right: 0; width: 100%; filter: opacity(0.3)">
        </div>

        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Правила та умови:</div>
                <ul class="tournament__list">
                    <li><span class="red-text">Рейтинги та бали відсутні. Ідея проста - всі призи генеруються випадково.</span> Для участі в акції не потрібна мінімальна ставка.</li>
                    <li>Щоб брати участь у кампанії, гравці повинні підтвердити своє бажання взяти участь у кампанії, натиснувши «приєднатися зараз» у спливаючому вікні. Учасники братимуть участь у кампанії, зробивши ставку в іграх, що беруть участь, протягом періоду кампанії. Для участі в кампанії додаткова оплата не потрібна.</li>
                    <li>Будь-яка ставка, розміщена в <span class="red-text">будь-який із ігор</span>, запускає випадковий загадковий приз із призового фонду.</li>
                    <li>Залишок призів оновлюється кожні 60 секунд.</li>
                    <li>Призи будуть присуджені протягом усього періоду кампанії. Мінімум один із трьох найцінніших призів («головний приз») буде присуджено протягом заключного періоду кампанії, такий період дорівнює 5% від загального періоду кампанії (наприклад, якщо період кампанії становить 100 годин). , протягом останніх 5 годин буде присуджено щонайменше один головний приз).</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/fortune-quest/skul_01.png" alt="skull" class="tournament__image">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style=''>
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>Кожна ставка може принести лише один випадковий приз.</li>
                    <li>Під час кампанії гравці можуть виграти понад 1 випадковий приз.</li>
                    <li>Yggdrasil залишає за собою право анулювати очки або не виплачувати приз, якщо всі або частина очок є результатом якоїсь очевидної помилки, помилки або технічної несправності (включаючи неправильні ігрові виплати), спричиненої помилкою машини або людини. щодо будь-якої з ігор, що беруть участь. Крім того, ми залишаємо за собою право анулювати результати або не сплачувати приз, якщо, на нашу думку, всі або частина результатів є результатом шахрайства або змови з іншими гравцями. </li>
                    <li>В акції беруть участь лише ставки, зроблені в реальному режимі гри.</li>
                </ul>
            </div>
        </div>
        <div class="flex jc-center">
            <img src="/img/tournaments/fortune-quest/compass_01.png" alt="compass" class="tournament__image">
        </div>

        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/fortune-quest/map2.png') no-repeat center; background-size: 100% 90%;" >
            <div class="" style="padding-bottom: 3rem; margin-right: auto; margin-left: auto; width: 90%;">
                <table class="tournament__table mt-2" style="width: 100%">
                    <thead>
                        <tr>
                            <th>Winners</th>
                            <th>EUR</th>
                        </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>5 000 €</td></tr>
                    <tr><td>2-3</td><td>2 000 €</td></tr>
                    <tr><td>4-8</td><td>700 €</td></tr>
                    <tr><td>9-18</td><td>300 €</td></tr>
                    <tr><td>19-69</td><td>80 €</td></tr>
                    <tr><td>69-168</td><td>30 €</td></tr>
                    <tr><td>169-668</td><td>15 €</td></tr>
{{--                    <tr style="font-weight: bold;"><td>668 winners</td><td>30 000 €</td></tr>--}}
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
