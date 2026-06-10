<div class="popup-dialog">
{{--    <img src="/img/popups/has_bonus.png">--}}
    <img src="/img/popups/birthdayb.png">
    <div class="inner-content">
        <div class="title">{{__('slc.bezdep_bonus')}}</div>
        <div class="message mt-1">{{__('slc.reg_popup_text')}}</div>
        <a class="popup-timeout btn-gold mt-1" href="{{route('slc.tasks', [], false)}}" data-seconds="10">{{__('slc.to_tasks')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="tasks">{{__('popups.show_no_more')}}</div>
    </div>
</div>
