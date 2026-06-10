<div class="popup-dialog">
    <img src="{{$img}}">
    <div class="inner-content">
        <div class="title">{!! $title !!}</div>
        <div class="message mt-1">{!! $msg !!}</div>
        <a class="popup-timeout {{$btn_type?? "btn-gold"}} mt-1" href="{{$url}}" data-seconds="30">{{$btn_text}}</a>
        @if($unsub)
            <div class="popup-unsub_click unsub mt-1 mb-1" data-category="{{$category}}">{{__('popups.show_no_more')}}</div>
        @endif
    </div>
</div>
