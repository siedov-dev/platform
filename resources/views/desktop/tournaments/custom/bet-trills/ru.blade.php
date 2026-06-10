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
    .tournament__list strong{
        color: #00bcff;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center" style="position: relative">
            <div class="tournament__cond-text" style="padding-right: 0px;">

                <div class="tournament__cond-title">Положения и условия:</div>
                <ul class="tournament__list">
                    <li>Сетевой турнир Evoplay Bet Trills доступен только в реальном режиме.</li>
                    <li>Сетевой турнир Evoplay Bet Trills проводится с 00:01 <strong>15 мая 2023 г. и 24 мая 2023 г.</strong> до 23:59 UTC</li>
                    <li>Сумма турнирного приза составляет <strong>10 000 евро</strong> и будет распределена между <strong>100</strong> лучшими игроками, набравшими наибольшее количество очков в списках лидеров.</li>
                    <li>Минимальная ставка, необходимая для участия в турнире, составляет <strong>0,20 евро</strong> или эквивалент в местной валюте. Первые <strong>100</strong> игроков в таблице лидеров получат призы.</li>
                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в <strong>евро</strong> и могут зависеть от колебаний конвертации валюты.</li>
                    <li>Каждый спин влияет на общий счет игрока, который отображается в таблице лидеров. Общий счет рассчитывается на основе очков, набранных во всех раундах участвующих игр.</li>
                </ul>
            </div>

            <img src="/img/tournaments/bet-trills/girl1.png" alt="girl1" style="margin-left: .5rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 1;">
        </div>

        <div class="flex jc-center mt-1">
            <img src="/img/tournaments/bet-trills/evo.png" alt="girl1" style="margin-left: .5rem; position: relative; right: 0; bottom: 0; width: 30%; opacity: 1;">
        </div>

        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/bet-trills/girl2.png" alt="girl2" style="margin-right: 1rem; position: relative; right: 0; bottom: 0; width: 300px; opacity: 1;">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">
                    <li>У турнира есть квалифицированная механика: <strong>накопление общей ставки</strong>.</li>
                    <li><strong>Пример:</strong> если вы поставите 1 евро и ваш выигрыш составит 12,80 евро, вы заработаете 12,8 балла. Если во втором раунде игры вы поставите 1 евро, а выигрыш составит 7,50 евро, то вы заработаете 7,5 очков, а общее количество заработанных очков составит 20,3. Любой выигрыш с минимальной ставкой конвертируется в <strong>БАЛЛЫ</strong></li>
                    <li>У турнира есть список квалификационных игр и призовой фонд (евро), который распределяется между победителями в соответствии с правилами турнира.</li>
                    <li>Если несколько участников набирают одинаковое количество баллов, то более высокую позицию в таблице лидеров получает участник, набравший первое место.</li>
                    <li>Денежные призы будут зачислены на счета игроков казино в виде средств, которые можно вывести после окончания акции.</li>
                    <li>Evoplay оставляет за собой право аннулировать результаты или не выплачивать призы в случае, если все или часть очков являются результатом какой-либо очевидной ошибки, ошибки или технической неисправности (включая неправильные игровые выплаты), вызванной машиной или человеком. ошибка в любой из участвующих игр.</li>
                    <li>Evoplay также оставляет за собой право аннулировать баллы или не выплачивать приз, если, по мнению, вся часть счета является результатом мошенничества или сговора с другими игроками.</li>                </ul>
            </div>
        </div>


        <div class="flex jc-center mt-2">
            <div class="tournament__cond-text flex jc-center" style="width: 50%">
                <table class="tournament__table mt-2" style='background-image: url("/img/tournaments/bet-trills/tbg3.png"); background-size: cover;'>
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>1 664 €</td>
                    <tr><td>2</td><td>1 270 €</td>
                    <tr><td>3</td><td>1 000 €</td>
                    <tr><td>4</td><td>667 €</td>
                    <tr><td>5</td><td>600 €</td>
                    <tr><td>6</td><td>539 €</td>
                    <tr><td>7</td><td>400 €</td>
                    <tr><td>8</td><td>300 €</td>
                    <tr><td>9</td><td>220 €</td>
                    <tr><td>10-19</td><td>133 €</td>
                    <tr><td>20-49</td><td>33 €</td>
                    <tr><td>50-100</td><td>20 €</td>
                    <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
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
