<div class="popup-dialog">
    <div class="flex jc-center mt-1 mb-1">
        <div class="congratz flex"><div style="transform: scaleX(-1)">🎉</div>&nbsp;{{__('popups.congratulations')}}&nbsp;<div>🎉</div></div>
    </div>
    <div class="flex column" style='background-image: url("/img/inventory/glance.png"); background-position: center; background-size: contain; background-repeat: no-repeat;'>
        {!! $info->renderItemIcon() !!}
        <div class="mt-1">{{ $info->name() }}</div>
    </div>
    <div class="inner-content">
        <div class="title">{{__('slc.tasks_completed')}}</div>
        <div class="message mt-1">{{__('slc.tasks_completed_text')}}</div>
        <a class="popup-timeout btn-gold mt-1" href="{{route('slc.tasks', [], false)}}" data-seconds="30">{{__('slc.to_tasks')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="tasks">{{__('popups.show_no_more')}}</div>
    </div>
</div>
