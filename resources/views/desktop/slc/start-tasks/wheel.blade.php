@php
    $task_status = \App\Models\Slc\SlcCompletedTask::getTaskStatus(\Illuminate\Support\Facades\Auth::id(), $slcTaskType);
    $slc_task = \App\Models\Slc\SlcTask::getByType($slcTaskType);
    $task_checker = $slcTaskType->getTaskChecker();
    if ($task_status === \App\Models\Slc\SlcTaskStatus::NotCompleted) {
        if ($task_checker->checkComplete(\App\MUser::getAuth(), $slc_task)) $task_status = \App\Models\Slc\SlcCompletedTask::getTaskStatus(\Illuminate\Support\Facades\Auth::id(), $slcTaskType);
    }
    $completed_task = \App\Models\Slc\SlcCompletedTask::getTask(\Illuminate\Support\Facades\Auth::id(), $slcTaskType)
@endphp
@if($task_status === \App\Models\Slc\SlcTaskStatus::Completed)
<div class="my-tasks__block-task main-tasks popup-link {{$task_status->styleName()}}" href="{{route('wheel.modal', [], false)}}" data-ajax="modal">
@else
<a class="my-tasks__block-task main-tasks popup-link {{$task_status->styleName()}}" href="{{route('wheel.modal', [], false)}}" data-ajax="modal">
@endif
    <div class="my-tasks__block-task-wrap-circle">
        <div class="my-tasks__block-task-circle">
            @if($task_status !== \App\Models\Slc\SlcTaskStatus::Rewarded)
                <p>{{$task_checker->getCurrentValue(\App\MUser::getAuth(), $slc_task)}}/{{$task_checker->getGoalValue($slc_task)}}</p>
            @endif
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="M1.00041 12.9044C1.00874 11.8198 1.64682 10.8992 2.62059 10.5667C3.59692 10.2329 4.69177 10.5449 5.32985 11.4136C6.21841 12.6238 7.06213 13.8679 7.92571 15.0967C8.37096 15.7296 8.78353 15.745 9.26721 15.1473C12.9041 10.6615 16.5474 6.18153 20.1722 1.68617C20.8173 0.886018 21.6809 0.882174 22.244 1.18712C23.0249 1.61058 23.2427 2.64393 22.6995 3.4364C21.9704 4.49922 21.2241 5.55051 20.4848 6.60628C16.9248 11.6955 13.3641 16.7841 9.80406 21.8733C9.22621 22.6998 8.46961 23.0034 7.66946 22.6466C7.35619 22.5069 7.05316 22.2481 6.85328 21.9669C5.05245 19.4306 3.27468 16.8776 1.49242 14.3285C1.19453 13.9025 0.989523 13.44 1.00041 12.9044Z"/>
            </svg>
        </div>
    </div>
    <div class="my-tasks__block-task-name">
        <h4>{{$slc_task?->getTaskName(\App\MUser::getAuth()->getCurrencyName())}}</h4>
        @if($task_status === \App\Models\Slc\SlcTaskStatus::Rewarded)
            <p class="reward-received">{{__('slc.rewarded')}}</p>
        @else
            <div class="flex ai-center">
                <div style="width: 1rem; height: 1rem;">
                    <div class="scale-wrapper scale-02">
                        {!! $task_checker->getReward($slc_task)->getInfo()->renderItemIcon()  !!}
                    </div>
                </div>
                <p class="ml-05">{{$task_checker->getReward($slc_task)->getInfo()->name()}}
                    @if($slc_task->getType() === \App\Models\Slc\SlcTaskType::FirstSteps)
                        + {{__('slc.user_daily_tasks')}}
                    @endif
                </p>
            </div>
        @endif
    </div>
    @if($task_status === \App\Models\Slc\SlcTaskStatus::Completed)
        <div class="button-shade">
            <a class="button_green button" href="{{route('slc.reward-modal', ['task_id' => $completed_task?->getId()], false)}}" data-ajax="modal" data-popup-type="slc-popup">
                {{__('slc.take-task_reward')}}
            </a>
        </div>
    @endif
@if($task_status !== \App\Models\Slc\SlcTaskStatus::Completed)
</a>
@else
</div>
@endif
