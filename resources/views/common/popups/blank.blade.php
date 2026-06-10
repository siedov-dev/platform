<div class="popup-dialog">
    @if (!empty($img))
        <img src="{{$img}}">
    @endif
    <div class="inner-content">
        <div class="title">{!! $title ?? '' !!}</div>
        <div class="message mt-1">{!! $msg ?? '' !!}</div>
        <a class="popup-timeout btn-gold mt-1" href="{{$route}}" data-seconds="30">{{$button_text?? __('popups.take_bonus')}}</a>
        <div class="popup-unsub_click unsub mt-1 mb-1" data-category="{{$category}}">{{__('popups.show_no_more')}}</div>
    </div>
</div>
