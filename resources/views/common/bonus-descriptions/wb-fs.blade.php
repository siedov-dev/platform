@php
    /** @var \App\Models\Bonuses\KUserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты

    /** @var \App\Models\BonusPatterns $pattern */
    $pattern = \App\Models\BonusPatterns::find($bonus->pattern_id);
    preg_match('#(\d)#', $pattern->name, $match);
    $dep_n = $match[1];
    $dep_mult = $bonus->getJsonParam('multiplier');
    $wager = $bonus->getJsonParam('wager');
    $min_dep = $bonus->getJsonParam('min_sum');
    $max_dep = $bonus->getJsonParam('max_sum');
    $fs_sum = $bonus->getJsonParam('fs_dep_sum');
    $curr_name = $bonus->getCurrencyName();


    $pattern_fd_fs_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 1);
    $pattern_fd_fs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 2);
    $pattern_fd_fs_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 3);
    $pattern_fd_ndfs_1 =\App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 4);
    $pattern_fd_ndfs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 5);


//    $nd_fs = $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
//    $nd_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    /** @var \App\Models\BonusPatterns $spins_ptn */
    $spins_ptn = match (intval($dep_n)) {
        1 => $pattern_fd_fs_1,
        2 => $pattern_fd_fs_2,
        3 => $pattern_fd_fs_3,
        4 => $pattern_fd_ndfs_1,
        5 => $pattern_fd_ndfs_2,
    };
    $fs_bonus = $spins_ptn->createForUser(\App\MUser::getAuth(), $bonus->getCurrency(), true);
    $fs_game = $fs_bonus->getGameName();

@endphp

<div class="params">
    @if($dep_n < 4)
        <div class="param">{{__('bonus.fs_for_dep_'.$dep_n)}}:</div>
    @else
        <div class="param">{{__('bonus.fs_non_dep_'.$dep_n)}}:</div>
    @endif
    <div class="">
        @if(!empty($fs_game))
            <div class="param">💫 {{__('bonus.game')}}: <span class="value"> {{$fs_game}} </span></div>
        @else
            <div class="param">💫 {{__('bonus.game')}}: <span class="value"> {{__('games.allGames')}} {{__('games.category')}} {{__('games.slots')}}</span></div>
        @endif
        <div class="param">🎰 {{__('bonus.min_dep')}}: <span class="value"> {{ $fs_sum }} {{$symbol}}</span></div>
        <div class="param">🔄 {{__('bonus.spins')}}: <span class="value">{{ $spins_ptn->getBonusParam('freespins', $currency) }}</span></div>
        <div class="param">💰 {{__('bonus.stake')}}: <span class="value">{{ $spins_ptn->getBonusParam('fs_bet', $currency) }} {{$symbol}}</span></div>
        @if($bonus->getJsonParam('max_x_win', 0) > 0)
            <div class="param">💸 {{__('bonus.max_x_win_sum')}}: <span class="value"> X{{$bonus->getJsonParam('max_x_win')}}</span></div>
        @elseif($bonus->getJsonParam('max_win', 0) > 0)
            <div class="param">💸 {{__('bonus.max_win_sum')}}: <span class="value"> {{$bonus->getJsonParam('max_win')}} {{$symbol}}</span></div>
        @endif
        <div class="param">🤑 {{__('bonus.wager')}}:  <span class="value">{{$bonus->getJsonParam('wager')}}</span></div>
        <div class="param">⏰ {{__('bonus.available_to')}}:  <span class="value">{{$bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
    </div>
</div>
