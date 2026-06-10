<div class="page__head">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing', [], false)}}">{{__('slc.back_to_main')}}</a>
        </div>
        <h1 class="page__title flex">
            {{__('slc.my_tasks')}}
            <a href="{{route('slc.page-info', ['page' => 'my_tasks'], false)}}" class="page-info-icon ml-05" data-ajax="modal"></a>
        </h1>

    </div>
</div>
<main class="main">
    <div class="page__content">
        <div class="n_container">
            <div class="flex">
                <div class="tabs">
                    @if(!$is_daily_allowed)
                        <label class="tabs__tab tabs__tab_active" data-target="#tasks-main-tab">
                            {{ __('slc.tasks') }}
                        </label>
                    @else
                        <label class="tabs__tab tabs__tab_active" data-target="#tasks-today-tab">
                            {{ __('slc.tasks') }}
                        </label>
                    @endif
                    <label class="tabs__tab" data-target="#tasks-daily-tab">
                        {{ __('slc.daily_tasks') }}
                        @if(\App\MUser::getAuth()->getValue('login_reward_date', null) !== now()->format('Y-m-d'))
                            <span class="label">New</span>
                        @endif
                    </label>
                </div>
            </div>
            @if(!$is_daily_allowed)
            <div id="tasks-main-tab" class="tabContent">
                <div class="page-ad-text">{{__('slc.tasks-ad-text')}}</div>
                @php
                    $tasks_new = [];
                    $tasks_done = [];
                    $tasks_rewarded = [];
                    $c = 0;
                    foreach (\App\Models\Slc\SlcTaskType::cases() as $slcTaskType) {
                        $task_status = \App\Models\Slc\SlcCompletedTask::getTaskStatus(\Illuminate\Support\Facades\Auth::id(), $slcTaskType);
                        $slc_task = \App\Models\Slc\SlcTask::getByType($slcTaskType);
                        $task_checker = $slcTaskType->getTaskChecker();
                        if ($task_status === \App\Models\Slc\SlcTaskStatus::NotCompleted) {
                            if ($task_checker->checkComplete(\App\MUser::getAuth(), $slc_task)) $task_status = \App\Models\Slc\SlcCompletedTask::getTaskStatus(\Illuminate\Support\Facades\Auth::id(), $slcTaskType);
                        }
                        if ($task_status === \App\Models\Slc\SlcTaskStatus::Rewarded) {
                            $tasks_rewarded[] = $slcTaskType;
                        } elseif ($task_status === \App\Models\Slc\SlcTaskStatus::Completed) {
                            $tasks_done[] = $slcTaskType;
                        } else {
                            $tasks_new[] = $slcTaskType;
                        }
                    }
                    $tasks = [...$tasks_done, ...$tasks_new, ...$tasks_rewarded];
                @endphp
                @foreach($tasks as $slcTaskType)
                    <div class="task-gradient-wrapper-{{$c%4}}">
                        {!! $slcTaskType->renderTaskPad() !!}
                    </div>
                    @php
                        $c++;
                    @endphp
                @endforeach
            </div>
            @else
            <div id="tasks-today-tab" class="tabContent">
                <div class="page-ad-text">{{__('slc.tasks-ad-text')}}</div>
                <p class="next-task-in mt-2 mb-2">{{__('tasks.next_tasks_in')}}:
                    <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">00:00:00</span>
                </p>
                @php
                    $user = \App\MUser::getAuth();
                    /** @var \App\Models\Slc\UserTasks\UserDailyTask[] $_tasks */
                    $_tasks = \App\Models\Slc\UserTasks\UserDailyTask::getTodaysTasks($user)->sortBy('is_completed');
                    $tasks_new = [];
                    $tasks_done = [];
                    $tasks_rewarded = [];
                    foreach ($_tasks as $task) {
                        if ($task->isRewarded()){
                            $tasks_rewarded[] = $task;
                        } elseif ($task->isCompleted()) {
                            $tasks_done[] = $task;
                        } else {
                            $tasks_new[] = $task;
                        }
                    }
                    $todays_tasks = [...$tasks_done, ...$tasks_new, ...$tasks_rewarded];
                    $c = 0;
                @endphp
                <h4>{{__('tasks.todays_tasks')}}</h4>
                @foreach($todays_tasks as $today_task)
                    <div class="task-gradient-wrapper-{{$c%4}}">
                        {!! $today_task->getTask($user)->renderTaskItem() !!}
                    </div>
                    @php
                        $c++;
                    @endphp
                @endforeach
            </div>
            @endif
            <div id="tasks-daily-tab" class="tabContent" style="display: none">
                <div class="page-ad-text mb-1">{{__('slc.tasks-login-ad-text')}}</div>
                @php
                    $c = 0;
                @endphp

                @foreach(range(1, 7) as $day_n)
                    @php
                        $rewards = \App\Models\Slc\SlcDailyReward::getForDay($day_n);
                    @endphp
                    <div class="task-gradient-wrapper-{{$c%4}}">
                        <div class="my-tasks__block-task popup-link @if($day_n != $next_daily && ($active_day > $day_n || ($active_day == $day_n && $login_rewarded))) done @endif">
                            <div class="my-tasks__block-task-wrap-circle">
                                <div class="my-tasks__block-task-circle">
                                    @if($day_n != $next_daily && $active_day >= $day_n)
                                        @include('common.blocks.circle-progress', ['r' => 26, 'prc' => 100])
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M1.00041 12.9044C1.00874 11.8198 1.64682 10.8992 2.62059 10.5667C3.59692 10.2329 4.69177 10.5449 5.32985 11.4136C6.21841 12.6238 7.06213 13.8679 7.92571 15.0967C8.37096 15.7296 8.78353 15.745 9.26721 15.1473C12.9041 10.6615 16.5474 6.18153 20.1722 1.68617C20.8173 0.886018 21.6809 0.882174 22.244 1.18712C23.0249 1.61058 23.2427 2.64393 22.6995 3.4364C21.9704 4.49922 21.2241 5.55051 20.4848 6.60628C16.9248 11.6955 13.3641 16.7841 9.80406 21.8733C9.22621 22.6998 8.46961 23.0034 7.66946 22.6466C7.35619 22.5069 7.05316 22.2481 6.85328 21.9669C5.05245 19.4306 3.27468 16.8776 1.49242 14.3285C1.19453 13.9025 0.989523 13.44 1.00041 12.9044Z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="my-tasks__block-task-name">
                                <h4>
                                    {{__('slc.day')}} {{$day_n}}
                                </h4>
                                @if($day_n == $next_daily)
                                    <p class="get-through">{{__('slc.get_reward_in')}}:
                                        <span class="time-estimated" data-target_time="{{now()->endOfDay()}}" data-divider=":">00:00:00</span>
                                    </p>
                                @endif
                                <p class="reward-received">{{__('slc.rewarded')}}</p>
                                @if($day_n > $active_day || ($day_n == $active_day && !$login_rewarded) || $day_n == $next_daily)
                                    <div class="flex ai-center">
                                        @foreach($rewards as $reward)
                                            <div style="width: 1rem; height: 1rem;">
                                                <div class="scale-wrapper scale-02">
                                                    {!! $reward->getInfo()->renderItemIcon() !!}
                                                </div>
                                            </div>&nbsp;
                                            <p class="mr-1">{{$reward->getInfo()->name()}}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if(!$login_rewarded && $day_n == $active_day)
                                <div class="button-shade">
                                    <a class="button_green button" href="{{route('slc.reward-modal', ['login_day' => $day_n,], false)}}" data-ajax="modal" data-popup-type="slc-popup">
                                        {{__('slc.take-task_reward')}}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    @php
                        $c++;
                    @endphp
                @endforeach
            </div>
        </div>
    </div>
</main>
