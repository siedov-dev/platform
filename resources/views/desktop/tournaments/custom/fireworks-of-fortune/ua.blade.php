@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .orange_bg{
        background: #d8b818!important;
        color: #000;
        font-weight: 600;
    }
    .tnt_table{
        width: 100%;
        color: #000
    }
    .tnt_table td{
        padding: 10px;
        background: #fff;
        text-align: center;
    }
    .tablett{
        width: 100%;
        color: #000;
    }
    .tablett td{
        padding: 5px;
        background: #d8b818;
    }
    .tablett2{
        width: 100%;
    }
    .tablett2 td{
        padding: 5px;
        background: #fff;
        color: #000;
        font-weight: 600;
        width: 50%;
    }
    .pdis_table{
        color: #fff;
        width: 100%;
    }
    .pdis_table td{
        padding: 3px;
        text-align: center;
        background: #fff;
        color: #000;
        font-weight: 600;
    }
    .pdis_table_title{
        padding: 5px;
        background: #d8b818;
        color: #000;
        font-weight: 600;
        margin: 2px;
        margin-top: 30px;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">3 Oaks Network Турнірна серія <span style="color: #d8b818">Fireworks of Fortune</span> – Призовий фонд <span style="color: red">100,000</span> Euro</div>
        
        
        <div class="flex">
            <div>
                <img src="/img/tournaments/fireworks-of-fortune/344x515_fireworks_of_fortune.jpg" alt="tree" style="height: 464px; margin-right: 30px; margin-top: 30px;">
            </div>
            <div>
                <div class="tournament__cond-title">Fireworks of Fortune старт:</div>
                <div class="tournament__cond-text">
                    <span style="color: #d8b818;">28.12.2023 00:00</span> UTC до <span style="color: #d8b818;">02.01.2024 23:00</span> UTC
                </div>
                
                <div class="tournament__cond-title">Загальний призовий фонд:</div>
                <div class="tournament__cond-text">
                    Загальний призовий фонд цієї кампанії - <span style="color: red">EUR 100,000</span> (або еквівалент у валюті гравця) - об'єднується з кількох пулів за такою структурою:<br>
                        <span style="color: red">EUR 100,000</span> за період <span style="color: red">28.12.2023 00:00 UTC</span> до <span style="color: red">02.01.2024 23:00 UTC</span>
                        <ul>
                            <li><span style="color: red">EUR 20,000</span> присуджується за кожен регулярний турнір на загальну суму <span style="color: red">EUR 60,000</span> в <span style="color: red">3</span> турнірах</li>
                            <li><span style="color: red">EUR 40,000</span>нагороджується через таблицю лідерів серії в 1 окремій серії</li>
                        </ul>
                </div>
                
                <div class="tournament__cond-title">Призовий фонд турніру:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 20,000</strong> (або еквівалент у валюті гравця) нараховується для кожного турніру на загальну суму <strong style="color: red">EUR 60,000</strong> у 3 турнірах.</p>
                    <p>Будь ласка, перегляньте розділ <strong>Розподіл призового фонду</strong> для отримання додаткової інформації.</p>
                </div>
                
                <div class="tournament__cond-title">Додатковий призовий фонд серії:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 40,000</strong> нагороджується через таблицю лідерів серії в 1 окремій серії.</p>
                    <p>Будь ласка, перегляньте <strong style="color: red">розділ розподілу призового фонду</strong>, щоб отримати докладнішу інформацію. </p>
                </div>
            </div>
        </div>

        <div class="tournament__cond-title">Структура турнірної серії:</div>
        <div class="tournament__cond-text">
            <p><span style="color: red">1</span> окрема серія з <span style="color: red">3</span> звичайними турнірами в кожній:</p>
            <table class="tnt_table">
                <thead class="">
                    <tr>
                        <td class="orange_bg" style="text-align: center;">Fireworks of Fortune 28.12 - 02.01</td>
                    </tr>
                </thead>
            </table>
            <table class="tnt_table">
                <tbody>
                    <tr>
                        <td class="orange_bg">TNTs</td>
                        <td>TNT1</td>
                        <td>TNT2</td>
                        <td>TNT3</td>
                        <td class="orange_bg">Серія</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Час початку</td>
                        <td>
                            28.12.2023 00:00 UTC
                        </td>
                        <td>
                            30.12.2023 00:00 UTC
                        </td>
                        <td>
                            01.01.2024 00:00 UTC
                        </td>
                        <td class="orange_bg">28.12.2023 00:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Час закінчення</td>
                        <td>29.12.2023 23:00 UTC</td>
                        <td>31.12.2023 23:00 UTC</td>
                        <td>02.01.2024 23:00 UTC</td>
                        <td class="orange_bg">02.01.2024 23:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Скоринг</td>
                        <td>HSRM</td>
                        <td>HSRP</td>
                        <td>HSRM</td>
                        <td class="orange_bg"></td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Призовий фонд</td>
                        <td>EUR 20,000 </td>
                        <td>EUR 20,000 </td>
                        <td>EUR 20,000 </td>
                        <td class="orange_bg">EUR 40,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <div class="tournament__cond-title">Призовий фонд турніру:</div>
        <div class="tournament__cond-text">
            <p><strong style="color: red">EUR 20,000</strong> (або еквівалент у валюті гравця) нараховується для кожного турніру на загальну суму <strong style="color: red">EUR 60,000</strong> у 3 турнірах.</p>
            <p>Будь ласка, перегляньте розділ <strong>Розподіл призового фонду</strong> для отримання додаткової інформації.</p>
        </div>

        <div class="tournament__cond-title">Tournament Scoring (Winning Mechanic):</div>
        <div class="tournament__cond-text">
            <p>
                <strong style="color: red">HSRP:</strong> гравці отримають очки за найбільшу виплату в одному раунді.<br>
                 <strong style="color: red">HSRM:</strong> гравці отримуватимуть бали за найвищий множник в одному раунді
            </p>
        </div>
        <div class="tournament__cond-title">Додаткові бали серії (механіка перемоги):</div>
        <div class="tournament__cond-text">
            <p>Гравці отримують додаткові бали серії протягом усієї кампанії на основі наступних критеріїв</p>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">Для кваліфікації в кожній регулярній TNT</td>
                    <td>200 балів</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">За кожні 100 євро ставок протягом усього регулярного TNT</td>
                    <td>1 бал</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">За місце в регулярному ТНТ</td>
                    <td>
                        <table class="tablett2">
                            <tr>
                                <td>1st</td>
                                <td>1000 балів</td>
                            </tr>
                            <tr>
                                <td>2nd</td>
                                <td>500 балів</td>
                            </tr>
                            <tr>
                                <td>3rd</td>
                                <td>250 балів</td>
                            </tr>
                            <tr>
                                <td>4th-5th</td>
                                <td>200 балів</td>
                            </tr>
                            <tr>
                                <td>6th-10th</td>
                                <td>150 балів</td>
                            </tr>
                            <tr>
                                <td>11th-100th</td>
                                <td>50 балів</td>
                            </tr>
                            <tr>
                                <td>101st-150th</td>
                                <td>30 балів</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <img src="/img/tournaments/fireworks-of-fortune/1200x300_fireworks_of_fortune.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
        </div>

        <div class="tournament__cond-text">
            <div class="flex">
                <div>
                    <div class="tournament__cond-title">Розподіл призового фонду:</div>
                    <p>
                        1. Призи автоматично виплачуються переможцям, якщо ввімкнено <strong>функцію автоматичних виплат</strong>. Гравець отримує спливаюче вікно Win після
                         Турнір завершено, а остаточні результати відображаються в таблиці лідерів.<br>
                         2. Якщо <strong>функцію автоматичних виплат вимкнено</strong>, гравці отримують спливаюче вікно «Виграш» із повідомленням про те, що приз буде вручну зараховано на
                         рахунків Оператором протягом <strong style="color: red;">72 годин</strong>.<br>
                         3. Якщо функція автоматичних виплат недоступна через будь-які технічні проблеми, Оператори винагороджують гравців вручну на основі Переможців
                         Звіт надано 3 Oaks.
                    </p>
                </div>
                <img src="/img/tournaments/fireworks-of-fortune/600x700_fireworks_of_fortune.jpg" alt="tree" style="width: 200px; margin-left: 30px; margin-top: 30px;">
            </div>
           <div class="flex">
                <div style="width: 50%;">
                    <div class="pdis_table_title">Звичайний призовий фонд турніру в євро</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>Від</td>
                                <td>До</td>
                                <td>Приз</td>
                                <td>Сума</td>  
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr>   
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr>   
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 1,000</td>    
                                <td>€ 1,000</td>    
                            </tr>   
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 750</td>    
                                <td>€ 750</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 600</td>    
                                <td>€ 600</td>    
                            </tr>
                            <tr>
                                <td>6</td>    
                                <td>8</td>    
                                <td>€ 400</td>    
                                <td>€ 1,200</td>    
                            </tr>  
                            <tr>
                                <td>9</td>    
                                <td>22</td>    
                                <td>€ 250</td>    
                                <td>€ 3,500</td>    
                            </tr> 
                            <tr>
                                <td>23</td>    
                                <td>50</td>    
                                <td>€ 150</td>    
                                <td>€ 4,200</td>    
                            </tr> 
                            <tr>
                                <td>51</td>    
                                <td>100</td>    
                                <td>€ 75</td>    
                                <td>€ 3,750</td>    
                            </tr> 
                            <tr>
                                <td>101</td>    
                                <td>150</td>    
                                <td>€ 30</td>    
                                <td>€ 1,500</td>    
                            </tr> 
                            <tr>
                                <td> </td>    
                                <td> </td>    
                                <td> </td>    
                                <td>€ 20,000</td>    
                            </tr> 
                        </tbody> 
                    </table>
                </div>
                <div style="width: 50%;">
                    <div class="pdis_table_title">SERIES Leaderboard Prize Pool in EUR</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>Від</td>
                                <td>До</td>
                                <td>Приз</td>
                                <td>Сума</td>  
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 4,000</td>    
                                <td>€ 4,000</td>    
                            </tr> 
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 3,500</td>    
                                <td>€ 3,500</td>    
                            </tr> 
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 3,000</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 2,500</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>6</td>    
                                <td>9</td>    
                                <td>€ 1000</td>    
                                <td>€ 4,000</td>    
                            </tr> 
                            <tr>
                                <td>10</td>    
                                <td>17</td>    
                                <td>€ 750</td>    
                                <td>€ 6,000</td>    
                            </tr> 
                            <tr>
                                <td>18</td>    
                                <td>26</td>    
                                <td>€ 400</td>    
                                <td>€ 3,600</td>    
                            </tr> 
                            <tr>
                                <td>27</td>    
                                <td>41</td>    
                                <td>€ 200</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>42</td>    
                                <td>66</td>    
                                <td>€ 100</td>    
                                <td>€ 2,500</td>    
                            </tr>
                            <tr>
                                <td>67</td>    
                                <td>91</td>    
                                <td>€ 75</td>    
                                <td>€ 1,875</td>    
                            </tr>
                            <tr>
                                <td>92</td>    
                                <td>143</td>    
                                <td>€ 50</td>    
                                <td>€ 2,600</td>    
                            </tr>
                            <tr>
                                <td>144</td>    
                                <td>200</td>    
                                <td>€ 25</td>    
                                <td>€ 1,425</td>    
                            </tr>
                            <tr>
                                <td></td>    
                                <td></td>    
                                <td></td>    
                                <td>€ 40,000</td>    
                            </tr>
                        </tbody>
                    </table>
                </div>
           </div>
        </div>


       
        <div class="tournament__cond-title">Правила та умови:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Беручи участь у турнірі, ви погоджуєтеся з положеннями та умовами, викладеними тут, а також умовами, наданими
                оператор.<br>
                2. У екстрених випадках 3 Oaks Gaming залишає за собою право в будь-який час скасувати акцію Sparkling Spins, включаючи платежі
                для виграшів, якщо це буде визнано необхідним.<br>
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
