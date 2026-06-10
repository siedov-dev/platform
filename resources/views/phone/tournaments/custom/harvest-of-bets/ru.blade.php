@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp

<style>
    .tournament__list ul {
        margin: 0.5rem 0 2rem 1.5rem;
    }
    .tournament__list ul li {
        font-size: 0.875rem;
        color: #7a79b0;
    }
    .tournament__list ul li::before {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #9d8b53;
    }
    .tournament__table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid #3c3b89;
        box-shadow: #0d8f1b 0 0 5px;
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFF;
        border-bottom: 1px solid #3c3b89;
        font-weight: 500;
        text-align: center;
    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table tr:last-child td{
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__table th {
        color: #fff;
        background-color: #29b24880;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
        text-align: center;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Сетевой турнир Evoplay Harvest Of Bets доступен для игры только в реальном режиме.</li>
                    <li>Сетевой турнир Evoplay Harvest of Bets проводится в течение 12 (двенадцати) согласованных дней в сентябре месяце 2022 года. Турнир состоится в 00:01 UTC 19 сентября 2022 г. и 30 сентября 2022 г. до 23:59 UTC</li>
                    <li>Сумма призового фонда турнира составляет 12 000 евро и будет распределена среди 100 лучших игроков, которые имеют самые высокие баллы в списке лидеров.</li>
                    <li>Минимальная ставка, необходимая для участия в турнире, составляет 0,50 евро. или эквивалент в местной валюте. Чем больше баллов, тем выше игрок попадает в таблицу лидеров. Первые 100 игроков получат призы.</li>
                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в евро и могут быть подвержены колебаниям конвертации валюты.</li>
                    <li>Каждый спин влияет на общий счет игрока, который отображается в таблице лидеров. Общий счет рассчитывается с помощью очков, набранных во всех раундах, сыгранных в участвующих играх.</li>
                </ul>
            </div>

        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/harvest-of-bets/rept2.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>У турнира есть квалифицированная механика: накопление общей ставки. Турнирные очки начисляются за каждую ставку (минимальная ставка 0,50 евро)</li>
                    <li>Пример. Если вы поставите 1 евро, неважно, выиграете вы или нет, вы все равно получите 1 очко. Если во втором раунде игры вы поставите €2, то получите 2 очка, а общее количество заработанных очков будет равно 3. 1 € = 1 ОЧКО</li>
                    <li>У турнира есть список квалификационных игр и призовой фонд (евро), который распределяется между победителями в соответствии с правилами турнира.</li>
                    <li>Если несколько участников набирают одинаковое количество баллов, то более высокую позицию в таблице лидеров получает участник, набравший первое место.</li>
                    <li>Денежные призы будут зачислены на счета игроков казино в виде средств, которые можно вывести после окончания акции.</li>
                    <li>Evoplay оставляет за собой право аннулировать результаты или не выплачивать призы в случае, если все или часть очков являются результатом какой-либо очевидной ошибки, ошибки или технической неисправности (включая неправильные игровые выплаты), вызванной машиной или человеком. ошибка в любой из участвующих игр.</li>
                    <li>Evoplay также оставляет за собой право аннулировать баллы или не выплачивать приз, если, по мнению, вся часть счета является результатом мошенничества или сговора с другими игроками.</li>
                </ul>
            </div>
        </div>

        <div class="flex jc-center">
            <img src="/img/tournaments/harvest-of-bets/evo.png" alt="Lion head" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 0.9;">
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 90%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/harvest-of-bets/tbg.jpg"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Player</th>
                        <th>Prize</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 985 €</td>
                    <tr><td>2</td><td>1 520 €</td>
                    <tr><td>3</td><td>1 200 €</td>
                    <tr><td>4</td><td>800 €</td>
                    <tr><td>5</td><td>720 €</td>
                    <tr><td>6</td><td>647 €</td>
                    <tr><td>7</td><td>480 €</td>
                    <tr><td>8</td><td>360 €</td>
                    <tr><td>9</td><td>264 €</td>
                    <tr><td>10-19</td><td>160 €</td>
                    <tr><td>20-49</td><td>40 €</td>
                    <tr><td>50-100</td><td>24 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 12 000 €</b></td></tr>
                    </tbody>
                </table>
            </div>
        </div>




    </div>



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
