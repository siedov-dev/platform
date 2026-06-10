@php
    /** @var \App\Models\Slc\Achievements\SlcAchievement $achievement */
    $new_ach = \App\Models\Slc\Achievements\SlcUserAchievement::newAchievementsCount(\App\MUser::getAuth());
@endphp
<div class="page__head">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing')}}">{{__('slc.back_to_main')}}</a>
        </div>
        <div class="page__head-wrap-title">
            <h1 class="page__title flex">
                {{__('slc.achievements')}}
                <a href="{{route('slc.page-info', ['page' => 'achievements'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
            </h1>
        </div>
    </div>
</div>
<main class="main">
    <div class="page__content">
        <div class="n_container">
            <div class="flex">
                <div class="tabs w-100">
                    <label class="tabs__tab @if(!$show_ranks) tabs__tab_active @endif" data-target="#achievements-tab">
                        {{ __('slc.achievements') }}
                        @if($new_ach > 0)
                            <span class="label">New</span>
                        @endif
                    </label>
                    <label class="tabs__tab ranks-tab @if($show_ranks) tabs__tab_active @endif" data-target="#ranks-tab">
                        {{ __('cabinet.ranks') }}
                        @if($rank_rewards > 0)
                            <span class="label">New</span>
                        @endif
                    </label>
                </div>
            </div>
            <div id="achievements-tab" class="tabContent" @if($show_ranks) style="display: none;" @endif>
                <div class="page-ad-text mb-1">{{__('slc.achievements-ad-text')}}</div>
                <div class="slc__achievement-items">
                    @foreach($achievements as $achievement)
                        @php
                            $is_completed = $achievement->checkCompleted(\App\MUser::getAuth());
                        @endphp
                        {{--                        @if($achievement->checkCompleted(\App\MUser::getAuth())) @continue @endif--}}
                        <div class="slc__achievement-item @if(!$is_completed) not-completed @endif">
                            @if($achievement->isCompleted(\Illuminate\Support\Facades\Auth::id()) && !$achievement->getUserAchievement(\Illuminate\Support\Facades\Auth::id())->isRewarded())
                                <span class="label">New</span>
                            @endif
                            <div class="">
                                <a href="{{route('slc.achievement-popup', ['achievement_id' => $achievement->getId()], false)}}" class="achievements__block-image wrap-lazy-load-image popup-link" data-ajax="modal" data-popup-type="bottom-popup">
                                    @if(!$is_completed)
                                        <img src="{{$achievement->getDisabledImage()}}" alt="">
                                    @else
                                        <img src="{{$achievement->getImage()}}" alt="">
                                    @endif
                                </a>
                                <div class="achievement-name">{{$achievement->getTitle()}}</div>
                                {{--                                <a href="{{route('slc.achievement-popup', ['achievement_id' => $achievement->getId()], false)}}" class="info-icon popup-link" data-ajax="modal" data-popup-type="slc-popup"></a>--}}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div id="ranks-tab" class="tabContent" @if(!$show_ranks) style="display: none;" @endif>
                @include('phone.slc.achievements.ranks.ranks')
            </div>
        </div>
    </div>
</main>
@include('phone.blocks.bottom-deposit-btn')
