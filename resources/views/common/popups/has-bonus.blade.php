<div class="popup-dialog">
    <img src="/img/popups/has_bonus.png">
    <div class="inner-content">
        <div class="title">{{$title ?? ''}}</div>
        <div class="message mt-1">{{__('popups.has_bonus_msg')}}</div>
        <a class="popup-timeout btn-primary mt-1" href="{{route('cabinet.bonuses', [], false)}}" data-seconds="30">{{__('popups.take_bonus')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="has-bonus">{{__('popups.show_no_more')}}</div>
    </div>
</div>
