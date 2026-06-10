<div class="pad">
    <h3 class="modal-title mt-3 mb-2 flex jc-center text-white">{{__('slc.bezdep_required')}}</h3>
    <div class="flex jc-center column">
        <div class="flex jc-center ai-center">
            <a href="{{route('slc.achievement-popup', ['achievement_id' => $achievement->getId()], false)}}" class="popup-link" data-ajax="modal" data-popup-type="bottom-popup">
                <img src="{{$achievement->getDisabledImage()}}" alt="" width="100">
            </a>
        </div>
        <div class="slc__achievement-name mt-1">{{$achievement->getTitle()}}</div>
    </div>
    <div class="flex jc-center ai-center mt-1">
        <a class="content-link button button_shop button_left-icon" href="{{route('slc.achievements', [], false)}}">
            <img src="/images/achievements-icon.png" alt="">{{__('menu.achievements')}}
        </a>
    </div>
    @if(isset($item))
        <div class="timeout mt-1 rewards">
            <div>
                <div class="flex jc-center ai-center pt-1">
                    <img src="/img/icons/time.svg" class="mr-05"> {{__('bonus.wait-bezdep-item')}}
                </div>
                <div id="bezdep-timeout" class="time update-timer-block pb-1" data-target_time="{{$item->created_at->addDay()}}" data-hours-only="true" data-timeout-event="true">
                    <div><span class="timer-hours-left">-</span>h</div>&nbsp;:&nbsp;
                    <div><span class="timer-minutes-left">-</span>m</div>
                </div>
            </div>
        </div>
    @endif
</div>
<script>
    (() => {
        const bezdepTimeout = document.getElementById('bezdep-timeout');
        bezdepTimeout.addEventListener('timer-timeout', () => {
            document.dispatchEvent(new CustomEvent('inventory:item-changed'));
            Modal.hide(bezdepTimeout);
        });
    })()
</script>
