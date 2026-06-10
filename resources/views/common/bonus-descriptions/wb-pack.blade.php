@php
    //welcome bonus pack info
    /** @var \App\Models\Bonuses\KUserBonus $bonus */
    $game_name = $bonus->getGameName();// название игры если одна
    $currency = $bonus->getCurrencyName(); //название валюты RUB
    $symbol = \App\Wallet::currencySymbol($bonus->currency);// символ валюты
    $curr_name = $bonus->getCurrencyName();
    $user = \App\MUser::getAuth();

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

//    $nd_fs = $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
//    $nd_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    /** @var \App\Models\BonusPatterns $spins_ptn */
    $spins_ptns = [
        $pattern_fd_fs_1,
        $pattern_fd_fs_2,
        $pattern_fd_fs_3,
    ];

    $dep_ptns = [
        $pattern_fd_dep_1,
        $pattern_fd_dep_2,
        $pattern_fd_dep_3,
    ];

    $non_dep_ptns = [
        $pattern_fd_ndfs_1,
        $pattern_fd_ndfs_2,
    ];
@endphp

<a class="wb-conditions @if(!$bonus->isPaused()) mt-3 @endif" href="{{route('wb.cond', ['bonus_id' => $bonus->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
    <img src="/img/bonuses/cond.svg" alt="conditions">
    &nbsp;{{__('bonus.conditions')}}
</a>

<div class="bonus_name @if(!$bonus->isPaused()) mt-4 @endif">{{__('bonuses.welcome_bonus')}}</div>
<div class="sub-title">{{__('bonus.to')}} {{$total_money}} {{$curr_name}} + {{$total_fs}} FS</div>

<div class="params">
    <div class="param">{{__('bonus.common_conditions')}}:</div>
    <div class="param">📅 {{__('bonus.available_to')}}: <span class="value">{{$bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
    <div class="param">💰 {{__('bonus.total_deposit')}}: <span class="value">{{$total_dep}} %</span></div>
    <div class="param">💸 {{__('bonus.max_bonus_sum')}}: <span class="value">{{$total_money}} {{$symbol}}</span></div>
    <div class="param">🔄 {{__('bonus.total_freespins')}}: <span class="value">{{$total_fs}}</span></div>
</div>

@foreach(range(1,3) as $dep_n)
    @php
        $dep_bonus = $dep_ptns[$dep_n - 1]->createForUser($user, $bonus->getCurrency(), true);
    @endphp
    <div class="params">
        <div class="param">{{__('bonus.for_dep_'.$dep_n)}}:</div>
        <div class="">
            <div class="param">🎰 {{__('bonus.min_dep')}} <span class="value"> {{ $dep_bonus->getJsonParam('min_sum') }} {{$symbol}}</span></div>
            <div class="param">🎰 {{__('bonus.game')}}:  <span class="value">{{__('games.allGames')}}</span></div>
            <div class="param">💰 {{__('bonus.dep_prc')}}: <span class="value">{{ $dep_bonus->getJsonParam('multiplier', $curr_name, 0)}} %</span></div>
            <div class="param">💸 {{__('bonus.max_bonus_sum')}}: <span class="value">{{ $dep_bonus->getJsonParam('max_sum') }} {{$symbol}}</span></div>
            @if($bonus->getJsonParam('max_x_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> X{{$dep_bonus->getJsonParam('max_x_win')}} {{__('bonus.of_deposit')}} {{$symbol}}</span></div>
            @elseif($bonus->getJsonParam('max_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> {{$dep_bonus->getJsonParam('max_win')}} {{$symbol}}</span></div>
            @endif
            <div class="param">🤑 {{__('bonus.wager')}}:  <span class="value">{{$dep_bonus->getJsonParam('wager')}}</span></div>
            <div class="param">⏰ {{__('bonus.available_to')}}:  <span class="value">{{$dep_bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
        </div>
    </div>

    @php
        $fs_bonus = $spins_ptns[$dep_n - 1]->createForUser($user, $bonus->getCurrency(), true);
        $fs_game = $fs_bonus->getGameName();
        $fs_sum = $dep_bonus->getJsonParam('fs_dep_sum');
    @endphp
    <div class="params">
        <div class="param">{{__('bonus.fs_for_dep_'.$dep_n)}}:</div>
        <div class="">
            @if(!empty($fs_game))
                <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{$fs_game}} </span></div>
            @else
                <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{__('games.allGames')}} {{__('games.category')}} {{__('games.slots')}}</span></div>
            @endif
            <div class="param">🎰 {{__('bonus.min_dep')}} <span class="value"> {{ $fs_sum }} {{$symbol}}</span></div>
            <div class="param">🔄 {{__('bonus.spins')}}: <span class="value">{{ $spins_ptns[$dep_n - 1]->getBonusParam('freespins', $currency) }}</span></div>
            <div class="param">💰 {{__('bonus.stake')}}: <span class="value">{{ $spins_ptns[$dep_n - 1]->getBonusParam('fs_bet', $currency) }} {{$symbol}}</span></div>
            @if($fs_bonus->getJsonParam('max_x_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_x_win_sum')}} <span class="value"> X{{$fs_bonus->getJsonParam('max_x_win')}}</span></div>
            @elseif($fs_bonus->getJsonParam('max_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> {{$fs_bonus->getJsonParam('max_win')}} {{$symbol}}</span></div>
            @endif
            <div class="param">🤑 {{__('bonus.wager')}}:  <span class="value">{{$fs_bonus->getJsonParam('wager')}}</span></div>
            <div class="param">⏰ {{__('bonus.available_to')}}:  <span class="value">{{$fs_bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
        </div>
    </div>
@endforeach

@foreach(range(4,5) as $dep_n)
    @php
        $fs_bonus = $non_dep_ptns[$dep_n - 4]->createForUser($user, $bonus->getCurrency(), true);
        $fs_game = $fs_bonus->getGameName();
    @endphp
    <div class="params">
        <div class="param">{{__('bonus.fs_non_dep_'.$dep_n)}}:</div>
        <div class="">
            @if(!empty($fs_game))
                <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{$fs_game}} </span></div>
            @else
                <div class="param">💫 {{__('bonus.game')}} <span class="value"> {{__('games.allGames')}} {{__('games.category')}} {{__('games.slots')}}</span></div>
            @endif
            <div class="param">🔄 {{__('bonus.spins')}}: <span class="value">{{ $non_dep_ptns[$dep_n - 4]->getBonusParam('freespins') }}</span></div>
            <div class="param">💰 {{__('bonus.stake')}}: <span class="value">{{ $non_dep_ptns[$dep_n - 4]->getBonusParam('fs_bet', $currency) }} {{$symbol}}</span></div>
            @if($fs_bonus->getJsonParam('max_x_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_x_win_sum')}} <span class="value"> X{{$fs_bonus->getJsonParam('max_x_win')}}</span></div>
            @elseif($fs_bonus->getJsonParam('max_win', 0) > 0)
                <div class="param">💸 {{__('bonus.max_win_sum')}} <span class="value"> {{$fs_bonus->getJsonParam('max_win')}} {{$symbol}}</span></div>
            @endif
            <div class="param">🤑 {{__('bonus.wager')}}:  <span class="value">{{$fs_bonus->getJsonParam('wager')}}</span></div>
            <div class="param">⏰ {{__('bonus.available_to')}}:  <span class="value">{{$fs_bonus->getExpirationDate()->isoFormat('DD.MM.YYYY')}}</span></div>
        </div>
    </div>
@endforeach
