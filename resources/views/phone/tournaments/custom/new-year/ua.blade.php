@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .pad_left_20{
        padding-left: 40px;
    }
    .tournament__table{
        background-color: #0B0D31C0; 
        margin-top: 20px; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 5px;
        text-align: center;
    }
    .margintop_30{
        margin-top: 30px;
    }
    .just_center{
        justify-content: center
    }
    .text-center{
        text-align: center;
    }
    .mob_table{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .tour_page .flex{
        display: block
    }
</style>
<div class="width-limiter tour_page">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Новорічний турнір Smartsoft – <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Грати в JetX, Balloon, FootballX, CricketX, PlinkoX, Multihot5, GeniesBonanza, MultiHotWays, BurningICE40,
             та інші ігри, надані Smartoft, завершують оборот і місії множника, повертають таблицю лідерів
             колесо та вигравайте призи!
        </div>
        <div class="tournament__cond-title">Період кампанії:</div>
        <div class="tournament__cond-text">
            Серія мережевих турнірів проводиться з <strong>01.12.2023 – 28.12.2023</strong>. Турнір буде розділеним
             на два етапи; Призовий фонд кожного етапу становить <strong>25 000 євро</strong>. Перший етап проводиться з <strong>01.12.2023</strong> 11:00 год.
             ранку (+ 4 UTC) до (включно) <strong>14.12.2023</strong> 11:00 (+ 4 UTC) і другий етап проводиться з
             <strong>15.12.2023</strong> 11:00 (+ 4 UTC) до (включно) <strong>28.12.2023</strong> 11:00 (+ 4 UTC).
        </div>
        

        <div class="tournament__cond-title">Як прийняти участь?</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                <p>
                    На початку акції гравці повинні перевірити список ігор, які беруть участь, і виконати біг
                     місії. Призи можна виграти, потрапивши серед 500 найкращих гравців у таблиці лідерів.
                </p>
                <p>
                    Таблиця лідерів працює за бальною системою. Для отримання балів гравець повинен крутити колесо таблиці лідерів.
                     Колесо таблиці лідерів містить 6 різних точок: 1,2,10,25,50,100
                </p>
                <p>
                    Щоб отримати право крутити колесо, гравець повинен виконати одне з 2 різних типів місій.
                </p>
                <p>
                    Перша місія називається «Місія обороту». У гравця буде 10 різних фаз, які потрібно пройти кожну
                     день і після виконання кожного з них він/вона отримає 1 оберт на колесі таблиці лідерів. Товарообіг
                     вимоги місії:
                </p>
                <ul class="tournament__list pad_left_20">
                    <li>Перший етап вимагає від гравця зробити ставку на загальну суму 0,10 євро;</li>
                    <li>Другий етап вимагає від гравця зробити ставку на загальну суму 5,1 євро</li>
                    <li>
                        Третя фаза вимагає від гравця зробити ставку на загальну суму 15,1 євро і так далі
                         етапи.
                    </li>
                </ul>
                <table class="tournament__table mob_table" style="">
                    <thead>
                        <tr>
                            <td>№1</td>
                            <td>№2</td>
                            <td>№3</td>
                            <td>№4</td>
                            <td>№5</td>
                            <td>№6</td>
                            <td>№7</td>
                            <td>№8</td>
                            <td>№9</td>
                            <td>№10</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>0.10 EURO</td>
                            <td>5 EURO</td>
                            <td>10 EURO</td>
                            <td>25 EURO</td>
                            <td>50 EURO</td>
                            <td>100 EURO</td>
                            <td>250 EURO</td>
                            <td>500 EURO</td>
                            <td>1000 EURO</td>
                            <td>1500 EURO</td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
            <img src="/img/tournaments/new-year/tree.png" alt="tree" style="width: 100%;">
        </div>
        <img src="/img/tournaments/new-year/tile.jpg" alt="tree" class="margintop_30"  style="width: 100%;">
        <div class="flex  margintop_30">
            <div class="tournament__cond-text">
                <p>
                    Друга місія називається «Місія мультиплікатора». Кожного разу, коли гравець досягає множника X400 (або вище),
                     він/вона отримає 1 спін на колесі очок. Якщо гравець досягає 850X, він/вона отримає 2 оберти на колесі.
                </p>
                <p>
                    Протягом дня гравець може отримувати обертання в обох місіях, але денний ліміт становить 10 обертань.
                     з обох або будь-якої з кожної місії. Після досягнення ліміту сторінка місій буде заморожена.
                </p>
                <p>
                    Прогрес місій буде оновлюватися щодня о 00:00 (UTC:00), і гравці матимуть можливість розпочати
                     нова колекція обертів коліс і очок.
                </p>
                <p>
                    Для Crash Games існує додаткове правило: ставка повинна бути зібрана принаймні на множник X1,5 або вище або
                    бути втраченим.
                </p>
                <p>
                    Іноді може знадобитися до 5 хвилин, перш ніж прогрес колеса з’явиться на передньому екрані реклами –
                     тим часом всередині колеса результати оновлюються негайно.
                </p>
                <p>
                    Після обертання колеса гравець одразу отримує таблицю лідерів.
                </p>
                <p>
                    Якщо 2 або більше гравців мають однакову кількість очок у таблиці лідерів, то позиція буде кращою
                     надається гравцеві, який заробив останні очки пізніше, ніж інші гравці-учасники.
                </p>
            </div>
        </div>

        
        <div class="tournament__cond-title">Мінімальна ставка для участі в турнірі становить 0,10 євро.</div>
        
        <div class="flex ai-center margintop_30">
            <div class="tournament__cond-text" style="margin: 0;">
                <table class="tournament__table mob_table"  style="margin: 0; min-width: 300px;">
                    <thead>
                        <tr>
                            <td>Призове місце</td>
                            <td>Призи</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>3,000 Euro</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2,500 Euro</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>2,000 Euro</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>1,500 Euro</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>1,000 Euro</td>
                        </tr>
                        <tr>
                            <td>6*10</td>
                            <td>500 Euro</td>
                        </tr>
                        <tr>
                            <td>11*20</td>
                            <td>300 Euro</td>
                        </tr>
                        <tr>
                            <td>21*50</td>
                            <td>150 Euro</td>
                        </tr>
                        <tr>
                            <td>51*100</td>
                            <td>50 Euro</td>
                        </tr>
                        <tr>
                            <td>101*200</td>
                            <td>10 Euro</td>
                        </tr>
                        <tr>
                            <td>201*500</td>
                            <td>5 Euro</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <img src="/img/tournaments/new-year/new_year.jpg" alt="tree"style="width: 100%; ">
        </div>

       
        <div class="tournament__cond-title">Правила та умови:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Беруть участь лише ставки, зроблені за реальні гроші на повному веб-сайті, мобільному телефоні чи версії мобільного додатку
                 в турнірі.<br>
                 2. Ігри, які беруть участь у цьому турнірі, це JetX, Balloon, FootballX, CricketX,
                 PlinkoX, SlicerX, Multihot5, GeniesBonanza, BurningICE BurningICE10, BurningICE40, Jungles,
                 Вампіри, Самурай, Вікінг, Joker's4Bonuses, TowerX, MultiHotWays, Magic garden 40, Magic garden
                 10, Чарівний сад 5, ParaDice10, JetX3, Babylon, Elves, Aztec, Crazyape, Cowboy, Moonstone,
                 fruitcoctail, Dark, Apollo, SummerCocktail, Evolution, Argo, Dark100, Zombies, WW2, CrazyHuntX.<br>
                 3. Ви можете крутити колесо очок протягом 30 секунд після того, як воно з’явиться. У разі бездіяльності після 30
                 секунд колесо автоматично обертатиметься, і гравець отримуватиме винагороду на випадковій основі.<br>
                 4. Очки таблиці лідерів і прогрес відображатимуться в лобі ігор-учасників.<br>
                 5. Гравець, який набере максимальну кількість очок, виграє турнір.<br>
                 6. Після закінчення акції протягом 3 робочих днів гравці-переможці отримають призи.<br>
                 7. У разі втрати з’єднання або технічної проблеми всі зачеплені спіни вважаються недійсними та можуть
                 не включаються в результати турніру.<br>
                 8. Казино та Smartsoft отримують право анулювати будь-які зароблені бали або утримувати призи, якщо вони
                 вважають, що результат повністю або частково став результатом шахрайства або змови з іншими гравцями.<br>
                 9. Smartsoft залишає за собою право змінити, призупинити або скасувати цю пропозицію в будь-який час.<br>
                 10. Беручи участь у цій пропозиції, клієнт погоджується з цими умовами та загальними умовами
                 умови Казино.<br>
                 11. Усі рішення, прийняті керівництвом Казино, є остаточними.
            </p>
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
