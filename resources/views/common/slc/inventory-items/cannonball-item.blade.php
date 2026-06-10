<div class="shop__item">
    <div class="shop__block">
        <div class="shop__block-image wrap-lazy-load-image">
            <img class="desktop" src="/img/inventory/item_bg.png" alt="">
            <img class="mobile" src="/img/inventory/item_bg.png" alt="">
            <img class="glance" src="/img/inventory/glance.png" alt="">
            {!! $info->renderShopImage() !!}
            <a href="{{route('slc.user-item-info', ['item_id' => $item->getId()], false)}}"
               class="info-icon popup-link" data-popup-type="bottom-popup" data-ajax="modal"></a>
            @if($info->isGroupable())
                <div class="shards-count">x{{ \App\Models\Slc\SlcUserInventory::forUser(\App\MUser::getAuth())->getItemsCount($item->getType()) }}</div>
            @else
                <div class="shards-count">x{{ $item->getAmount() }}</div>
            @endif
        </div>
        <div class="shop__block-wrap-bottom my-bonuses">
            <p>{{$info->name(false)}}</p>
            <a href="{{route('slc.cashback', [], false)}}" data-ajax="modal" data-popup-type="bottom-popup">{{__('cashback-plus.shoot')}}</a>
            @if(!$info->isGroupable())
                <p>
                    @if($info->isExpirable())
                        <span>{{__('slc.utilize_to')}}:</span><br> {{$item->getActiveTo()->format('H:i, d.m.Y')}}
                    @else
                        <span>&nbsp;</span><br>&nbsp;
                    @endif
                </p>
            @endif
        </div>
    </div>
</div>
