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
<div id="cabinet-position" data-position="tournaments"></div>
<div class="cabinet__tournaments">

    @php
        $tournaments = \App\Models\Tournaments\Custom\CustomTournament::getAvailableTournaments();
    @endphp
    @foreach($tournaments as $tournament)
        @php
          $active_tournament = true;
        @endphp
        <div class="cabinet-trn">
            <div class="cabinet-trn__img-wrap">
                <img src="{{$tournament->cabinetBannerUrl()}}" class="cabinet-trn__img"/>
            </div>

            <div class="cabinet-trn__info">
                <div class="cabinet-trn__head">
                    <div class="cabinet-trn__name">{{$tournament->getName()}}</div>
                    <div class="cabinet__pad-status_success">Active</div>
                </div>

                <div class="cabinet-trn__fund-label">{{__('tournament.prize_fund')}}:</div>
                <div class="cabinet-trn__fund-value">
                    <img src="/img/ui/icons/cup.svg" class="cabinet-trn__fund-icon">
                    {{$tournament->getPrizeFund()}}
                </div>

            </div>

            @if($tournament->isActive())
                <div class="cabinet-trn__dates">
                    <div class="cabinet-trn__expire-value time-estimated" data-target_time="{{$tournament->getEndDateTime()}}" data-nosymbols data-separator=" | ">-</div>
                </div>

                <div class="cabinet-trn__ptcp-btn tnt-info-button" data-url="{{ $tournament->infoUrl() }}">{{__('tournament.participate')}}</div>
            @else
                <div class="cabinet-trn__dates">
                    <div class="cabinet-trn__expire-value time-estimated" data-target_time="{{$tournament->getStartDateTime()}}" data-nosymbols data-separator=" | ">-</div>
                </div>

                <div class="cabinet-trn__ptcp-btn tnt-info-button" data-url="{{ $tournament->infoUrl() }}">{{__('tournament.details')}}</div>
            @endif
        </div>
    @endforeach

    @if(empty($active_tournaments) && !$active_tournament)
            <div class="cabinet__bonuses-empty-msg">
                <img src="/img/ui/icons/cup.svg" class="cabinet__bonuses-empty-msg-img ml-1">
                {{__('tournament.no_active')}}
            </div>
    @else
    <div class="cabinet__bonuses-list">

        @if(!empty($active_tournaments))
            <div class="cabinet__bonuses-list-title">{{__('tournament.active_tournaments')}}:</div>
        @endif

        @foreach($active_tournaments as $tournament)
            <div class="cabinet-trn">
                <div class="cabinet-trn__img-wrap">
                    <img src="{{$tournament->getImgUrl()}}" class="cabinet-trn__img"/>
                </div>

                <div class="cabinet-trn__info">
                    <div class="cabinet-trn__head">
                        <div class="cabinet-trn__name">{{$tournament->getName()}}</div>
                        <div class="cabinet__pad-status_success">{{$tournament->getStatusName()}}</div>
                    </div>

                    <div class="cabinet-trn__fund-label">{{__('tournament.prize_fund')}}:</div>
                    <div class="cabinet-trn__fund-value">
                        <img src="/img/ui/icons/cup.svg" class="cabinet-trn__fund-icon">
                        {{$tournament->getPrizeFund()}}
                    </div>

                </div>

                @if($tournament->isActive())
                    <div class="cabinet-trn__dates">
                        <div class="cabinet-trn__expire-value time-estimated" data-target_time="{{$tournament->getEndDateTime()}}" data-nosymbols data-separator=" | ">-</div>
                    </div>

                    <div class="cabinet-trn__ptcp-btn tnt-info-button" data-url="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}">{{__('tournament.participate')}}</div>
                @else
                    <div class="cabinet-trn__dates">
                        <div class="cabinet-trn__expire-value time-estimated" data-target_time="{{$tournament->getStartDateTime()}}" data-nosymbols data-separator=" | ">-</div>
                    </div>

                    <div class="cabinet-trn__ptcp-btn tnt-info-button" data-url="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}">{{__('tournament.details')}}</div>
                @endif
            </div>
        @endforeach

    </div>
    @endif

    <div class="cabinet__bonuses-list">

        @if (!empty($completed_tournaments))
            <div class="cabinet__bonuses-list-title">{{__('tournament.completed_tournaments')}}:</div>
        @endif

        @foreach($completed_tournaments as $tournament)
            <div class="cabinet-trn">
                <div class="cabinet-trn__img-wrap">
                    <img src="{{$tournament->getImgUrl()}}" class="cabinet-trn__img"/>
                </div>

                <div class="cabinet-trn__info">
                    <div class="cabinet-trn__head">
                        <div class="cabinet-trn__name">{{$tournament->getName()}}</div>
                        <div class="cabinet__pad-status_warn">{{$tournament->getStatusName()}}</div>
                    </div>

                    <div class="cabinet-trn__fund-label">{{__('tournament.prize_fund')}}:</div>
                    <div class="cabinet-trn__fund-value_past">
                        <img src="/img/ui/icons/cup.svg" class="cabinet-trn__fund-icon">
                        {{$tournament->getPrizeFund()}}
                    </div>

                </div>

                <div class="cabinet-trn__dates">
                    <div class="cabinet-trn__expire-value_past time-estimated" data-target_time="{{$tournament->getStartDateTime()}}" data-nosymbols data-separator=" | ">-</div>
                </div>

                <div class="cabinet-trn__ptcp-btn_past tnt-info-button" data-url="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}">{{__('tournament.details')}}</div>
            </div>
        @endforeach

    </div>

</div>
