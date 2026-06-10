@php
    /** @var App\Models\Tournaments\GoTournament $tournament */
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
        display: flex;
        justify-content: space-between;
        margin-top: 20px;

    }
    .tourn_stat_itm{
        background: #181833;
        color: #8C90CA;
        text-align: center;
        width: 24%;
        padding: 20px 0;
        border-radius: 10px;
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

    @media (max-width: 500px) {
        .tournament-promo table{
            font-size: .75rem;
        }
        .tournament-promo table td{
            padding: .25rem;
        }
    }
</style>
<div class="width-limiter tournament-promo">

    <div class="tournament__banner">
        <img src="{{ $tournament->getBannerUrl() }}" class="tournament__banner-image" style="height: auto;">
    </div>

    <div class="tourn_stats">

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.duration') }}</h5>
            <h4>{{ $tournament->getStartTime() }} - {{ $tournament->getEndTime() }}</h4>
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

        <div class="tourn_stat_itm">
            <h5>{{ __('tournament.min_number_of_spins') }}</h5>
            <h4>
                @if($tournament->getSpinsRequired() > 0)
                    {{ $tournament->getSpinsRequired() }}
                @else
                    -
                @endif
            </h4>
        </div>

    </div>

    @if(\Illuminate\Support\Facades\Auth::check())
        @if($tournament->isActive() && $tournament->agreementRequired())
            @if(!$tournament->userParticipates(\Illuminate\Support\Facades\Auth::id()))
                <div class="flex jc-center w-100 mt-2">
                    <form method="post" action="{{action("TournamentController@participate", ['tournament' => $tournament->getId()])}}">
                        @csrf
                        <input type="hidden" name="tournament_id" value="{{$tournament->getId()}}">
                        <input type="submit" class="button button_green" value="{{__('tournament.participate2')}}"></input>
                    </form>
                </div>
            @endif
        @endif
    @endif

    <div class="tabs_tour">
        <button id="pt_games" class="tabs_tourbtn active" data-type="games">{{ __('tournament.games') }}</button>
        <button id="pt_terms" class="tabs_tourbtn" data-type="terms">{{ __('tournament.terms_conditions') }}</button>
        <button id="pt_prize_page" class="tabs_tourbtn" data-type="prize_page">{{ __('tournament.tab_pize_page') }}</button>
        <button id="pt_prizes" class="tabs_tourbtn" data-type="prizes">{{ __('tournament.table.title') }}</button>
    </div>

    <div class="panes_tour">
        <div id="pn_games" class="panes_tour_itm pane_games active">
            <div class="tournament__games">
                <div class="tournament__games-head">
                    <div class="tournament__games-title">{{__('tournament.tournament_games')}}:</div>
                    <a class="tournament__games-button" href="{{route('games', ['category' => 'tournament', 't_id' => $tournament->getId()])}}">{{__('tournament.all_games')}} ({{$tournament->getAllowedGames()->count()}})</a>
                </div>
                <div class="tournament__games-list">
                    @if(\Illuminate\Support\Facades\Auth::check() && $tournament->isActive() && $tournament->agreementRequired() && !$tournament->userParticipates(\Illuminate\Support\Facades\Auth::id()))
                        @foreach($tournament->getAllowedGames() as $game)
                            <div class="game slider__item @if($game->isTestMode()) test-mode @endif">
                                @if($game->isNew())
                                    <div class="game__badge">New</div>
                                @elseif($game->isInTop())
                                    <div class="game__badge">Top</div>
                                @endif
                                <div class="game__veil">
                                    <div class="game__name">{{ $game->getName() }}</div>
                                        <div class="game__play-real" data-url="{{ $game->getRealPlayUrl() }}" onclick="shortMessage.show('{{__('tournament.accept_required')}}')"><div class="triangle"></div></div>
                                </div>
                                <img loading="lazy" src="{{ $game->getImageUrl() }}" class="game__image">
                            </div>
                        @endforeach
                    @else
                        @foreach($tournament->getAllowedGames() as $game)
                            @include('desktop.games.single-game', ['game' => $game])
                        @endforeach
                    @endif
                </div>
            </div>
        </div>

        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                <div class="article__text">
                    {!! $tournament->getConditions(app()->getLocale()) !!}
                </div>
            </div>
        </div>

        @if(!empty($tournament->getPrizePage()))
            <div id="pn_prize_page" class="panes_tour_itm pane_terms">
                <div class="article__text">
                    <div class="article__text">
                        {!! $tournament->getPrizePage() !!}
                    </div>
                </div>
            </div>
        @endif

        <div id="pn_prizes" class="panes_tour_itm pane_prizes">
            @php
                $page = request('page', 1);
                $table_size = count($prizes);
                if ($table_size < 10) $table_size = 10;
                $left_table_size = intval($table_size / 2);
                $curr_symb = \App\UserWallet::currencySymbol($tournament->getCurrency());
                $currency = mb_strtolower( \App\UserWallet::currencyName($tournament->getCurrency()));
                $table['user_position'] = $user_place;
            @endphp


            <div class="tournament__leaders">
                <div class="tournament__leaders-title">{{__("tournament.table.title")}}:</div>

                <div class="tournaments__leaders-list">

                    <div class="tournament__leaders-table">
                        <div class="tournament__leaders-table-head">
                            <div class="tournament__leaders-table-th w-3">#</div>
                            <div class="tournament__leaders-table-th w-10">{{__("tournament.table.player")}}</div>
                            <div class="tournament__leaders-table-th w-5">{{__("tournament.table.points")}}</div>
                            <div class="tournament__leaders-table-th grow">{{__("tournament.table.prize")}}</div>
                        </div>
                        @for($i = 1; $i <= $left_table_size; $i++)
                            @php
                                $t_index = $i + (($page-1) * $table_size);
                            @endphp
                            <div class="tournament__leaders-table-row @if($t_index === $table['user_position']) players_slot @endif" data-index="{{ $i }}">
                                <div class="tournament__leaders-table-td tournament__table-pos w-3" data-index="{{ $i }}"></div>
                                <div class="tournament__leaders-table-td w-10">
                                    <span class="text-overflow">
                                        {{(isset($table[$t_index]) && $table[$t_index]['points'] > 0)? \App\MUser::hideLogin($table[$t_index]['login']?? '-') : '-'}}
                                    </span>
                                </div>
                                <div class="tournament__leaders-table-td w-5">{{(($table[$t_index]['points']?? 0) > 0)? ($table[$t_index]['points']) : '-'}}</div>
                                <div class="tournament__leaders-table-td" style="flex-shrink: 10;">
                                    @if(isset($prizes[$t_index]['money_amount']) && $prizes[$t_index]['money_amount'] > 0)
                                        <div class="flex text-nowrap ai-center">
                                            <div class="currency-icon currency-icon_{{$currency}} custom-table__cell-icon"></div>&nbsp;
                                            @if(isset($prizes[$t_index]['money_amount']))
                                                {{number_format($prizes[$t_index]['money_amount'], 2, ".", " ")}}
                                            @endif
                                        </div>
                                    @endif
                                    @if(isset($prizes[$t_index]['item']))
                                        @php
                                            $item = new \App\Models\Tournaments\TournamentSlcItem($prizes[$t_index]['item']);
                                        @endphp
                                        <div style="width: 3rem;">
                                            <div class="scale-wrapper scale-04">
                                                {!! $item->getInfo()->renderItemIcon() !!}
                                            </div>
                                        </div>
                                        <div>{!! $item->getInfo()->name() !!}</div>
                                    @endif

                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="tournament__leaders-table">
                        <div class="tournament__leaders-table-head">
                            <div class="tournament__leaders-table-th w-3">#</div>
                            <div class="tournament__leaders-table-th w-10">{{__("tournament.table.player")}}</div>
                            <div class="tournament__leaders-table-th w-5">{{__("tournament.table.points")}}</div>
                            <div class="tournament__leaders-table-th grow">{{__("tournament.table.prize")}}</div>
                        </div>
                        @for($i = $left_table_size + 1; $i <= $left_table_size * 2; $i++)
                            @php
                                if ($i == ($left_table_size * 2) && $table['user_position'] > ($left_table_size * 2)) $i = $table['user_position'];
                                $t_index = $i + (($page - 1 ) * $table_size);
                            @endphp
                            <div class="tournament__leaders-table-row @if($t_index === $table['user_position']) players_slot @endif" data-index="{{ $i }}">
                                <div class="tournament__leaders-table-td tournament__table-pos w-3" data-index="{{ $i }}"></div>
                                <div class="tournament__leaders-table-td w-10">
                                    <span class="text-overflow">
                                        {{(isset($table[$t_index]) && $table[$t_index]['points'] > 0)? \App\MUser::hideLogin($table[$t_index]['login']?? '-') : '-'}}
                                    </span>
                                </div>
                                <div class="tournament__leaders-table-td w-5">{{(($table[$t_index]['points']?? 0) > 0)? ($table[$t_index]['points']) : '-'}}</div>
                                <div class="tournament__leaders-table-td" style="flex-shrink: 10;">
                                    @if(isset($prizes[$t_index]['money_amount']) && $prizes[$t_index]['money_amount'] > 0)
                                        <div class="flex text-nowrap ai-center">
                                            <div class="currency-icon currency-icon_{{$currency}} custom-table__cell-icon"></div>&nbsp;
                                            @if(isset($prizes[$t_index]['money_amount']))
                                                {{number_format($prizes[$t_index]['money_amount'], 2, ".", " ")}}
                                            @endif
                                        </div>
                                    @endif
                                    @if(isset($prizes[$t_index]['item']))
                                        @php
                                            $item = new \App\Models\Tournaments\TournamentSlcItem($prizes[$t_index]['item']);
                                        @endphp
                                        <div style="width: 3rem;">
                                            <div class="scale-wrapper scale-04">
                                                {!! $item->getInfo()->renderItemIcon() !!}
                                            </div>
                                        </div>
                                        <div>{!! $item->getInfo()->name() !!}</div>
                                    @endif
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
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

</div>
