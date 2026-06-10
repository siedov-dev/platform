@php
    /** @var \App\Models\Tournaments\GoTournament $tournament */
@endphp
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

        @if(\Illuminate\Support\Facades\Auth::check())
            @if($tournament->isActive() && $tournament->agreementRequired())
                @if(!$tournament->userParticipates(\Illuminate\Support\Facades\Auth::id()))
                    <div class="flex jc-center w-100">
                        <form method="post" action="{{action("TournamentController@participate", ['tournament' => $tournament->getId()])}}">
                            @csrf
                            <input type="hidden" name="tournament_id" value="{{$tournament->getId()}}">
                            <input type="submit" class="button button_green" value="{{__('tournament.participate2')}}"></input>
                        </form>
                    </div>
                @endif
            @endif
        @endif
    </div>

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
                            @include('phone.games.single-game', ['game' => $game])
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        <div id="pn_terms" class="panes_tour_itm pane_terms">
            <div class="article__text">
                {!! $tournament->getConditions(app()->getLocale()) !!}
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
            <div class="tournament__leaders">
                <div class="tournament__leaders-title">{{__("tournament.table.title")}}:</div>

                <table class="rating-table">
                    <thead>
                    <th>#</th>
                    <th>{{__("tournament.table.player")}}</th>
                    <th>{{__("tournament.table.points")}}</th>
                    <th>{{__("tournament.table.prize")}}</th>
                    </thead>
                    <tbody>
                    @php
                        $table['user_position'] = $user_place;
                        $page = request('page', 1);
                        $table_size = count($prizes);
                        if ($table_size < 10) $table_size = 10;
                        $curr_symb = \App\UserWallet::currencySymbol($tournament->getCurrency());
                        $currency = mb_strtolower( \App\UserWallet::currencyName($tournament->getCurrency()));
                    @endphp

                    @for($i = 1; $i <= $table_size; $i++)
                        @php
                            if ($i == ($table_size) && $table['user_position'] > $table_size) $i = $table['user_position'];
                            $t_index = $i + (($page-1) * $table_size);
                        @endphp
                        <tr class="@if($t_index === $table['user_position']) players_slot @endif" data-index="{{ $i }}">
                            <td data-index="{{ $i }}"></td>
                            <td>
                                <span class="text-overflow">
                                    {{(isset($table[$t_index]) && $table[$t_index]['points'] > 0)? \App\MUser::hideLogin($table[$t_index]['login']?? '-') : '-'}}
                                </span>
                            </td>
                            <td>
                                {{(($table[$t_index]['points']?? 0) > 0)? ($table[$t_index]['points']) : '-'}}
                            </td>
                            <td>
                                <div class="flex ai-center">
                                    @if(isset($prizes[$t_index]['money_amount']) && $prizes[$t_index]['money_amount'] > 0)
                                        <div class="currency-icon currency-icon_{{$currency}} custom-table__cell-icon"></div>&nbsp;
                                        @if(isset($prizes[$t_index]['money_amount']))
                                            {{number_format($prizes[$t_index]['money_amount'], 2, ".", " ")}}
                                        @endif
                                    @endif
                                </div>
                                <div class="flex ai-center">
                                    @if(isset($prizes[$t_index]['item']))
                                        @php
                                            $item = new \App\Models\Tournaments\TournamentSlcItem($prizes[$t_index]['item']);
                                        @endphp
                                        <div class="prize-item">
                                            <div class="scale-wrapper scale-04">
                                                {!! $item->getInfo()->renderItemIcon() !!}
                                            </div>
                                        </div>
                                        <div>{!! $item->getInfo()->name() !!}</div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endfor
                    </tbody>
                </table>
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

    @include('phone.blocks.bottom-deposit-btn')

</div>
