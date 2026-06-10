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
        <div class="tournament__cond-title mt-2">3 серии турниров Oaks Network <span style="color: #d8b818">Sparkling Spins</span> – Призовой фонд <span style="color: red">75,000</span> Euro</div>
        <div class="tournament__cond-text">
            Играй в Gold Nuggets, Crystal Scarabs, Olaf Viking, Coin Volcano, Sun of Egypt 3
            More Magic Apple, Sun of Egypt 2, Black Wolf 2, Egypt Fire, Green Chilli 2, Aztec Fire
            777 Coins, Hit more Gold!, Magic Apple, Green Chilli, 3 Hot Chillies, Grab the Gold!,Tiger Gems, Hit the Gold!, Sun of Egypt, и выигрывай 75,000 EURO!
        </div>
        
        <div class="flex">
            <div>
                <img src="/img/tournaments/sparkling-spins/344x515_sparkling_spins.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
            </div>
            <div>
                <div class="tournament__cond-title">Сверкающие вращения:</div>
                <div class="tournament__cond-text">
                    <span style="color: #d8b818;">20.12.2023 00:00</span> UTC до <span style="color: #d8b818;">25.12.2023 23:00</span> UTC
                </div>
                
                <div class="tournament__cond-title">Общий призовой фонд:</div>
                <div class="tournament__cond-text">
                    Общий призовой фонд этой кампании — <span style="color: red">75 000 евро</span> (или эквивалент в валюте игрока) — складывается из нескольких пулов согласно следующей структуре:<br>
                         <span style="color: red">75 000 евро</span> на период с <span style="color: red">20.12.2023 00:00 UTC</span> по <span style="color: red" ">25.12.2023 23:00 UTC</span>
                        <ul>
                            <li><span style="color: red">15 000 евро</span> присуждается за каждый регулярный турнир на общую сумму <span style="color: red">45 000 евро</span> в <span style= "color: red">3</span> турнира</li>
                             <li><span style="color: red">30 000 евро</span> присуждается через таблицу лидеров серии в 1 отдельной серии.</li>
                        </ul>
                </div>
                
                <div class="tournament__cond-title">Призовой фонд турнира:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">15 000 евро</strong> (или эквивалентная сумма в валюте игрока) присуждается за каждый турнир на общую сумму <strong style="color: red">45 000 евро</strong>. сильный> в 3-х турнирах.</p>
                     <p>Подробную информацию можно найти в разделе <strong>Распределение призового фонда</strong>.</p>
                </div>
                
                <div class="tournament__cond-title">Дополнительный призовой фонд серии:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">15 000 евро</strong> присуждается через таблицу лидеров серии в 1 отдельной серии.</p>
                     <p>Подробную информацию можно найти в <strong style="color: red">разделе распределения призового фонда</strong>. </p>
                </div>
            </div>
        </div>

        <div class="tournament__cond-title">Структура турнирной серии:</div>
        <div class="tournament__cond-text">
            <p><span style="color: red">1</span> индивидуальная серия с <span style="color: red">3</span> регулярными турнирами в каждой:</p>
            <table class="tnt_table">
                <thead class="">
                    <tr>
                        <td class="orange_bg" style="text-align: center;">Sparkling Spins 20.12 - 25.12</td>
                    </tr>
                </thead>
            </table>
            <table class="tnt_table mob_table">
                <tbody>
                    <tr>
                        <td class="orange_bg">TNTs</td>
                        <td>TNT1</td>
                        <td>TNT2</td>
                        <td>TNT3</td>
                        <td class="orange_bg">Ряд</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Время начала</td>
                        <td>
                            20.12.2023 00:00 UTC
                        </td>
                        <td>
                            22.12.2023 00:00 UTC
                        </td>
                        <td>
                            24.12.2023 00:00 UTC
                        </td>
                        <td class="orange_bg">20.12.2023 00:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Время окончания</td>
                        <td>21.12.2023 23:00 UTC</td>
                        <td>23.12.2023 23:00 UTC</td>
                        <td>25.12.2023 23:00 UTC</td>
                        <td class="orange_bg">25.12.2023 23:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Подсчет очков</td>
                        <td>MR</td>
                        <td>WR</td>
                        <td>MR</td>
                        <td class="orange_bg"></td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Призовой фонд</td>
                        <td>EUR 15,000 E</td>
                        <td>EUR 15,000 E</td>
                        <td>EUR 15,000 E</td>
                        <td class="orange_bg">EUR 30,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <div class="tournament__cond-title">Квалификация:</div>
        <div class="tournament__cond-text">
            <p><strong style="color: red">15 000 евро</strong> (или эквивалентная сумма в валюте игрока) присуждается за каждый турнир на общую сумму <strong style="color: red">45 000 евро</strong>. сильный> в 3-х турнирах.</p>
             <p>Подробную информацию можно найти в разделе <strong>Распределение призового фонда</strong>.</p>
        </div>

        <div class="tournament__cond-title">Подсчет очков в турнире (механика победы):</div>
        <div class="tournament__cond-text">
            <p>
                <strong style="color: red">Выиграйте гонку:</strong> если игрок сделал общую ставку <strong>1 евро</strong> и выиграл <strong>120 евро</strong>, он получит <strong>120 евро</strong>. strong>120 баллов</strong>.<br>
                 <strong style="color: red">Гонка с мультипликатором:</strong> если игрок сделал общую ставку <strong>2 евро</strong> и выиграл <strong>120 евро</strong>, он получит
                 120(выигрыш)÷2(ставка)= множитель 60x = <strong>60 очков</strong>.
            </p>
        </div>
        <div class="tournament__cond-title">Дополнительный подсчет очков в серии (механик-победитель):</div>
        <div class="tournament__cond-text">
            <p>Игроки получают дополнительные очки серии на протяжении всей кампании на основе следующих критериев:</p>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">За квалификацию в каждом регулярном турнире TNT</td>
                    <td>200 очков</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">За каждые 100 евро ставок в течение всего регулярного турнира TNT</td>
                    <td>1 очко</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">За место в штатном ТНТ</td>
                    <td>
                        <table class="tablett2 mob_table">
                            <tr>
                                <td>1st</td>
                                <td>1000 очков</td>
                            </tr>
                            <tr>
                                <td>2nd</td>
                                <td>500 очков</td>
                            </tr>
                            <tr>
                                <td>3rd</td>
                                <td>250 очков</td>
                            </tr>
                            <tr>
                                <td>4th-5th</td>
                                <td>200 очков</td>
                            </tr>
                            <tr>
                                <td>6th-10th</td>
                                <td>150 очков</td>
                            </tr>
                            <tr>
                                <td>11th-100th</td>
                                <td>50 очков</td>
                            </tr>
                            <tr>
                                <td>101st-150th</td>
                                <td>30 очков</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <img src="/img/tournaments/sparkling-spins/1200x300_sparkling_spins.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
        </div>

        <div class="tournament__cond-text">
            <div class="flex">
                <div>
                    <div class="tournament__cond-title">Распределение призового фонда:</div>
                    <p>
                        1. Призы выплачиваются победителям автоматически, если включена <strong>функция автоматической выплаты</strong>. Игрок получает всплывающее окно с выигрышем после
                         Турнир завершен, и окончательные результаты отображаются в таблице лидеров.<br>
                         2. Если <strong>функция автоматической выплаты отключена</strong>, игроки увидят всплывающее окно с сообщением о том, что приз будет вручную зачислен на их счет.
                         аккаунты Оператора в течение <strong style="color: red;">72 часов</strong>.<br>
                         3. Если функция автоматической выплаты недоступна из-за каких-либо технических проблем, Операторы награждают игроков вручную в зависимости от победителей.
                         Отчет предоставлен 3 Oaks.
                    </p>
                </div>
            </div>
           <div class="flex">
                <div style="width: 100%;">
                    <div class="pdis_table_title">Призовой фонд РЕГУЛЯРНОГО турнира в евро</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>С места</td>
                                <td>Разместить</td>
                                <td>Приз</td>
                                <td>Сумма</td>
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr>   
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 1,250</td>    
                                <td>€ 1,250</td>    
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
                                <td>€ 500</td>    
                                <td>€ 500</td>    
                            </tr>
                            <tr>
                                <td>6</td>    
                                <td>10</td>    
                                <td>€ 300</td>    
                                <td>€ 1,500</td>    
                            </tr>  
                            <tr>
                                <td>11</td>    
                                <td>20</td>    
                                <td>€ 200</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>21</td>    
                                <td>50</td>    
                                <td>€ 100</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>51</td>    
                                <td>100</td>    
                                <td>€ 50</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>101</td>    
                                <td>150</td>    
                                <td>€ 20</td>    
                                <td>€ 1,000</td>    
                            </tr> 
                            <tr>
                                <td> </td>    
                                <td> </td>    
                                <td> </td>    
                                <td>€ 15,000</td>    
                            </tr> 
                        </tbody> 
                    </table>
                </div>
                <div style="width: 100%;">
                    <div class="pdis_table_title">Призовой фонд таблицы лидеров SERIES в евро</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>С места</td>
                                <td>Разместить</td>
                                <td>Приз</td>
                                <td>Сумма</td>  
                            </tr>    
                        </thead> 
                        <tbody>
                            <tr>
                                <td>1</td>    
                                <td>1</td>    
                                <td>€ 3,000</td>    
                                <td>€ 3,000</td>    
                            </tr> 
                            <tr>
                                <td>2</td>    
                                <td>2</td>    
                                <td>€ 2,500</td>    
                                <td>€ 2,500</td>    
                            </tr> 
                            <tr>
                                <td>3</td>    
                                <td>3</td>    
                                <td>€ 2,000</td>    
                                <td>€ 2,000</td>    
                            </tr> 
                            <tr>
                                <td>4</td>    
                                <td>4</td>    
                                <td>€ 1,500</td>    
                                <td>€ 1,500</td>    
                            </tr> 
                            <tr>
                                <td>5</td>    
                                <td>5</td>    
                                <td>€ 1,000</td>    
                                <td>€ 1,000</td>    
                            </tr> 
                            <tr>
                                <td>6</td>    
                                <td>9</td>    
                                <td>€ 700</td>    
                                <td>€ 2,800</td>    
                            </tr> 
                            <tr>
                                <td>10</td>    
                                <td>17</td>    
                                <td>€ 400</td>    
                                <td>€ 3,200</td>    
                            </tr> 
                            <tr>
                                <td>18</td>    
                                <td>25</td>    
                                <td>€ 300</td>    
                                <td>€ 2,400</td>    
                            </tr> 
                            <tr>
                                <td>26</td>    
                                <td>41</td>    
                                <td>€ 200</td>    
                                <td>€ 3,200</td>    
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
                                <td>€ 30,000</td>    
                            </tr>
                        </tbody>
                    </table>
                </div>
           </div>
        </div>


       
        <div class="tournament__cond-title">Условия и положения:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Участвуя в турнире, вы соглашаетесь с Условиями, изложенными здесь, а также с условиями, предоставленными
                 оператор.<br>
                 2. В чрезвычайных случаях 3 Oaks Gaming оставляет за собой право отменить акцию Sparkling Spins в любое время, включая выплаты.
                 для выигрыша, если это будет сочтено необходимым.<br>
                 3. Только квалифицированные игроки могут претендовать на приз (см. раздел «Информация о кампании»).<br>
                 4. В турнире могут участвовать только раунды на реальные деньги.<br>
                 5. За участие в турнире не взимается дополнительная плата, кроме платы за вращение.<br>
                 6. Каждый игрок получит персональный турнирный идентификатор для таблицы лидеров.<br>
                 7. Если у двух или более игроков ничья, то те игроки, которые первыми наберут очки, разрешат ничью.<br>
                 8. Правила турнира могут быть изменены в любое время.<br>
                 9. Неисправность аннулирует все выплаты.
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
