@php
/** @var \App\Models\Slc\SlcCompletedTask $completed_task */
/** @var \App\Models\Slc\SlcTask $task */
$task_checker = $task->getType()->getTaskChecker();
@endphp
<div class="popup achievements__popup my-task-popup">
    <div class="popup__body">
        <div class="popup__content">
            <form action="{{route('slc.reward-modal', [], false)}}" method="POST">
                @csrf
                <input type="hidden" name="task_id" value="{{$completed_task->getId()}}">
                <h2>{{__('slc.task_completed')}}</h2>
                <div class="achievements__popup-block-icon">
                    {!! $task_checker->getReward($task)->getInfo()->renderItemIcon()  !!}
{{--                    <img src="/images/icons/my-task-popup.png" alt="">--}}
                </div>
                <p class="achievements__popup-price">{{$task_checker->getReward($task)->getInfo()->name()}}</p>
                @if($task->getType() === \App\Models\Slc\SlcTaskType::FirstSteps)
                    <p class="achievements__popup-price">{{__('slc.user_daily_tasks')}}</p>
                @endif
                <button type="submit" class="achievements__popup-button close-popup">{{__('slc.get_reward')}}</button>
                <div class="popup__close close-popup" onclick="Modal.hide(this.closest('.modal'))">
                    <img src="/images/svg/close-pop-up.svg" alt="" class="dekstop">
                    <img src="/images/svg/close-pop-up-mobile.svg" alt="" class="mobile">
                </div>
                <div class="achievements__popup-block-image">
                    <img class="achievements__popup-bg desktop" src="/images/my-task-popup-content-bg.jpg" alt="">
                    <img class="achievements__popup-bg mobile" src="/images/my-task-popup-content-bg-mobile.jpg" alt="">
                </div>
            </form>
        </div>
    </div>
</div>
