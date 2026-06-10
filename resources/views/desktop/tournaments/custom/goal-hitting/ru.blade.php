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
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #FFFFFF;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
    .tournament__image {
        width: 40%;
        height: auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
    .tournament__list strong{
        color: #00b8ff;
    }

</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: 30rem;">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Условия и положения:</div>
                <ul class="tournament__list">
                    <li>Сетевой турнир Evoplay Goal Hitting Network доступен только в реальном режиме.</li>

                    <li>Сетевой турнир Evoplay Goal Hitting Network Tournament проводится в течение (7) семи согласованных дней в ноябре 2022 г. Турнир будет проходить с 00:01 UTC 17 ноября 2022 г. до 23 ноября 2022 г. до 23:59 UTC. </li>

                    <li>Сумма призового фонда турнира составляет <strong>10 000 евро</strong> и будет распределена между 100 лучшими игроками, набравшими наибольшее количество очков в списках лидеров.</li>

                    <li>Минимальная ставка, необходимая для участия в турнире, составляет <strong>0,50 евро</strong> или эквивалент в местной валюте. Чем больше очков, тем выше игрок поднимается в таблице лидеров. Первые 100 игроков в таблице лидеров получат призы. </li>

                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в <strong>евро</strong> и могут зависеть от колебаний конвертации валюты.</li>
                </ul>
            </div>

            <img src="/img/tournaments/goal-hitting/ball.png" alt="Gambling lady" class="tournament__image" style="margin-left: 2rem; filter: drop-shadow(0 0 10px #00b8ff)">
        </div>


        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/goal-hitting/cup.png" alt="Gambling man" class="tournament__image" style="margin-right: 3rem; filter: drop-shadow(0 0 10px #00b8ff);">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Каждый спин влияет на общий счет игрока, который отображается в таблице лидеров. Общий счет рассчитывается на основе очков, набранных во всех раундах участвующих игр.</li>
                    <li>У турнира есть квалифицированная механика: накопление общего выигрыша.</li>

                    <li>У турнира есть <strong>список квалификационных игр и призовой фонд</strong> (евро), который распределяется между победителями таблицы лидеров в соответствии с настоящими правилами турнира.</li>

                    <li>Если несколько Участников набирают одинаковое количество очков в процессе подсчета результатов турнира, более высокая позиция в рейтинге отдается Участнику, набравшему первое место.</li>

                    <li>Денежные призы будут зачислены на счета игроков в виде средств, которые могут быть сняты казино после окончания турнира.</li>

                    <li>Evoplay оставляет за собой право аннулировать баллы или не выплачивать приз, если все или часть баллов являются результатом какой-либо очевидной ошибки, ошибки или технической неисправности (включая неправильные игровые выплаты), вызванной машинной или человеческой ошибкой. в отношении любой из участвующих игр.</li>
                </ul>
            </div>
        </div>


        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay также оставляет за собой право аннулировать баллы или не выплачивать приз, если, по мнению, вся часть счета является результатом мошенничества или сговора с другими игроками.</li>


                    <li><strong>Турнирный рейтинг:</strong> общее количество побед.</li>
                    <li><strong>Турнирные критерии:</strong> турнирные очки начисляются за каждую победу (минимальная ставка 0,50 евро)</li>

                    <li><strong>Пример:</strong> если вы поставите 1 евро и ваш выигрыш составит 12,80 евро, вы заработаете 12,8 балла. Если во втором раунде игры вы поставите 1 евро, а выигрыш составит 7,50 евро, то вы заработаете 7,5 очков, а общее количество заработанных очков составит 20,3. Все выигрыши с минимальной ставкой конвертируются в БАЛЛЫ</li>
                </ul>
            </div>

            <img src="/img/tournaments/goal-hitting/owl.png" alt="Gambling lady" class="tournament__image" style="margin-left: 3rem;  filter: drop-shadow(0 0 10px #00b8ff);">
        </div>



        <div class="flex jc-center ai-center mt-2">
            <div style="background: url('/img/tournaments/goal-hitting/stadium.png') no-repeat center; background-size: cover; border-radius: 2rem; width: 50rem;">
                <div class="tournament__cond-text" style="width: 30rem; padding: 3rem; margin-right: auto; margin-left: auto;">
                    <table class="tournament__table mt-2" style=" background: linear-gradient(#8f173399, #4d4ab599);">
                        <thead>
                        <tr>
                            <th>Игрок</th>
                            <th>Приз</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr><td>1st</td><td>1 664 €</td></tr>
                        <tr><td>2nd</td><td>1 270 €</td></tr>
                        <tr><td>3rd</td><td>1 000 €</td></tr>
                        <tr><td>4th</td><td>667 €</td></tr>
                        <tr><td>5th</td><td>600 €</td></tr>
                        <tr><td>6th</td><td>539 €</td></tr>
                        <tr><td>7th</td><td>400 €</td></tr>
                        <tr><td>8th</td><td>300 €</td></tr>
                        <tr><td>9th</td><td>220 €</td></tr>
                        <tr><td>10th - 19th</td><td>133 €</td></tr>
                        <tr><td>20th - 49th</td><td>33 €</td></tr>
                        <tr><td>50th - 100th</td><td>20 €</td></tr>
                        <tr><td colspan="2" style="text-align: center"><b>Total 10 000 €</b></td></tr>
                        </tbody>
                    </table>
                </div>
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
