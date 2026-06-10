@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class = "article__text">
        <div class = "tournament__cond-title"> Період проведення акції:</div>
        <div class = "tournament__cond-text"> C 11 лютого 2021 по 17 листопада 2021.</div>

        <div class = "tournament__cond-title"> Призовий фонд:</div>
        <div class = "tournament__cond-text">
            <ul>
                <Li> Грошові призи загальною сумою € / $ 4,000,000 (€ / $ 500,000 щомісяця)</li>
                <Li> 16 X щотижневих турнірів з призовим фондом € / $ 31,000</li>
                <Li> 24 X щотижневих турнірів з призовим фондом € / $ 62,000</li>
                <Li> 112 X щоденних акцій Prize Drops з призовим фондом € / $ 4,500</li>
                <Li> 168 X щоденних акцій Prize Drops з призовим фондом € / $ 9,000</li>
            </ul>
        </div>

        <div class = "tournament__cond-title">Механіка:</div>
        <div class = "tournament__cond-text">
            Період проведення кожного турніру - сім (7) днів, з четверга по середу, паралельно зі щоденною акцією Prize Drops.
            Тривалість акції зі звичайною механікою і механікою Множник Призу обмежена 4-тижневим періодом.
        </div>


        <div class = "tournament__cond-title">Як виграти:</div>
        <div class = "tournament__cond-text">
            <ul>
                <Li> Необхідно грати в будь-які з беруть участь ігор на реальні гроші.</li>
                <Li> Стандартна механіка: Мінімальна ставка € / $ 0,50. </li>
                <Li> Множник Призу: без мінімальної ставки. Для участі не потрібно додаткових вкладень.</li>
            </ul>
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
