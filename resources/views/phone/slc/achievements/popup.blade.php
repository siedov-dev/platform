@php
    /** @var \App\Models\Slc\Achievements\SlcAchievement $achievement */
    $m_user = \App\MUser::getAuth();
    $is_completed = $achievement->isCompleted($m_user->getId());
@endphp
<div class="popup__content achievement-popup"> <!-- popup__content-image -->
    <div class="mt-3"></div>
    @if($is_completed)
        <div class="shine-item">
            <img class="shine-img" src="/img/achievements/item_light.png">
            <div class="slc__popup-icon @if(!$is_completed) grayscale @endif">
                <img src="{{$achievement->getImage()}}" alt="">
            </div>
        </div>
    @else
        <div class="slc__popup-icon">
            <img src="{{$achievement->getDisabledImage()}}" alt="">
        </div>
    @endif
    <div class="slc__achievement-name mt-2 mb-05">{{$achievement->getTitle()}}</div>
    <p class="achievements__popup-above-progress-bar">{!! $achievement->getData()->getGoalString($m_user)  !!}</p>
    @if($achievement->isCompleted(\Illuminate\Support\Facades\Auth::id()))
        <div class="achievements__popup-progress-bar">
            <div class="achievements__popup-progress filling"></div>
        </div>
    @else
        <div class="achievements__popup-progress-bar">
            <div class="achievements__popup-progress" style="width: {{$achievement->getData()->getCurrentPercent($m_user)}}%"></div>
            <div class="hint">{{ $achievement->getData()->getValueHint($m_user)  }}</div>
        </div>
    @endif
    @if($is_completed && !$achievement->getUserAchievement($m_user->getId())->isRewarded())
        <a href="{{route('slc.achievement-reward', ['id' => $achievement->getId()], false)}}" class="achievements__popup-button close-popup">{{__('slc.get_reward')}}</a>
    @endif
    <div class="flex jc-center ai-center mt-1 achievement_rewards">
        @foreach($achievement->getRewards() as $reward)
            <div class="flex ai-center">
                <div style="width: 2.5rem; height: 2.5rem; z-index: 2; color: #fff;" class="ml-1">
                    <div class="scale-wrapper scale-05">
                        {!! $reward->getInfo()->renderItemIcon() !!}
                    </div>
                </div>
                <div class="ml-05 text-white text-bold">{{$reward->getInfo()->name()}}</div>
            </div>
        @endforeach
        {{--        @if($achievement->getSlcReward() > 0)--}}
        {{--            <div class="flex ai-center text-white text-bold"><img src="/images/svg/balans-icon.svg" alt="" class="mr-05"> {{$achievement->getSlcReward()}}</div>--}}
        {{--        @endif--}}
        {{--        @if($achievement->item_type !== null)--}}
        {{--            <div class="flex ai-center">--}}
        {{--                <div style="width: 2rem; height: 2rem; z-index: 2; color: #fff;" class="ml-1">--}}
        {{--                    <div class="scale-wrapper scale-04">--}}
        {{--                            {!! $achievement->getInfo()->renderItemIcon() !!}--}}
        {{--                    </div>--}}
        {{--                </div>--}}
        {{--                <div class="ml-05 text-white text-bold">{{$achievement->getInfo()->name()}}</div>--}}
        {{--            </div>--}}
        {{--        @endif--}}
    </div>
</div>
