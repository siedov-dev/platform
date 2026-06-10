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
    }
    .tournament__table td {
        padding: 0.5rem 1rem;
        color: #8888af;
        border-bottom: 1px solid #ffffff1a;

    }
    .tournament__table th {
        color: #fff;
        text-align: left;
        border-bottom: 2px solid #fff;
        padding: 0.5rem 1rem;
    }
    body.light .tournament__table th {
        color: #333;
        border-color: #333;
    }
</style>
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__cond-title">Період турніру:</div>
        <div class="tournament__cond-text">З 10 по 17 лютого 2022 року (включено обидва дні)</div>

    <div class="tournament__cond-title">Положення та умови «Фруктова вечірка від GameArt»</div>
            <ul class="tournament__list">
                <div class="tournament__cond-text">Гравець може брати участь в акції в період дії наступним чином:</div>
                <li>Зіграйте у кваліфікаційну гру та слідкуйте за своїм рахунком у внутрішньоігровій таблиці лідерів.</li>
                <li>За кожну ставку в 1 євро, зроблену реальними грошима в кваліфікаційній грі, гравець отримує 1 очко.</li>
                <div class="tournament__cond-title">Додаткові здобутки:</div>
                <li>ВЕЛИКИЙ ВИГРАШ – 50 балів</li>
                <li>300 символів Scatter у будь-якій грі – 300 балів</li>
                <li>ЕПІЧНА ПЕРЕМОГА – 500 балів</li>
                <li>5000 обертань - 1000 балів (вартість ставки 0,40 євро або вище)</li>
                <li>Таблиця лідерів відображатиметься у самій грі.</li>
            </ul>

        <div class="tournament__cond-title">Механика:</div>
        <div class="tournament__cond-text">
            <ul class="tournament__list">
                <li>Турнір Fruit Party GameArt - це глобальний турнір, доступний для всіх гравців.</li>
                <li>Турнір розпочнеться о 00:01 за Грінвічем у четвер, 10 лютого, і закінчиться о 23:59 за Грінвічем.
                     у четвер, 17 лютого 2022 року.</li>
                <li>Гравці можуть здобути такі призи: 10000 € буде розподілено між 150 гравцями</li>
                <li>Компанія залишає за собою право змінювати, скасовувати чи припиняти акцію у будь-який час.</li>
            </ul>


            <table class="tournament__table mt-2">
                <thead>
                <tr>
                    <th>Гравець</th>
                    <th>Приз</th>
                </tr>
                </thead>
                <tbody>
                <tr><td>1</td><td>2,500 €</td></tr>
                <tr><td>2</td><td>1,000 €</td></tr>
                <tr><td>3</td><td>600 €</td></tr>
                <tr><td>4</td><td>300 €</td></tr>
                <tr><td>5 -14</td><td>100 €</td></tr>
                <tr><td>15-24</td><td>80 €</td></tr>
                <tr><td>25-50</td><td>50 €</td></tr>
                <tr><td>51-150</td><td>25 €</td></tr>
                </tbody>
            </table>
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

    @include('phone.footer')

</div>
