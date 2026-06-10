<div class="popup-dialog">
    <img src="{{$img}}">
    <div class="inner-content">
        <div class="title">{{$name ?? ''}}</div>
        <div class="message mt-1">{{$msg ?? ''}}</div>
        <a class="popup-timeout btn-gold mt-1" href="{{route('tournaments', [], false)}}" data-seconds="20">{{__('popups.play_tournament')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="tournament">{{__('popups.show_no_more')}}</div>
    </div>
</div>
