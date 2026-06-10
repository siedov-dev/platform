<div class="popup achievements__popup my-task-popup">
    <div class="popup__body">
        <div class="popup__content">
            <form action="{{route('slc.reward-modal', [], false)}}" method="POST">
                @csrf
                <input type="hidden" name="daily_task_id" value="{{$daily_task->getid()}}">
                <h2>{{__('slc.task_completed')}}</h2>
                <div class="achievements__popup-block-icon">
                    @foreach($rewards as $reward)
                        @if($reward->getType() === \App\Models\Slc\SlcItemType::Slc && $rewards->count() > 1) @continue @endif
                        {!! $reward->getInfo()->renderItemIcon()  !!}
                    @endforeach
                </div>
                @foreach($rewards as $reward)
                    @if($reward->getType() === \App\Models\Slc\SlcItemType::Slc) @continue @endif
                    <p class="achievements__popup-price">{{$reward->getInfo()->name()}}</p>
                @endforeach
                <button type="submit" class="achievements__popup-button close-popup">{{__('slc.get_reward')}}</button>
                <div class="popup__close close-popup" onclick="Modal.hide(this.closest('.modal'))">
                    <img src="/images/svg/close-pop-up.svg" alt="" class="dekstop">
                    <img src="/images/svg/close-pop-up-mobile.svg" alt="" class="mobile">
                </div>
                <div class="achievements__popup-block-image">
                    <img class="achievements__popup-bg desktop" src="/images/my-task-popup-content-bg.jpg" alt="">
                    <img class="achievements__popup-bg mobile" src="/images/my-task-popup-content-bg-mobile.jpg" alt="">
                </div>
            </form>
        </div>
    </div>
</div>
