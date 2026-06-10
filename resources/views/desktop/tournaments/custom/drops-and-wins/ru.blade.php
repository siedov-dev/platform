@php
    /** @var \App\Models\Tournaments\Custom\CustomTournament $tournament */
@endphp
<div class="width-limiter">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image">
    </div>


    <div class="article__text">
        <div class="tournament__cond-title">Период проведения акции:</div>
        <div class="tournament__cond-text">C 11 февраля 2021 по 17 ноября 2021.</div>

        <div class="tournament__cond-title">Призовой фонд:</div>
        <div class="tournament__cond-text">
            <ul>
                <li>Денежные призы общей суммой €/$ 4,000,000 (€/$ 500,000 ежемесячно)</li>
                <li>16 X еженедельных турниров с призовым фондом €/$ 31,000</li>
                <li>24 X еженедельных турниров с призовым фондом €/$ 62,000</li>
                <li>112 X ежедневных акций Prize Drops с призовым фондом €/$ 4,500</li>
                <li>168 X ежедневных акций Prize Drops с призовым фондом €/$ 9,000</li>
            </ul>
        </div>

        <div class="tournament__cond-title">Механика:</div>
        <div class="tournament__cond-text">
            Период проведения каждого турнира - семь (7) дней, с четверга по среду, параллельно с ежедневной акцией Prize Drops.
            Продолжительность акции с обычной механикой и механикой Множитель Приза ограничена 4-недельным периодом.
        </div>


        <div class="tournament__cond-title">Как выиграть:</div>
        <div class="tournament__cond-text">
            <ul>
                <li>Необходимо играть в любые из участвующих игр на реальные деньги.</li>
                <li>Стандартная механика: Минимальная ставка €/$0,50.</li>
                <li>Множитель Приза: без минимальной ставки. Для участия не требуется дополнительных вложений.</li>
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
