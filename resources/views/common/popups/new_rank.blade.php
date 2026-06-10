<div class="popup-dialog">
    <div class="rank-card rank-{{$rank_level}}">
        <div class="current-rank">{{__('ranks.yourRank')}}</div>
        <img class="rank-bg"  src="/img/ui/ranks/rank-bg.png" alt="bg">
        <div class="rank-info">
            <img class="rank-icon" src="/img/ui/ranks/rank-{{$rank_level}}.png" alt="rank">
            <div class="rank-name">{{__('ranks.rank_'.$rank_level)}}</div>
        </div>
    </div>
    <div class="inner-content">
        <div class="message mt-1">{{__('ranks.got_new_rank')}}</div>
        <a class="popup-timeout btn-green mt-1" href="{{route('slc.ranks', [], false)}}" data-seconds="10">{{__('ranks.get_rewards')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="ranks">{{__('popups.show_no_more')}}</div>
    </div>
</div>
