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
        <div class="tournament__cond-title mt-2">3 Oaks Network турнирі <span style="color: rgb(255, 157, 0)">Money Desert</span> – Жүлде қоры <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Олаф Викинг, Crystal Scarabs ойнаңыз, көбірек алтын алыңыз!, 3 монета: Египет, Египет оты, Египет күні 2, монета жанартауы, Египет күні 3, көбірек сиқырлы алма, қара қасқыр 2, жасыл чили 2, ацтек оты, жасыл чили , Алтынға жетіңіз!, және сыйлықтар ұтып алыңыз!
        </div>
        <div class="tournament__cond-title">Науқан кезеңі:</div>
        <div class="tournament__cond-text">
            <strong>07.12.2023 09:00 UTC</strong> to <strong>13.12.2023 23:59 UTC</strong>
        </div>
        

        <div class="tournament__cond-title">Науқан мәліметтері</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                
                <table class="tournament__table mob_table" style="color: #fff;">
                    <tbody>
                        <tr>
                            <td>Жүлде қоры</td>
                            <td>EUR 50,000</td>
                        </tr>
                        <tr>
                            <td>Біліктілік</td>
                            <td>
                                Білікті болу және жүлдені талап ету құқығына ие болу үшін ойыншы келесі шарттарды орындауы керек:<br>
                                 1. <strong>Ең аз ставка</strong> <strong style="color: red">0,2 EUR</strong>,<br>
                                 2. Ең аз <strong>Айналдыру саны</strong> - <strong style="color: red">150</strong><br>
                                 Біліктілік кезеңінде жиналған барлық ұпайлар сақталады және өткеннен кейін ойыншыға есептеледі
                                 біліктілік. Сондай-ақ біліктілігі жоқ ойыншылар көрсетіледі
                            </td>
                        </tr>
                        <tr>
                            <td>Tournament Scoring</td>
                            <td>
                                <strong style="color: red">ЖЕҢІС ЖАРЫСЫ:</strong> егер ойыншы <strong>1 EUR</strong> жалпы ставкасын қойып, <strong>120 EUR</strong> ұтып алса, ойыншы << алады. strong>120 ұпай</strong>.<br>
                                 <strong style="color: red">МУЛЬТИПЛИКАЛЫҚ ЖАРЫС:</strong> егер ойыншы <strong>2 EUR</strong> жалпы ставкасын қойып, <strong>120 EUR</strong> ұтып алса, ойыншы алады.
                                 120(жеңіс)÷2(ставка)= 60x мультипликатор = <strong>60 ұпай</strong>.
                            </td>
                        </tr>
                        <tr>
                            <td>Ойын ішіндегі көшбасшылар тақтасы</td>
                            <td>
                                <p>Турнир барысында көшбасшылар тақтасы әрбір ойыншының айналуымен нақты уақыт режимінде жаңартылады. А жоқ
                                    ойыншының белсенділігі, ойыншы статистикасы ойын мен арасындағы 10 секундтық синхрондау үшін әдепкі бойынша жаңартылады
                                    промо-сервер.
                                </p>
                                <p>
                                    Жеңіс ұпайлары Турнирдің көшбасшылар тақтасында көрсетіледі және сұрыпталады - ең жақсы ойыншылардың нәтижелері
                                     әрқашан ТОПта.
                                </p>
                                <p>
                                    Қорытынды көшбасшылар тақтасын құру кезінде біз барлық ықтимал деректер жоғалуын болдырмау үшін 5 минуттық кідірісті сақтаймыз
                                     сценарийлер; соңғы есептеулерді жасағанда, көшбасшылар тақтасы аяқталғанша жолақ болып көрінеді.
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
                <div class="tournament__cond-title" style="margin: 0;">Жүлде қорын бөлу</div>
                <ul class="tournament__list pad_left_20 ">
                    <li>Автоматты төлеу мүмкіндігі қосылған болса, жүлделер жеңімпаздарға автоматты түрде төленеді. Жеңімпаз ойыннан кейін Win қалқымалы терезесін алады
                        Турнир аяқталды және қорытынды нәтижелер көшбасшылар тақтасында көрсетіледі.
                    </li>
                    <li>
                        Автоматты төлеу мүмкіндігі өшірілсе, ойыншылар жүлденің олардың шоттарына қолмен аударылатыны туралы Win Pop-Up терезесін алады.
                        Оператор 72 сағат ішінде
                    </li>
                    <li>
                        Кез келген техникалық ақауларға байланысты автоматты төлеу мүмкіндігі қолжетімсіз болса, Операторлар Жеңімпаздар негізінде ойыншыларды қолмен марапаттайды.
                        Есепті 3 Oaks ұсынған.
                    </li>
                    <li>
                        Турнирдің жалпы жүлде қоры 50 000 еуро (немесе ойыншының валюталық баламасы)
                    </li>
                    <li>
                        Еуродағы жүлделерді бөлу кестесі:
                    </li>
                </ul>
                <table class="tournament__table margintop_30 mob_table">
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
       
        <div class="tournament__cond-title">Ережелер мен шарттар:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Турнирге қатысу арқылы сіз осы жерде баяндалған Шарттар мен шарттармен келісесіз.
                 оператор.<br>
                 2. Төтенше жағдайларда 3 Oaks Gaming кез келген уақытта Money Desert промо-акциясынан бас тартуға құқылы, соның ішінде
                 қажет болған жағдайда ұтыстар.<br>
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
