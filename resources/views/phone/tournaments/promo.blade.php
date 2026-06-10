@php
    /** @var \App\Models\Tournaments\TournamentPromo\TournamentPromo $tournament */
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
    .tournament-promo table {
        border-spacing: 0;
        width: 100%;
        border: 1px solid #3c3b89;
    }
    .tournament-promo table td {
        padding: 0.5rem 1rem;
        color: #8888af;
        border-bottom: 1px solid #3c3b89;

    }
    .tournament-promo table tr:last-child td {
        border-bottom: none;
    }
    .tournament-promo table th {
        color: #fff;
        text-align: left;
        background-color: #3c3b89;
        border-bottom: 2px solid #3c3b89;
        padding: 0.5rem 1rem;
    }
    body.light .tournament-promo table th {
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
    .tourn_stats{
        display: block;
        justify-content: space-between;
        margin-top: 20px;
    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 100%;
        padding: 20px 0;
        border-radius: 10px;
        margin-bottom: 10px;
    }
    .tourn_stat_itm h5{
        font-size: 12px;
        font-weight: 400;
        margin-bottom: 15px;
    }
    .tourn_stat_itm h4{
        color: #FFC660;
        font-size: 16px
    }
    .tabs_tour{
        display: flex;
        background: #181833;
        margin-top: 30px;
        padding: 5px;
        justify-content: space-between;
    }
    .tabs_tour button{
        width: 32%;
        background: transparent;
        border: none;
        padding: 7px;
        border-radius: 5px;
        color: #8C90CA;
        cursor: pointer;
    }
    .tabs_tour button.active{
        background: #2B2F5B;
        color: #fff;
    }
    .tabs_tour button:hover{
        background: #2B2F5B;
        color: #fff;
    }
    .panes_tour{
        margin-top: 30px;
    }
    .panes_tour_itm{
        display: none;
    }
    .panes_tour_itm.active{
        display: block;
    }
</style>
<div class="width-limiter tournament-promo">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">
        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.duration') }}</h5>
            <h4>{{ $tournament->getStart()->isoFormat("DD.MM.YYYY") }} - {{ $tournament->getEnd()->isoFormat("DD.MM.YYYY") }}</h4>
        </div>

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.prize_fund') }}</h5>
            <h4>{{ $tournament->getPrizeFund() }}</h4>
        </div>

        @if(!empty($tournament->getMinBet()))
            <div class="tourn_stat_itm">
                <h5>{{ __('tournament.minimum_bid') }}</h5>
                <h4>{{ $tournament->getMinBet() }}</h4>
            </div>
        @endif

        @if($tournament->getMinSpins() > 0)
            <div class="tourn_stat_itm">
                <h5>{{ __('tournament.min_number_of_spins') }}</h5>
                <h4>{{ $tournament->getMinSpins() }}</h4>
            </div>
        @endif
    </div>

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">Games</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">Terms and Conditions</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">Prizes</button>
    </div>
    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
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
        </div>
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                {!! $tournament->renderConditions() !!}
            </div>
        </div>
        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            {!! $tournament->renderPrizes() !!}
        </div>
    </div>

    <script>
        for (let btn of document.querySelectorAll('.tournament-promo .tabs_tourbtn')) {
            btn.addEventListener('click', (e) => {
                let t = e.target;
                //clear active
                for (let el of document.querySelectorAll('.tournament-promo .panes_tour_itm')) el.classList.remove('active');
                for (let el of document.querySelectorAll('.tournament-promo .tabs_tourbtn')) el.classList.remove('active');
                //set current active els
                t.classList.add('active');
                document.querySelector('.tournament-promo '+ "#pn_" + t.dataset.type).classList.add('active');
            });
        }
    </script>
{{--    @include('desktop.footer')--}}
    @include('phone.blocks.bottom-deposit-btn')

</div>
