@php
    use \App\Models\Tournaments\TournamentInterface;
    /** @var TournamentInterface[] $active_tournaments */
    /** @var TournamentInterface[] $completed_tournaments */
@endphp
<div class="width-limiter">
    <div class="bonuses__banner-wrap">
        <img src="/img/tournaments/banner_desktop_{{ app()->getLocale() }}.png" class="bonuses__banner">
    </div>

    <div class="tabs mt-2">
        <div class="tabs__tab tabs__tab_active" data-target="#tournaments__list-active">{{__('tournament.active_tournaments')}}</div>
        <div class="tabs__tab" data-target="#tournaments__list-past">{{__('tournament.completed_tournaments')}}</div>
    </div>

    <div id="tournaments__list-active" class="bonuses__list mt-2">

        <div class="bonuses__items">

            @foreach($active_tournaments as $tournament)
                <div class="bonuses__item" @if($tournament->isTestMode()) style="border: rgba(255,214,43,0.73) 2px solid" @endif>
                    <a href="{{ $tournament->infoUrl() }}" class="content-link">
                        <img src="{{$tournament->getImgUrl()}}" class="bonuses__item-image">
                    </a>

                    <div class="bonuses__item-info ai-center">
                        <div class="bonuses__item-title">{{$tournament->getName()}}</div>
                        <div class="bonuses__item-desc">{{__('tournament.prize_fund')}}:</div>
                        <div class="bonuses__item-fund">{{$tournament->getPrizeFund()}}</div>
                        <a href="{{ $tournament->infoUrl() }}" class="content-link bonuses__item-button tournaments__info-button w-100">{{__('tournament.details')}}</a>
                        @if($tournament->isActive())
                            <div class="bonuses__item-start-label">{{__('tournament.to_end')}}: <span class="bonuses__item-start-value time-estimated" data-timeout-event="true" data-target_time="{{$tournament->getEndDateTime()}}">-</span></div>
                            <div class="bonuses__item-progress-track">
                                <div class="bonuses__item-progress-bar time-progress-bar" data-min="{{strtotime($tournament->getStartDateTime())}}" data-max="{{strtotime($tournament->getEndDateTime())}}" style="width: 0%"></div>
                            </div>
                        @else
                            <div class="bonuses__item-start-label">{{__('tournament.to_start')}}: <span class="bonuses__item-start-value time-estimated" data-target_time="{{$tournament->getStartDateTime()}}">-</span></div>
                            <div class="bonuses__item-progress-track">
                                <div class="bonuses__item-progress-bar" style="width: 0%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            @php
                $tournaments = \App\Models\Tournaments\Custom\CustomTournament::getAvailableTournaments();
            @endphp
            @foreach($tournaments as $tournament)
                <div class="bonuses__item flex column @if($tournament->isTestMode()) test-item @endif">
                    <a href="{{ $tournament->infoUrl() }}" class="content-link">
                        <img src="{{$tournament->getImgUrl()}}" class="bonuses__item-image">
                    </a>

                    <div class="bonuses__item-info ai-center grow">
                        <div class="bonuses__item-title" style="text-align: center;">{{$tournament->getName()}}</div>
                        <div class="bonuses__item-desc mt-auto" style="margin-top: auto">{{__('tournament.prize_fund')}}:</div>
                        <div class="bonuses__item-fund">{{$tournament->getPrizeFund()}}</div>
                        <a href="{{ $tournament->infoUrl() }}" class="content-link bonuses__item-button tournaments__info-button w-100">{{__('tournament.details')}}</a>
                        @if($tournament->isActive())
                            <div class="bonuses__item-start-label">{{__('tournament.to_end')}}: <span class="bonuses__item-start-value time-estimated" data-target_time="{{$tournament->getEndDateTime()}}">-</span></div>
                            <div class="bonuses__item-progress-track">
                                <div class="bonuses__item-progress-bar time-progress-bar" data-min="{{strtotime($tournament->getStartDateTime())}}" data-max="{{strtotime($tournament->getEndDateTime())}}" style="width: 0%"></div>
                            </div>
                        @else
                            <div class="bonuses__item-start-label">{{__('tournament.to_start')}}: <span class="bonuses__item-start-value time-estimated" data-target_time="{{$tournament->getStartDateTime()}}">-</span></div>
                            <div class="bonuses__item-progress-track">
                                <div class="bonuses__item-progress-bar" style="width: 0%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($completed_tournaments) && empty($tournaments))
            <div class="no-records">
                <div class="no-records__title">{{ __('tournament.noTournamentsTitle') }}</div>
                <div class="no-records__hint">{{ __('tournament.noTournamentsHint') }}</div>
            </div>
        @endif

    </div>

    <div id="tournaments__list-past" class="bonuses__list mt-2" style="display: none">

        <div class="bonuses__items">

            @foreach($completed_tournaments as $tournament)
                <div class="bonuses__item">
                    <img src="{{$tournament->getImgUrl()}}" class="bonuses__item-image">
                    <div class="bonuses__item-info ai-center">
                        <div class="bonuses__item-title">{{$tournament->getName()}}</div>
                        <div class="bonuses__item-desc">{{__('tournament.prize_fund')}}:</div>
                        <div class="bonuses__item-fund">{{$tournament->getPrizeFund()}}</div>
                        <a href="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}" class="content-link bonuses__item-button tournaments__info-button w-100">{{__('tournament.details')}}</a>
                    </div>
                </div>
            @endforeach

            @php
                $tournaments = \App\Models\Tournaments\Custom\CustomTournament::getCompletedTournaments();
            @endphp
            @foreach($tournaments as $tournament)
                <div class="bonuses__item flex column">
                    <a href="{{ route('tournaments.info', ['tournament' => $tournament->getId()], false) }}" class="content-link">
                        <img src="{{$tournament->getImgUrl()}}" class="bonuses__item-image">
                    </a>

                    <div class="bonuses__item-info ai-center grow">
                        <div class="bonuses__item-title" style="text-align: center;">{{$tournament->getName()}}</div>
                        <div class="bonuses__item-desc mt-auto" style="margin-top: auto">{{__('tournament.prize_fund')}}:</div>
                        <div class="bonuses__item-fund">{{$tournament->getPrizeFund()}}</div>
                        <a href="{{ $tournament->infoUrl() }}" class="content-link bonuses__item-button tournaments__info-button w-100">{{__('tournament.details')}}</a>
                    </div>
                </div>
            @endforeach

        </div>
        @if(empty($completed_tournaments) && empty($tournaments))
            <div class="no-records">
                <div class="no-records__title">{{ __('tournament.noCompletedTournamentsTitle') }}</div>
                <div class="no-records__hint">{{ __('tournament.noCompletedTournamentsHint') }}</div>
            </div>
        @endif
    </div>
</div>
<script>
    document.querySelector('.bonuses__items').addEventListener('timer-timeout', function (e) {
        const target = e.target;
        const tournament = target.closest('.bonuses__item');
        document.querySelector('#tournaments__list-active .bonuses__items').removeChild(tournament);
        document.querySelector('#tournaments__list-past .bonuses__items').insertBefore(tournament, document.querySelector('#tournaments__list-past .bonuses__items').firstChild);
    })
</script>
