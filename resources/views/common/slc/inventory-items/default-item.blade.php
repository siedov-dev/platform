@if($item->isActive())
    <div class="shop__item">
        <div class="shop__block {{$info->blockType()}}">
            <div class="shop__block-image wrap-lazy-load-image">
                <img class="desktop" src="/img/inventory/item_bg.png" alt="">
                <img class="mobile" src="/img/inventory/item_bg.png" alt="">
                <img class="glance" src="/img/inventory/glance.png" alt="">
                {!! $info->renderShopImage() !!}
                <a href="{{route('slc.user-item-info', ['item_id' => $item->getId()], false)}}"
                   class="info-icon popup-link" data-popup-type="bottom-popup" data-ajax="modal"></a>
                @if($info->isStackable())
                    <div class="shards-count">x{{ $item->getAmount() }}}}</div>
                @endif
            </div>
            <div class="shop__block-wrap-bottom my-bonuses">
                <p>{{$info->name()}}</p>
{{--                <p class="description">{{$info->getShortDescription()}}</p>--}}
                @if($item->getActivator()->requiresDialog($item))
                    <a href="{{route('slc.activate-modal', ['item_id' => $item->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup" data-method="POST">{{$info->getActionName()}}</a>
                @else
                    <a @if($info->canActivate()) href="{{route('slc.activate-item', ['item_id' => $item->getId()], false)}}" @else  class="disabled" @endif>
                        {{$info->getActionName()}}
                    </a>
                @endif
                <div class="flex estimated wrap jc-center">
                    @if($info->isExpirable())
                        <div>{{__('slc.estimated_in')}}:&nbsp;</div><div class="time-estimated" data-target_time="{{$item->getActiveTo()}}"></div>
                    @else
                        <span>&nbsp;</span><br>&nbsp;
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
