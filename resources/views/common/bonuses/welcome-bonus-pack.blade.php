@php
/** @var $bonus \App\Models\Bonuses\SharedMoneyBonus */
    $active = $bonus->isCancelable();
    $ptn = \App\Models\BonusPatterns::getRegPattern($bonus->curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 1);
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

    $total_dep = $pattern_fd_dep_1->getBonusParam('multiplier', $curr_name, 0);
    $total_dep += $pattern_fd_dep_2->getBonusParam('multiplier', $curr_name, 0);
    $total_dep += $pattern_fd_dep_3->getBonusParam('multiplier', $curr_name, 0);

    $total_fs = $pattern_fd_fs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_2->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_3->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);
@endphp
<div class="cabinet__bonuses-wrapper no-click-hide">
<div class="cabinet__bonuses-item cabinet__bonus welcome-bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     data-id="{{ $bonus->getId() }}" style="--bgImg: url('/img/bonuses/wb/{{$bonus->curr_name}}-wb.png')">

    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-time">
            @if($bonus->isCancelable())
                <img src="/img/icons/calendar.svg">
                <div>{{__('bonus.to')}}&nbsp;{{\Carbon\Carbon::parse($bonus->active_to)->isoFormat('DD MMMM YYYY')}}</div>
            @endif
        </div>
        <div class="mt-1"></div>
        <div class="cabinet__bonus-title">
            <div>{{ $bonus->getName() }}</div>
            <div class="cabinet__bonus-sub_title">{{__('bonus.to')}} {{$total_money}} {{$curr_name}} + {{$total_fs}} FS</div>
        </div>
        <div class="bonus-btn-wrap">
            {!! $bonus->getRenderer()->renderPlayButton() !!}
            @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
        </div>
    </div>
</div>
</div>
