@php
    use App\MUser;
    use App\Services\Tournaments\TournamentsService;
     $service = new TournamentsService(MUser::getAuth());
    /** @var App\Models\Tournaments\TournamentInterface[] $active_tournaments */
     $active_tournaments = $service->getAvailableTournaments();
    /** @var App\Models\Tournaments\TournamentInterface[] $completed_tournaments */
    $completed_tournaments = $service->getCompletedTournaments();
    $active_tournament = false;
@endphp

<div class="cabinet__tournaments">

    <div id="cabinet-position" data-position="tournaments"></div>

    @php
        $tournaments = \App\Models\Tournaments\Custom\CustomTournament::getAvailableTournaments();
    @endphp
    @foreach($tournaments as $tournament)
        @php
            $active_tournament = true;
        @endphp
        <div class="cabinet__bonuses-trn">
            <div class="cabinet__bonuses-trn-img-wrap">
                <img src="{{$tournament->cabinetBannerUrl()}}" class="cabinet__bonuses-trn-img"/>
            </div>

            <div class="cabinet__bonuses-trn-info">
                <div class="cabinet__pad-status_success">Active</div>
                <div class="cabinet__bonuses-trn-name">{{$tournament->getName()}}</div>
                <div class="cabinet__bonuses-trn-fund-label">{{__('tournament.prize_fund')}}:</div>
                <div class="cabinet__bonuses-trn-fund-value">
                    <img src="/img/ui/icons/cup.svg" class="cabinet__bonuses-trn-fund-icon">
                    {{$tournament->getPrizeFund()}}
                </div>

            </div>

            @if($tournament->isActive())
                <div class="cabinet__bonuses-trn-dates">
                    <div class="cabinet__bonuses-trn-expire-label">{{__('tournament.time_left')}}</div>
                    <div class="cabinet__bonuses-trn-expire-value time-estimated"
                         data-target_time="{{$tournament->getEndDateTime()}}" data-nosymbols data-separator=" | ">-
                    </div>
                    <div class="cabinet__bonuses-trn-ptcp-btn tnt-info-button w-10"
                         data-url="{{ $tournament->infoUrl() }}">{{__('tournament.participate')}}</div>
                </div>
            @else
                <div class="cabinet__bonuses-trn-dates">
                    <div class="cabinet__bonuses-trn-expire-label">{{__('tournament.to_tournament_start')}}</div>
                    <div class="cabinet__bonuses-trn-expire-value time-estimated"
                         data-target_time="{{$tournament->getStartDateTime()}}" data-nosymbols data-separator=" | ">-
                    </div>
                    <div class="cabinet__bonuses-trn-ptcp-btn tnt-info-button w-10"
                         data-url="{{ $tournament->infoUrl() }}">{{__('tournament.details')}}</div>
                </div>
            @endif
        </div>
    @endforeach

    @if(empty($active_tournaments))
        @if (empty($tournaments))
            <div class="cabinet__bonuses-empty-msg">
                <img src="/img/ui/icons/cup.svg" class="cabinet__bonuses-empty-msg-img">
                {{__('tournament.no_active')}}
            </div>
        @endif
    @else
        <div class="cabinet__bonuses-list">

            <div class="cabinet__bonuses-list-title">{{__('tournament.active_tournaments')}}:</div>
            @foreach($active_tournaments as $tournament)
                <div class="cabinet__bonuses-trn"
                     @if($tournament->isTestMode()) style="border: rgba(255,214,43,0.73) 2px solid" @endif>
                    <div class="cabinet__bonuses-trn-img-wrap">
                        <img src="{{$tournament->getImgUrl()}}" class="cabinet__bonuses-trn-img"/>
                    </div>

                    <div class="cabinet__bonuses-trn-info">
                        <div class="cabinet__pad-status_success">Active</div>
                        <div class="cabinet__bonuses-trn-name">{{$tournament->getName()}}</div>
                        <div class="cabinet__bonuses-trn-fund-label">{{__('tournament.prize_fund')}}:</div>
                        <div class="cabinet__bonuses-trn-fund-value">
                            <img src="/img/ui/icons/cup.svg" class="cabinet__bonuses-trn-fund-icon">
                            {{$tournament->getPrizeFund()}}
                        </div>

                    </div>

                    @if($tournament->isActive())
                        <div class="cabinet__bonuses-trn-dates">
                            <div class="cabinet__bonuses-trn-expire-label">{{__('tournament.time_left')}}</div>
                            <div class="cabinet__bonuses-trn-expire-value time-estimated"
                                 data-target_time="{{$tournament->getEndDateTime()}}" data-nosymbols
                                 data-separator=" | ">-
                            </div>
                            <div class="cabinet__bonuses-trn-ptcp-btn tnt-info-button w-10"
                                 data-url="{{ $tournament->infoUrl() }}">{{__('tournament.participate')}}</div>
                        </div>
                    @else
                        <div class="cabinet__bonuses-trn-dates">
                            <div
                                class="cabinet__bonuses-trn-expire-label">{{__('tournament.to_tournament_start')}}</div>
                            <div class="cabinet__bonuses-trn-expire-value time-estimated"
                                 data-target_time="{{$tournament->getStartDateTime()}}" data-nosymbols
                                 data-separator=" | ">-
                            </div>
                            <div class="cabinet__bonuses-trn-ptcp-btn tnt-info-button w-10"
                                 data-url="{{  $tournament->infoUrl() }}">{{__('tournament.details')}}</div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(!empty($completed_tournaments))
        <div class="cabinet__bonuses-list">

            <div class="cabinet__bonuses-list-title">{{__('tournament.completed_tournaments')}}:</div>

            @foreach($completed_tournaments as $tournament)
                <div class="cabinet__bonuses-trn">
                    <div class="cabinet__bonuses-trn-img-wrap">
                        <img src="{{$tournament->getImgUrl()}}"
                             class="cabinet__bonuses-trn-img cabinet__bonuses-trn-img_past"/>
                    </div>

                    <div class="cabinet__bonuses-trn-info">
                        <div class="cabinet__pad-status_warn">{{$tournament->getStatusName()}}</div>
                        <div class="cabinet__bonuses-trn-name">{{$tournament->getName()}}</div>
                        <div class="cabinet__bonuses-trn-fund-label">{{__('tournament.prize_fund')}}:</div>
                        <div class="cabinet__bonuses-trn-fund-value_past">
                            <img src="/img/ui/icons/cup.svg" class="cabinet__bonuses-trn-fund-icon_past">
                            {{$tournament->getPrizeFund()}}
                        </div>

                    </div>

                    <div class="cabinet__bonuses-trn-dates">
                        <div class="cabinet__bonuses-trn-ptcp-btn_past tnt-info-button w-10"
                             data-url="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}">{{__('tournament.details')}}</div>
                    </div>
                </div>
            @endforeach

        </div>
    @endif

</div>
