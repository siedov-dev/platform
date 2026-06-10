<div class="shop__item">
    <div class="shop__block">
        <div class="shop__block-image wrap-lazy-load-image">
            <img class="desktop" src="/img/inventory/item_bg.png" alt="">
            <img class="mobile" src="/img/inventory/item_bg.png" alt="">
            <img class="glance" src="/img/inventory/glance.png" alt="">
            {!! $info->renderShopImage() !!}
            <a href="{{route('slc.user-item-info', ['item_id' => $item->getId()], false)}}"
               class="info-icon popup-link" data-popup-type="bottom-popup" data-ajax="modal"></a>
            <div class="shards-count">x{{ $item->getAmount() }}</div>
        </div>
        <div class="shop__block-wrap-bottom my-bonuses">
            <p>{{$info->name()}}</p>
{{--            <p class="description">{{$info->getShortDescription()}}</p>--}}
            <a class="content-link" href="{{route('slc.treasures', [], false)}}">{{__('chests.open')}}</a>
            <p>
                @if($info->isExpirable())
                    <span>{{__('slc.utilize_to')}}:</span><br> {{$item->getActiveTo()->format('H:i, d.m.Y')}}
                @else
                    <span>&nbsp;</span><br>&nbsp;
                @endif
            </p>
        </div>
    </div>
</div>
