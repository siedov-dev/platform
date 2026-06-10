<div class="rank-card rank-{{$rank_level}} reqired-modal">
    <img class="rank-bg"  src="/img/ui/ranks/rank-bg.png" alt="bg">
    <div class="rank-info">
        <img class="rank-icon" src="/img/ui/ranks/rank-{{$rank_level}}.png" alt="rank">
        <div class="rank-required">{{$title}}</div>
        <div class="rank-name">{{__('ranks.rank_'.$rank_level)}}</div>
        <p class="goal-title">{{__('ranks.bets')}}</p>
        <div class="goal-values"><span class="current-value">{{__('ranks.from')}} {{$rank->getStartValue()}} {{$user->getCurrencyName()}}</span></div>

        <div class="achievements__popup-progress-bar mt-05 mb-2">
            <div class="achievements__popup-progress" style="width: {{$rank->getNextRankProgress($user->getRankValue($currency), $currency)}}%"></div>
        </div>
        <a class="menu__item hidden-menu__first-link achievements content-link w-100" href="{{route('slc.ranks', [], false)}}" onclick="wallet.hide()">
            <span class="wrap-img"><img src="/images/achievements-icon.png" alt=""></span>
            {{__('menu.achievements')}}
        </a>
    </div>
</div>
