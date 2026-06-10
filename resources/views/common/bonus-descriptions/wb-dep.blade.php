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

//    $user = \App\MUser::getAuth();
//    $user_bonuses = \App\Services\KBonus\KBonus::getInstance()->getUserBonuses($user->getId(), $user->getCurrency());
//    $reg_ptn_ids = \App\Models\BonusPatterns::getRegPatternIds($currency);
//    $users_fd_bonuses = [];
//    foreach ($user_bonuses as $ubonus) {
//        if (in_array($ubonus->pattern_id, $reg_ptn_ids)) $users_fd_bonuses[$ubonus->pattern_id] = $ubonus;
//    }
//    krsort($users_fd_bonuses);
//    $users_fd_bonuses = array_values($users_fd_bonuses);

//    $users_fd_bonuses = \App\Models\Bonuses\UserBonus::where('user_id', $bonus->user_id)
//            ->whereIn('pattern_id', \App\Models\BonusPatterns::getRegPatternIds($bonus->curr_name))
//            ->orderBy('pattern_id')
//            ->get();

//    $pattern_fd_fs_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 1);
//    $pattern_fd_fs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 2);
//    $pattern_fd_fs_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 3);

@endphp

<div class="params">
    <div class="param">{{__('bonus.for_dep_'.$dep_n)}}:</div>
    <div class="">
        <div class="param">🎰 {{__('bonus.min_dep')}} <span class="value"> {{ $min_dep }} {{$symbol}}</span></div>
        <div class="param">🎰 {{__('bonus.game')}}:  <span class="value">{{__('games.allGames')}}</span></div>
        <div class="param">💰 {{__('bonus.dep_prc')}}:
            <span class="value">{{ $bonus->getJsonParam('multiplier', $curr_name, 0)}} %</span>
        </div>
        <div class="param">💸 {{__('bonus.max_bonus_sum')}}: <span class="value">{{ $max_dep }} {{$symbol}}</span></div>
        @if($bonus->getJsonParam('max_x_win', 0) > 0)
            <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> X{{$bonus->getJsonParam('max_x_win')}} {{__('bonus.of_deposit')}} {{$symbol}}</span></div>
        @elseif($bonus->getJsonParam('max_win', 0) > 0)
            <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> {{$bonus->getJsonParam('max_win')}} {{$symbol}}</span></div>
        @endif
        <div class="param">🤑 {{__('bonus.wager')}}:  <span class="value">{{$bonus->getJsonParam('wager')}}</span></div>
        <div class="param">⏰ {{__('bonus.available_to')}}:  <span class="value">{{$bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
    </div>
</div>
