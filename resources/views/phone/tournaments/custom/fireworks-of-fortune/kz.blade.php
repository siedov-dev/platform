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
        <div class="tournament__cond-title mt-2">3 Oaks Network турнирінің сериясы <span style="color: #d8b818">Fireworks of Fortune</span> – Жүлде қоры <span style="color: red">100,000</span> Euro</div>
        
        
        <div class="flex">
            <div>
                <img src="/img/tournaments/fireworks-of-fortune/344x515_fireworks_of_fortune.jpg" alt="tree" style="width: 100%; margin-top: 30px;">
            </div>
            <div>
                <div class="tournament__cond-title">Fireworks of Fortune:</div>
                <div class="tournament__cond-text">
                    <span style="color: #d8b818;">28.12.2023 00:00</span> UTC дейін <span style="color: #d8b818;">02.01.2024 23:00</span> UTC
                </div>
                
                <div class="tournament__cond-title">Жалпы жүлде қоры:</div>
                <div class="tournament__cond-text">
                    Осы науқанның жалпы жүлде қоры - <span style="color: red">100 000 EUR</span> (немесе ойыншының валюталық баламасы) - келесі құрылымға сәйкес бірнеше пулдан біріктірілген:<br>
                    <span style="color: red">100 000 EUR</span> <span style="color: red">28.12.2023 00:00 UTC</span> - <span style="color: red" ">02.01.2024 23:00 UTC</span>
                        <ul>
                            <li><span style="color: red">EUR 20,000</span> әрбір Тұрақты Турнир үшін жалпы сомасы беріледі <span style="color: red">EUR 60,000</span> жылы <span style="color: red">3</span> турнирлер</li>
                            <li><span style="color: red">EUR 40,000</span> 1 жеке сериядағы сериялардың көшбасшылар тақтасы арқылы беріледі</li>
                        </ul>
                </div>
                
                <div class="tournament__cond-title">Турнирдің жүлде қоры:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">EUR 20,000</strong>(немесе ойыншының валюталық эквиваленті) әрбір Турнир үшін жалпы сомаға беріледі<strong style="color: red">EUR 60,000</strong> 3 турнирде.</p>
                    <p>Қосымша мәліметтер алу үшін <strong>Жүлделік қорды бөлу</strong> бөлімін қараңыз.</p>
                </div>
                
                <div class="tournament__cond-title">Сериялардың қосымша жүлде қоры:</div>
                <div class="tournament__cond-text">
                    <p><strong style="color: red">40 000 еуро</strong> 1 жеке сериядағы Топтардың көшбасшылар тақтасы арқылы беріледі.</p>
                     <p>Толығырақ ақпарат алу үшін <strong style="color: red">Жүлделік қорды бөлу бөлімін</strong> тексеріңіз. </p>
                </div>
            </div>
        </div>

        <div class="tournament__cond-title">Турнир сериясының құрылымы:</div>
        <div class="tournament__cond-text">
            <p><span style="color: red">1</span> жеке серия <span style="color: red">3</span> Әрқайсысында тұрақты турнир:</p>
            <table class="tnt_table">
                <thead class="">
                    <tr>
                        <td class="orange_bg" style="text-align: center;">Fireworks of Fortune 28.12 - 02.01</td>
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
                        <td class="orange_bg">Сериялар</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Басталу уақыты</td>
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
                        <td class="orange_bg">Аяқтау уақыты</td>
                        <td>29.12.2023 23:00 UTC</td>
                        <td>31.12.2023 23:00 UTC</td>
                        <td>02.01.2024 23:00 UTC</td>
                        <td class="orange_bg">02.01.2024 23:00 UTC</td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Ұпай қою</td>
                        <td>HSRM</td>
                        <td>HSRP</td>
                        <td>HSRM</td>
                        <td class="orange_bg"></td>
                    </tr>
                    <tr>
                        <td class="orange_bg">Жүлде қоры</td>
                        <td>EUR 20,000 E</td>
                        <td>EUR 20,000 E</td>
                        <td>EUR 20,000 E</td>
                        <td class="orange_bg">EUR 40,000</td>
                    </tr>
                </tbody>
            </table>
        </div>
       

        <div class="tournament__cond-title">Турнирдің жүлде қоры:</div>
        <div class="tournament__cond-text">
            <p><strong style="color: red">EUR 20,000</strong> (немесе ойыншының валюталық эквиваленті) әрбір Турнир үшін жалпы сомаға беріледі<strong style="color: red">EUR 60,000</strong> 3 турнирде.</p>
            <p>Толығырақ ақпарат алу үшін <strong>Жүлделік қорды бөлу</strong> бөлімін қараңыз.</p>
        </div>

        <div class="tournament__cond-title">Турнирдегі ұпай саны (жеңімпаз механик):</div>
        <div class="tournament__cond-text">
            <p>
                <strong style="color: red">HSRP:</strong> ойыншылар бір раундта ең жоғары төлем үшін ұпай алады.<br>
                <strong style="color: red">HSRM:</strong> ойыншылар бір раундтағы ең жоғары мультипликатор үшін ұпай алады
          
            </p>
        </div>
        <div class="tournament__cond-title">Қосымша ұпайлар сериясы (жеңімпаз механик):</div>
        <div class="tournament__cond-text">
            <p>Ойыншылар келесі критерийлерге негізделген бүкіл науқан кезінде сериялық қосымша ұпайларды алады</p>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">Әрбір тұрақты TNT бойынша біліктілік үшін</td>
                    <td>200 ұпай</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">Бүкіл тұрақты TNT кезінде ұтыс тігудегі әрбір 100 еуро үшін</td>
                    <td>1 нүкте</td>
                </tr>
            </table>
            <table class="tablett">
                <tr>
                    <td style="width: 50%;">Тұрақты TNT-дегі орын үшін</td>
                    <td>
                        <table class="tablett2 mob_table">
                            <tr>
                                <td>1st</td>
                                <td>1000 ұпай</td>
                            </tr>
                            <tr>
                                <td>2nd</td>
                                <td>500 ұпай</td>
                            </tr>
                            <tr>
                                <td>3rd</td>
                                <td>250 ұпай</td>
                            </tr>
                            <tr>
                                <td>4th-5th</td>
                                <td>200 ұпай</td>
                            </tr>
                            <tr>
                                <td>6th-10th</td>
                                <td>150 ұпай</td>
                            </tr>
                            <tr>
                                <td>11th-100th</td>
                                <td>50 ұпай</td>
                            </tr>
                            <tr>
                                <td>101st-150th</td>
                                <td>30 ұпай</td>
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
                    <div class="tournament__cond-title">Жүлде қорын бөлу:</div>
                    <p>
                        1. <strong>автоматты төлеу мүмкіндігі</strong> қосылса, жүлделер жеңімпаздарға автоматты түрде төленеді. Ойыншы ойыннан кейін Win қалқымалы терезесін алады
                         Турнир аяқталды және соңғы нәтижелер көшбасшылар тақтасында көрсетіледі.<br>
                         2. Егер <strong>автоматты төлеу мүмкіндігі өшірілсе</strong>, ойыншыларға жүлде қолмен есептелетінін көрсететін Win қалқымалы терезесін алады.
                         Оператор шоттарын <strong style="color: red;">72 сағат</strong> ішінде.<br>
                         3. Кез келген техникалық ақауларға байланысты автоматты төлеу мүмкіндігі қолжетімсіз болса, Операторлар Жеңімпаздар негізінде ойыншыларды қолмен марапаттайды.
                         Есепті 3 Oaks ұсынған.
                    </p>
                </div>
            </div>
           <div class="flex">
                <div style="width: 100%;">
                    <div class="pdis_table_title">ЕВРО түріндегі тұрақты турнирдің жүлде қоры</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>Орыннан</td>
                                <td>Орналастыру</td>
                                <td>Жүлде</td>
                                <td>Қосынды</td>  
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
                <div style="width: 100%;">
                    <div class="pdis_table_title">SERIES Leaderboard жүлде қоры EUR</div>
                    <table class="pdis_table">
                        <thead>
                            <tr>
                                <td>Орыннан</td>
                                <td>Орналастыру</td>
                                <td>Жүлде</td>
                                <td>Қосынды</td> 
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


       
        <div class="tournament__cond-title">Ережелер мен шарттар:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Турнирге қатысу арқылы сіз осы жерде баяндалған Шарттар мен шарттармен келісесіз.
                 оператор.<br>
                 2. Төтенше жағдайларда 3 Oaks Gaming кез келген уақытта Sparkling Spins промо-промосынан, соның ішінде төлемдерден бас тартуға құқылы.
                 қажет болған жағдайда ұтыстар үшін.<br>
                 3. Жүлдені тек білікті ойыншылар талап ете алады (Науқан туралы мәліметтер бөлімін қараңыз).<br>
                 4. Турнирге тек нақты ақша раундтары қатыса алады.<br>
                 5. Айналдыру бағасынан басқа турнирге қатысу үшін қосымша ақы жоқ.<br>
                 6. Әрбір ойыншы көшбасшылар тақтасы үшін жеке турнир идентификаторын алады.<br>
                 7. Егер екі немесе одан да көп ойыншылар тең болса, ұпайды бірінші алған ойыншылар есепті бұзады.<br>
                 8. Турнир ережелерін кез келген уақытта өзгертуге болады.<br>
                 9. Ақаулық барлық төлемдерді жояды.
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
