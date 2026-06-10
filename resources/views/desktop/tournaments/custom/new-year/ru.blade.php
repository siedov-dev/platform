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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">Новогодний турнир Smartsoft – <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Играйте в JetX, Balloon, FootballX, CricketX, PlinkoX, Multihot5, GeniesBonanza, MultiHotWays, BurningICE40 и другие игры, предоставленные Smartoft, выполняйте миссии по обороту и множителю, вращайте колесо таблицы лидеров и выигрывайте призы!
        </div>
        <div class="tournament__cond-title">Период кампании:</div>
        <div class="tournament__cond-text">
            Серия сетевых турниров проводится с <strong>01.12.2023 – 28.12.2023</strong>. Турнир будет разделен
             на два этапа; Призовой фонд каждого этапа составляет <strong>25 000 евро</strong>. Первый этап проводится с <strong>01.12.2023</strong> 11:00.
             AM (+4 UTC) по (включительно) <strong>14.12.2023</strong> 11:00 (+4 UTC) и второй этап проводится с
             <strong>15.12.2023</strong> с 11:00 (+4 UTC) до (включительно) <strong>28.12.2023</strong> 11:00 (+4 UTC).
        </div>
        

        <div class="tournament__cond-title">How to participate?</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                <p>
                    В начале акции игрокам необходимо просмотреть список участвующих игр и выполнить забег.
                     миссии. Призы можно выиграть, попав в число 500 лучших игроков в таблице лидеров.
                </p>
                <p>
                    Таблица лидеров работает по балльной системе. Для получения очков игроку необходимо вращать колесо таблицы лидеров.
                     Колесо таблицы лидеров содержит 6 различных очков: 1,2,10,25,50,100.
                </p>
                <p>
                    Чтобы получить право крутить колесо, игроку необходимо выполнить одну из 2-х различных типов миссий.
                </p>
                <p>
                    Первая миссия называется «Миссия оборота». Игроку предстоит пройти 10 различных этапов каждый.
                     день, и после выполнения каждого из них он/она получит 1 вращение на колесе таблицы лидеров. Оборот
                     Требования миссии:
                </p>
                <ul class="tournament__list pad_left_20">
                    <li>На первом этапе игрок должен сделать ставки на общую сумму 0,10 евро;</li>
                    <li>На втором этапе игрок должен сделать ставки на общую сумму 5,1 евро.</li>
                    <li>
                        На третьем этапе игрок должен сделать ставки на общую сумму 15,1 евро и так далее для последующих.
                         этапы.
                    </li>
                </ul>
                <table class="tournament__table" style="">
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
            <img src="/img/tournaments/new-year/tree.png" alt="tree" style="height: 400px; margin-left: 30px;">
        </div>
        <img src="/img/tournaments/new-year/tile.jpg" alt="tree" class="margintop_30"  style="width: 100%;">
        <div class="flex  margintop_30">
            <img src="/img/tournaments/new-year/tree2.png" alt="tree"style="height: 400px; margin-right: 50px;">
            <div class="tournament__cond-text">
                <p>
                     Вторая миссия называется «Мультипликационная миссия». Каждый раз, когда игрок достигает множителя X400 (или выше),
                     он/она получит 1 вращение на колесе очков. Если игрок наберет 850X, он/она получит 2 вращения колеса.
                 </p>
                 <p>
                     В течение дня игрок может получать вращения за обе миссии, но дневной лимит составляет всего 10 вращений.
                     из обеих или любой из каждой миссии. После достижения лимита страница миссий будет заморожена.
                 </p>
                 <p>
                     Прогресс миссий будет обновляться каждый день в 00:00 (UTC:00), и у игроков будет возможность начать
                     новая коллекция вращений колес и очков.
                 </p>
                 <p>
                     Для Crash Games существует дополнительное правило: ставка должна быть получена как минимум с множителем X1,5 или выше или
                     Потерянный.
                 </p>
                 <p>
                     Иногда может пройти до 5 минут, прежде чем прогресс колеса появится на главном экране промо-акции.
                     тем временем внутри колеса результаты обновляются немедленно.
                 </p>
                 <p>
                     После вращения колеса игрок сразу же получит таблицу лидеров.
                 </p>
                 <p>
                     Если 2 или более игроков имеют одинаковое количество очков в таблице лидеров, то лучшая позиция будет
                     вручается игроку, который заработал последние очки позже, чем другие игроки-участники.
                 </p>
            </div>
        </div>

        
        <div class="tournament__cond-title">Минимальная ставка для участия в турнире составляет 0,10 евро.</div>
        
        <div class="flex ai-center margintop_30">
            <div class="tournament__cond-text" style="margin: 0;">
                <table class="tournament__table"  style="margin: 0; min-width: 300px;">
                    <thead>
                        <tr>
                            <td>Призовое место</td>
                            <td>Призы</td>
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
            <img src="/img/tournaments/new-year/new_year.jpg" alt="tree"style="height: 362px; ">
        </div>

       
        <div class="tournament__cond-title">Условия и положения:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Участвуют только ставки, сделанные на реальные деньги на полной версии веб-сайта, в мобильной версии или в мобильном приложении.
                на турнире.<br>
                2. В этом турнире участвуют игры JetX, Balloon, FootballX, CricketX,
                PlinkoX, SlicerX, Multihot5, GeniesBonanza, BurningICE BurningICE10, BurningICE40, Джунгли,
                Вампиры, Самураи, Викинги, Joker's4Bonuses, TowerX, MultiHotWays, Волшебный сад 40, Волшебный сад
                10, Волшебный сад 5, ParaDice10, JetX3, Вавилон, Эльфы, Ацтеки, Crazyape, Ковбой, Лунный камень,
                фруктовый коктейль, Тьма, Аполлон, Летний коктейль, Эволюция, Арго, Dark100, Зомби, Вторая мировая война, CrazyHuntX.<br>
                3. Вы можете вращать колесо очков в течение 30 секунд после его появления. В случае бездействия, после 30
                секунд колесо автоматически начнет вращаться, и игрок получит случайную награду.<br>
                4. Очки и прогресс таблицы лидеров будут отображаться в лобби участвующих игр.<br>
                5. Победителем турнира станет игрок, набравший максимальное количество очков.<br>
                6. По окончании акции в течение 3 рабочих дней игроки-победители получат призы.<br>
                7. В случае потери соединения или возникновения технической проблемы все задействованные спины считаются недействительными и могут быть отменены.
                не включаться в результаты турнира.<br>
                8. Казино и Smartsoft имеют право аннулировать любые заработанные баллы или удержать призы, если они
                считаю, что результат полностью или частично является результатом мошенничества или сговора с другими игроками.<br>
                9. Smartsoft оставляет за собой право изменить, приостановить или отменить данное предложение в любое время.<br>
                10. Принимая участие в этом предложении, клиент соглашается с настоящими условиями и общими положениями.
                условия казино.<br>
                11. Все решения, принятые руководством Казино, являются окончательными.
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
