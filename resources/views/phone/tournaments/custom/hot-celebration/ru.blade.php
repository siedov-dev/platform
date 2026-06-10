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
        color: #fff;
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
        width: 80%;
        height: auto;
        margin: 0 auto;
    }
    .tournament__list li {
        margin: 1rem 0;
        font-size: 0.875rem;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="flex ai-center">
            <div class="tournament__cond-text">

                <div class="tournament__cond-title">Terms and conditions:</div>
                <ul class="tournament__list">
                    <li>Сетевой турнир Evoplay Hot Celebration доступен только в реальном режиме.</li>

                    <li>Сетевой турнир Evoplay Hot Celebration проводится на 12 (двенадцать) человек по согласованию
                        дней в августе месяце 2022 года. Турнир состоится между
                        00:01 UTC 11 августа 2022 г. и 22 августа 2022 г. до 23:59 UTC.</li>

                    <li>Сумма призового фонда турнира составляет 15 000 евро и будет распределена
                        среди 100 лучших игроков, набравших самые высокие баллы в списках лидеров.</li>

                    <li>Минимальная ставка, необходимая для участия в турнире, составляет 0,20 евро или
                        эквивалент в местной валюте. Чем больше очков, тем выше поднимается игрок
                        Таблица лидеров. Первые 100 игроков в таблице лидеров получат призы. </li>

                    <li>Призовой фонд и минимальная ставка для этой кампании устанавливаются в евро и
                        могут быть подвержены колебаниям конвертации валюты.</li>
                </ul>
            </div>

        </div>

        <div class="flex ai-center">
            <img src="/img/tournaments/spins-of-love/masks.png" alt="Gambling lady" class="tournament__image">
        </div>

        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text">
                <div class="mt-2"></div>
                <ul class="tournament__list">

                    <li>Каждый спин влияет на общий счет игрока, который отображается на
                        таблица лидеров. Общий балл рассчитывается с учетом баллов, набранных во время всех
                        раундов, сыгранных в участвующих играх.</li>
                    <li>У турнира есть квалифицированная механика: накопление общего выигрыша.</li>

                    <li>У турнира есть список квалификационных игр и призовой фонд (евро), который
                        распределяется между победителями таблицы лидеров по данным турнира
                        правила.</li>

                    <li>Если несколько Участников набирают одинаковое количество баллов в процессе
                        при подсчете результатов турнира дается более высокая позиция в рейтинге
                        Участнику, набравшему первое количество очков.</li>

                    <li>Денежные призы будут зачислены на счета игроков в виде средств, которые можно снять.
                        средств казино после окончания турнира.</li>

                    <li>Evoplay оставляет за собой право аннулировать результаты или не выплачивать приз, если все или
                        часть счета является результатом любой очевидной ошибки, ошибки или технической неисправности
                        (включая неправильные игровые выплаты), будь то машина или человек
                        ошибка в отношении любой из участвующих игр.</li>
                </ul>
            </div>
        </div>
        <div class="flex ai-center mt-2">
            <img src="/img/tournaments/hot-celebration/evo.png" alt="Gambling guy" class="tournament__image">
        </div>

        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="mt-4"></div>
                <ul class="tournament__list">
                    <li>Evoplay также оставляет за собой право аннулировать баллы или не выплачивать призы.
                        где, по мнению, вся часть счета является результатом обмана или сговора
                        с другими игроками.</li>

                    <li>Система вознаграждения рассчитывается в соответствии с призовым фондом и
                        установить количество участвующих лидеров.</li>

                    <li><b>Участие в играх:</b>
                        Полуночное шоу, Candy Dreams, Anubis Moon, Индиана квест,
                        Проклятие Фараона, Руны Судьбы, Таверна Дракона, Лесные Сны, Древо Света</li>

                    <li>Турнирный рейтинг: общее количество побед.</li>
                    <li>Критерии турнира: турнирные очки начисляются за
                        каждый выигрыш (минимальная ставка 0,20 EUR)</li>

                    <li><b>Пример:</b> если вы ставите 1 евро и ваш выигрыш составляет 12,80 евро, то
                        вы зарабатываете 12,8 балла. Если во втором раунде игры вы
                        поставь €1 и выиграй €7,50, то заработаешь 7,5
                        баллов, а общее количество заработанных баллов составит 20,3.
                        Любой выигрыш с минимальной ставкой конвертируется в
                        БАЛЛЫ</li>
                </ul>
            </div>

        </div>



        <div class="flex ai-center mt-2" style="background: url('/img/tournaments/hot-celebration/image-047.jpg') no-repeat center; background-size: cover; border-radius: 30px; overflow: hidden">
            <div class="tournament__cond-text" style="width: 20rem; padding-bottom: 5rem; margin-right: auto; margin-left: auto;">
                <table class="tournament__table mt-2" style=" background: linear-gradient(#5f32c699, #32187899);">
                    <thead>
                    <tr>
                        <th>Игрок</th>
                        <th>Приз</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1st</td><td>2 481 €</td></tr>
                    <tr><td>2nd</td><td>1 900 €</td></tr>
                    <tr><td>3rd</td><td>1 500 €</td></tr>
                    <tr><td>4th</td><td>1 000 €</td></tr>
                    <tr><td>5th</td><td>900 €</td></tr>
                    <tr><td>6th</td><td>809 €</td></tr>
                    <tr><td>7th</td><td>600 €</td></tr>
                    <tr><td>8th</td><td>450 €</td></tr>
                    <tr><td>9th</td><td>330 €</td></tr>
                    <tr><td>10th - 19th</td><td>200 €</td></tr>
                    <tr><td>20th - 49th</td><td>50 €</td></tr>
                    <tr><td>50th - 100th</td><td>30 €</td></tr>
                    <tr><td colspan="2" style="text-align: center"><b>Total 15 000 €</b></td></tr>
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
