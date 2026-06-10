@php
    //welcome bonus pack info
    /** @var \App\Models\Bonuses\UserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
    $curr_name = $bonus->getCurrencyName();
    $user = \App\MUser::getAuth();

    $pattern_fd_dep_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 1);

    $pattern_fd_fs_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 1);

    $pattern_fd_ndfs_1 =\App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 4);

    $min_dep = $pattern_fd_dep_1->getBonusParam('min_sum', $curr_name);
    $max_dep = $pattern_fd_dep_1->getBonusParam('max_sum', $curr_name);
    $fs_sum = $pattern_fd_dep_1->getBonusParam('fs_dep_sum', $curr_name);
    $dep_wager = $pattern_fd_dep_1->getBonusParam('wager', $curr_name);
    $dep_x_win = $pattern_fd_dep_1->getBonusParam('max_x_win', $curr_name);
    $fs_wager = $pattern_fd_fs_1->getBonusParam('wager', $curr_name);
    $fs_x_win = $pattern_fd_fs_1->getBonusParam('max_x_win', $curr_name);

//    $nd_fs = $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
//    $nd_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);
$data = [
    'curr' => $curr_name,
    'min_dep' => $min_dep,
    'max_dep' => $max_dep,
    'fs_sum' => $fs_sum,
    'dep_wager' => $dep_wager,
    'dep_x_win' => $dep_x_win,
    'fs_wager' => $fs_wager,
    'fs_x_win' => $fs_x_win,
];

@endphp

<div class="conditions-modal">
    <div class="cabinet__bonus-title mb-1">{{__('bonus.rules-and-conditions')}}</div>
    {{--    <p class="mt-1">{{__('bonus.wb-conditions', ['amount' => $total_money, 'curr' => $curr_name, 'fs' => $total_fs])}}</p>--}}
    <p>{{__('bonuses.wb-cond-1',$data)}}</p>
    <p>{{__('bonuses.wb-cond-2',$data)}}</p>
    <p>{{__('bonuses.wb-cond-4',$data)}}</p>
    <p>{{__('bonuses.wb-cond-5',$data)}}</p>
    <p>{{__('bonuses.wb-cond-6',$data)}}</p>
    <p>{{__('bonuses.wb-cond-7',$data)}}</p>
    <p>{{__('bonuses.wb-cond-8',$data)}}</p>
    <p>{{__('bonuses.wb-cond-9',$data)}}</p>
    <p>{{__('bonuses.wb-cond-10',$data)}}</p>
    <p>{{__('bonuses.wb-cond-11',$data)}}</p>
    <p>{{__('bonuses.wb-cond-12',$data)}}</p>
    <p>{{__('bonuses.wb-cond-13',$data)}}</p>
    <p>{{__('bonuses.wb-cond-14',$data)}}</p>
    <p>{{__('bonuses.wb-cond-15',$data)}}</p>
    <p>{{__('bonuses.wb-cond-16',$data)}}</p>
    <p>{{__('bonuses.wb-cond-17',$data)}}</p>
    <p>{{__('bonuses.wb-cond-18',$data)}}</p>
</div>
