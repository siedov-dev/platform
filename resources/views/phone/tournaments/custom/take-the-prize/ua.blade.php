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
        color: white;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament__table tr:last-child td {
        border-bottom: none;
    }
    .tournament__table th {
        color: yellow;
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
    .tournament__title-text{
        margin-top: 1rem;
        /*color: #8184BC;*/
        color: white;
        font-size: 1rem;
        font-weight: bold;
    }
    .yellow-text{
        color: #fcc339;
        text-transform: uppercase;
    }
    .ai-fe{
        align-items: flex-end;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" style="width: 100%;">
    </div>


    <div class="article__text">
        <div class="tournament__title-text mt-2">
            <p>
                Наша друга мережева акція надає гравцям <span class="yellow-text">€70 000 ГОТІВКОЮ</span> у вигляді 1000 призів ЩОДНЯ.
                Крутіть випадкове колесо фортуни з 29 вересня по 9 жовтня включно та отримуйте <span class="yellow-text">БІЛЬШІ ВИГРАШІ</span> щодня.            </p>
            <p>
                Виберіть один із більш ніж 40 слотів із відміченого нагородами портфоліо Betsoft і приготуйтеся до розігріву. Призові спини можуть бути запущені в будь-який момент гри, а не переривати гру, щоб додати острых ощущений і озноба від гри, щоб грати.
            </p>
        </div>
        <div class="flex ai-fe">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Умови та положення:</div>
                <ul class="tournament__list">
                    <li><span class="yellow-text">Отримайте приз</span> Мережева акція проводиться щодня з 10:00 четверга, 29 вересня, до 10:00 9 жовтня за центральноєвропейським літнім часом.</li>
                    <li>Кожна щоденна акція діє цілодобово з 10:00 до 10:00 за центральноєвропейським літнім часом.</li>
                    <li>BETSOFT Gaming залишає за собою право змінити, призупинити або скасувати акцію у будь-який час.</li>
                    <li>Загальний призовий фонд: <span class="yellow-text">70 000 євро готівкою</span></li>
                    <li>Щоденний призовий фонд: <span class="yellow-text">€7000</span> готівкою зі 100 призів</li>
                    <li>1000 грошових призів за всю акцію</li>
                </ul>
            </div>
        </div>


        <div class="flex jc-center">
            <img src="/img/tournaments/take-the-prize/Thief.png" alt="Thief" class="tournament__image" style="width: 180px;">
        </div>
        <div class="flex ai-center">
            <div class="tournament__cond-text">
                <div class="tournament__cond-title">Як виграти:</div>
                <ul class="tournament__list">
                    <li>Станьте щасливим, лише з одним обертанням на реальні гроші у будь-якій з кваліфікаційних ігор.</li>
                    <li>Мінімальна ставка для кваліфікації: 0,50 євро.</li>
                </ul>
            </div>

        </div>

        <img src="/img/tournaments/take-the-prize/wheel_coins.png" alt="wheel coins" class="tournament__image" style="margin-left: -5rem; width: 500px;">


        <div class="flex ai-center mt-2">
            <div class="tournament__cond-text" style="width: 100%">
                <table class="tournament__table mt-2">
                    <thead>
                    <tr>
                        <th>Кількість призів</th>
                        <th>Сума призів на день</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr><td>1</td><td>€ 1,000.00</td></tr>
                    <tr><td>2</td><td>€ 500.00</td></tr>
                    <tr><td>4</td><td>€ 300.00</td></tr>
                    <tr><td>13</td><td>€ 100.00</td></tr>
                    <tr><td>30</td><td>€ 50.00</td></tr>
                    <tr><td>50</td><td>€ 20.00</td></tr>
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
