@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__table{
        background-color: #0B0D31C0; 
        width: 100%;
        color: #fff
    }
    .tournament__table td{
        padding: 15px;
        vertical-align: text-top;
    }
    .margintop_30{
        margin-top: 30px;
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
        <div class="tournament__cond-title mt-2">3 Oaks Сетевой турнир <span style="color: rgb(255, 157, 0)">Money Desert</span> – Призовий фонд <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Грай в Olaf Viking, Crystal Scarabs, Grab more Gold!, 3 Coins: Egypt, Egypt Fire, Sun of Egypt 2, Coin Volcano, Sun of Egypt 3, More Magic Apple, Black Wolf 2, Green Chilli 2, Aztec Fire, Green Chilli, Hit the Gold!, та вигравай призи!
        </div>
        <div class="tournament__cond-title">Період кампанії:</div>
        <div class="tournament__cond-text">
            <strong>07.12.2023 09:00 UTC</strong> до <strong>13.12.2023 23:59 UTC</strong>
        </div>
        

        <div class="tournament__cond-title">Деталі кампанії</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                
                <table class="tournament__table mob_table" style="color: #fff;">
                    <tbody>
                        <tr>
                            <td>Призовий фонд</td>
                            <td>EUR 50,000</td>
                        </tr>
                        <tr>
                            <td>Кваліфікація</td>
                            <td>
                                Щоб пройти кваліфікацію та мати право претендувати на приз, гравець повинен виконати такі умови:<br>
                                 1. <strong>Мінімальна ставка</strong> становить <strong style="color: red">0,2 євро</strong>,<br>
                                 2. Мінімальна <strong>кількість обертань</strong> становить <strong style="color: red">150</strong><br>
                                 Усі очки, зібрані під час кваліфікаційного етапу, зберігаються та зараховуються гравцеві після проходження
                                 кваліфікація. Гравці, які не пройшли кваліфікацію, також відображаються в таблиці лідерів, але лінія для гравців, які не пройшли кваліфікацію, виділена сірою смугою, доки вони не пройдуть кваліфікацію.
                            </td>
                        </tr>
                        <tr>
                            <td>Підрахунок очок турніру</td>
                            <td>
                                <strong style="color: red">ПЕРЕГРАТИ:</strong> якщо гравець зробив загальну ставку <strong>1 євро</strong> та виграв <strong>120 євро</strong>, гравець отримає < strong>120 балів</strong>.<br>
                                 <strong style="color: red">МУЛЬТИПЛІКАЛЬНА ГОНКА:</strong> якщо гравець зробив загальну ставку <strong>2 євро</strong> та виграв <strong>120 євро</strong>, гравець отримає
                                 120(перемога)÷2(ставка)= множник 60x = <strong>60 очок</strong>.
                            </td>
                        </tr>
                        <tr>
                            <td>Таблиця лідерів у грі</td>
                            <td>
                                <p>Під час турніру таблиця лідерів оновлюється в режимі реального часу з обертанням кожного гравця. Без a
                                    активності гравця, статистика гравця за замовчуванням оновлюється через 10 секунд синхронізації між грою та
                                    промо сервер.
                                </p>
                                <p>
                                    Переможні очки відображаються та сортуються в таблиці лідерів турніру – результати найкращих гравців
                                     завжди на висоті.
                                </p>
                                <p>
                                    Під час формування остаточної таблиці лідерів ми залишаємо 5-хвилинну затримку, щоб запобігти всій можливій втраті даних
                                     сценарії; під час виконання остаточних розрахунків таблиця лідерів відображатиметься смужкою до завершення.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
        </div>
        <div class="flex  margintop_30">
            <img src="/img/tournaments/money-desert/600x800_money_desert.jpg" alt="tree"style="width: 100%;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title" style="margin: 0;">Розподіл призового фонду</div>
                <ul class="tournament__list pad_left_20">
                    <li>Призи автоматично виплачуються переможцям, якщо ввімкнено функцію автоматичних виплат. Переможець отримує Win Pop-Up після
                        Турнір завершено, а остаточні результати відображаються в таблиці лідерів.
                    </li>
                    <li>
                        Якщо функцію автоматичних виплат вимкнено, гравці отримують спливаюче вікно «Виграш» із повідомленням про те, що приз буде вручну зараховано на їхні облікові записи
                         Оператором протягом 72 годин
                    </li>
                    <li>
                        Якщо функція автоматичних виплат недоступна через будь-які технічні проблеми, оператори винагороджують гравців вручну на основі переможців
                        Звіт надано 3 Oaks.
                    </li>
                    <li>
                        Загальний призовий фонд Турніру становить 50 000 євро (або еквівалент у валюті гравця)
                    </li>
                    <li>
                        Таблиця розподілу призів у євро:
                    </li>
                </ul>
                <table class="tournament__table margintop_30 mob_table">
                    <thead>
                        <tr>
                            <td>З місця</td>
                             <td>Розмістити</td>
                             <td>Приз</td>
                             <td>Сума</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>1</td>
                            <td><strong>€ 5,000</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>2</td>
                            <td><strong>€ 3,500</strong></td>
                            <td>€ 3,500</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>3</td>
                            <td><strong>€ 2,500</strong></td>
                            <td>€ 2,500</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>5</td>
                            <td><strong>€ 1,500</strong></td>
                            <td>€ 3,000</td>
                        </tr>
                        <tr>
                            <td>6</td>
                            <td>10</td>
                            <td><strong>€ 750</strong></td>
                            <td>€ 3,750</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>20</td>
                            <td><strong>€ 550</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>21</td>
                            <td>50</td>
                            <td><strong>€ 375</strong></td>
                            <td>€ 11,250</td>
                        </tr>
                        <tr>
                            <td>51</td>
                            <td>100</td>
                            <td><strong>€ 175</strong></td>
                            <td>€ 8,750</td>
                        </tr>
                        <tr>
                            <td>101</td>
                            <td>150</td>
                            <td><strong>€ 100</strong></td>
                            <td>€ 5,000</td>
                        </tr>
                        <tr>
                            <td>151</td>
                            <td>200</td>
                            <td><strong>€ 45</strong></td>
                            <td>€ 2,250</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
       
        <div class="tournament__cond-title">Правила та умови:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Беручи участь у турнірі, ви погоджуєтеся з положеннями та умовами, викладеними тут, а також умовами, наданими
                 оператор.<br>
                 2. У екстрених випадках 3 Oaks Gaming залишає за собою право в будь-який час скасувати акцію Money Desert, включаючи платежі за
                 виграші, якщо це буде визнано необхідним.<br>
                 3. Лише кваліфіковані гравці можуть претендувати на приз (будь ласка, перегляньте розділ «Деталі кампанії»).<br>
                 4. У турнірі можуть брати участь лише раунди на реальні гроші.<br>
                 5. За участь у турнірі немає жодних додаткових витрат, окрім ціни за обертання.<br>
                 6. Кожен гравець отримає персональний ідентифікатор турніру для таблиці лідерів.<br>
                 7. Якщо два або більше гравців мають нічию, ті гравці, які першими отримають рахунок, вирішать нічию.<br>
                 8. Правила турніру можуть бути змінені в будь-який час.<br>
                 9. Несправність анулює всі виплати.
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
