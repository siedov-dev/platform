@php
    /** @var $bonus \App\Models\Bonuses\WelcomeBonusPack */
    /** @var \App\Models\BonusPatterns $pattern */
    $dep_n = $bonus->tries;
    $curr_name = $bonus->getCurrencyName();

    $pattern_fd_dep_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 1);
    $pattern_fd_dep_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 2);
    $pattern_fd_dep_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 3);

    $pattern_fd_fs_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 1);
    $pattern_fd_fs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 2);
    $pattern_fd_fs_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 3);

    $pattern_fd_ndfs_1 =\App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 4);
    $pattern_fd_ndfs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 5);

    $total_money = $pattern_fd_dep_1->getBonusParam('max_sum', $curr_name, 0);
    $total_money += $pattern_fd_dep_2->getBonusParam('max_sum', $curr_name, 0);
    $total_money += $pattern_fd_dep_3->getBonusParam('max_sum', $curr_name, 0);

    $total_fs = $pattern_fd_fs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_2->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_3->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    $nd_fs = $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
    $nd_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    $spins = match (intval($dep_n)) {
        4 => $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0),
        5 => $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0),
    };
    $nd_disabled = false;
@endphp
<div class="bonuses__wb-wrapper bonus-activate-dialog">
    <div class="cabinet__bonus-title">{{__('bonuses.welcome_bonus')}}</div>
    <div class="conditions" onclick="Modal.showBottom(this.parentElement.querySelector('.conditions-modal'))">
        <img src="/img/bonuses/cond.svg" alt="conditions">
        &nbsp;{{__('bonus.conditions')}}
    </div>
    <p class="mt-1">{{$total_money}} {{$curr_name}} + {{$total_fs}} FS</p>
    <p class="mt-1">{{__('bonuses.welcome_bonus-ndfs', ['ndfs' => $nd_fs])}}</p>
    <div class="w-100 line-through"></div>
    <div class="deposits-wrapper scroll-hidden">
        <div class="dep done">
            <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
        </div>
        <div class="dep done">
            <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
        </div>
        <div class="dep done">
            <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
        </div>

        @if($dep_n > 4)
            <div class="dep done">
                <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
            </div>
        @else
            <div class="dep">
                <div class="title">{{__('bonuses.welcome_bonus-1ndep')}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0)}}</div>
            </div>
        @endif
        <div class="dep">
            <div class="title">{{__('bonuses.welcome_bonus-2ndep')}}</div>
            <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0)}}</div>
        </div>
    </div>
    <div class="w-100 flex mt-1">
        <p>{{__('bonuses.welcome_bonus-rewards')}}:</p>
    </div>
    <div class="rewards">
        <div>
            <img src="/img/bonuses/fs.svg">
            <p>{{__('bonus.freespins')}}</p>
            <div class="amount"><span id="w-bonus-spins">{{$spins}}</span></div>
        </div>
    </div>

    @if($bonus->isPending())
        <div class="timeout mt-2 rewards">
            <div>
                <div class="flex ai-center pt-1">
                    <img src="/img/icons/time.svg" class="mr-05"> {{__('bonus.multi_day-next_day_in')}}
                </div>
                <div class="time update-timer-block pb-1" data-target_time="{{\Carbon\Carbon::parse($bonus->completed_at)->addDays($bonus->tries === 5? 2 : 1)}}" data-hours-only="true">
                    <div><span class="timer-hours-left">-</span>h</div>&nbsp;:&nbsp;
                    <div><span class="timer-minutes-left">-</span>m</div>
                </div>
            </div>
        </div>
    @else
        <div class="flex w-100 jc-center p-1">
            <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}" class="button w-100 grow cabinet__bonus-activate-btn button_green">
                {{__('bonus.get-freespins')}}
            </div>
        </div>
    @endif

    <div class="conditions-modal page-info" style="display: none;">
        <div class="cabinet__bonus-title">{{__('bonus.rules-and-conditions')}}</div>
        <p class="mt-1">{{__('bonus.wb-conditions', ['amount' => $total_money, 'curr' => $curr_name, 'fs' => $total_fs])}}</p>
    </div>
</div>
