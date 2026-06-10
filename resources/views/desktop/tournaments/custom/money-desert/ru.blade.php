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
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>

    <div class="article__text">
        <div class="tournament__cond-title mt-2">3 Oaks сетевой турнир <span style="color: rgb(255, 157, 0)">Money Desert</span> – Призовой фонд <span style="color: red">50,000</span> Euro</div>
        <div class="tournament__cond-text">
            Играйте в Olaf Viking, Crystal Scarabs, Grab more Gold!, 3 Coins: Egypt, Egypt Fire, Sun of Egypt 2, Coin Volcano, Sun of Egypt 3, More Magic Apple, Black Wolf 2, Green Chilli 2, Aztec Fire, Green Chilli, Hit the Gold! и выиграйте призы!
        </div>
        <div class="tournament__cond-title">Период кампании:</div>
        <div class="tournament__cond-text">
            <strong>07.12.2023 09:00 UTC</strong> до <strong>13.12.2023 23:59 UTC</strong>
        </div>
        

        <div class="tournament__cond-title">Подробности кампании</div>
        <div class="flex ">
            <div class="tournament__cond-text">
                
                <table class="tournament__table" style="color: #fff;">
                    <tbody>
                        <tr>
                            <td>Призовой фонд</td>
                            <td>EUR 50,000</td>
                        </tr>
                        <tr>
                            <td>Квалификация</td>
                            <td>
                                 Чтобы пройти квалификацию и иметь право претендовать на приз, игрок должен выполнить следующие условия:<br>
                                 1. <strong>Минимальная ставка</strong> составляет <strong style="color: red">0,2 евро</strong>,<br>
                                 2. Минимальное <strong>количество вращений</strong> составляет <strong style="color: red">150</strong><br>.
                                 Все очки, набранные на этапе квалификации, сохраняются и засчитываются игроку после прохождения
                                 квалификация. Неквалифицированные игроки также отображаются в таблице лидеров, но линия для неквалифицированных игроков отображается серым цветом до тех пор, пока они не пройдут квалификацию.
                             </td>
                        </tr>
                        <tr>
                            <td>Подсчет очков в турнире</td>
                            <td>
                                <strong style="color: red">Выиграйте гонку:</strong> если игрок сделал общую ставку <strong>1 евро</strong> и выиграл <strong>120 евро</strong>, он получит <strong>120 евро</strong>. strong>120 баллов</strong>.<br>
                                 <strong style="color: red">Гонка с мультипликатором:</strong> если игрок сделал общую ставку <strong>2 евро</strong> и выиграл <strong>120 евро</strong>, он получит
                                 120(выигрыш)÷2(ставка)= множитель 60x = <strong>60 очков</strong>.
                            </td>
                        </tr>
                        <tr>
                            <td>Внутриигровая таблица лидеров</td>
                            <td>
                                <p>Во время турнира таблица лидеров обновляется в режиме реального времени при каждом вращении игрока. Без
                                    активности игрока, статистика игрока по умолчанию обновляется после 10-секундной синхронизации между игрой и
                                    промо-сервер.
                                </p>
                                <p>
                                    Выигрышные очки отображаются и сортируются в таблице лидеров турнира – результаты лучших игроков
                                     Всегда на вершине.
                                </p>
                                <p>
                                    При формировании итоговой таблицы лидеров мы оставляем за собой 5-минутную задержку, чтобы предотвратить всю возможную потерю данных.
                                     сценарии; при окончательных расчетах таблица лидеров будет отображаться полосами до тех пор, пока она не будет завершена.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
            </div>
            <img src="/img/tournaments/money-desert/344x515_money_desert.jpg" alt="tree" style="height: 400px; margin-left: 30px; margin-top: 1rem;">
        </div>
        <img src="/img/tournaments/money-desert/1200x300_money_desert.jpg" alt="tree" class="margintop_30"  style="width: 100%;">

        <div class="flex  margintop_30">
            <img src="/img/tournaments/money-desert/600x800_money_desert.jpg" alt="tree"style="height: 400px; margin-right: 50px;">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title" style="margin: 0;">Распределение призового фонда</div>
                <ul class="tournament__list pad_left_20">
                    <li>Призы выплачиваются победителям автоматически, если включена функция автоматической выплаты. Победитель получает всплывающее окно с выигрышем после
                        Турнир завершен, и окончательные результаты отображаются в таблице лидеров.
                    </li>
                    <li>
                        Если функция автоматической выплаты отключена, игроки видят всплывающее окно с сообщением о том, что приз будет зачислен на их счета вручную.
                         Оператором за 72 часа
                    </li>
                    <li>
                        Если функция автоматической выплаты недоступна из-за каких-либо технических проблем, операторы награждают игроков вручную в зависимости от победителей.
                         Отчет предоставлен 3 Oaks.
                    </li>
                    <li>
                        Общий призовой фонд Турнира составляет 50 000 евро (или эквивалент в валюте игрока).
                    </li>
                    <li>
                        Таблица распределения призов в евро:
                    </li>
                </ul>
                <table class="tournament__table margintop_30">
                    <thead>
                        <tr>
                            <td>Место от</td>
                            <td>Место до</td>
                            <td>Приз</td>
                            <td>Сумма</td>
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
       
        <div class="tournament__cond-title">Условия и положения:</div>
        
        <div class="tournament__cond-text">
            <p>
                1. Участвуя в турнире, вы соглашаетесь с Условиями, изложенными здесь, а также с условиями, предоставленными
                 оператор.<br>
                 2. В чрезвычайных случаях 3 Oaks Gaming оставляет за собой право отменить акцию Money Desert в любое время, включая оплату
                 выигрыши, если это будет сочтено необходимым.<br>
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
