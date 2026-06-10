<div class="shop__item">
    <div class="shop__block {{$info->blockType()}}">
        <div class="shop__block-image wrap-lazy-load-image">
            <img class="desktop" src="/img/inventory/item_bg.png" alt="">
            <img class="mobile" src="/img/inventory/item_bg.png" alt="">
            <img class="glance" src="/img/inventory/glance.png" alt="">
            {!! $info->renderShopImage() !!}
            <a href="{{route('slc.user-item-info', ['item_id' => $item->getId()], false)}}"
               class="info-icon popup-link" data-popup-type="bottom-popup" data-ajax="modal"></a>
            <div class="shards-count">{{ $item->getAmount() }}/{{\App\Models\Slc\SlcConfig::getParams()->getKeyPiecesRequired($item->getType())}}</div>
        </div>
        <div class="shop__block-wrap-bottom my-bonuses">
            <p>{{$info->name()}}</p>
{{--            <p class="description">{{$info->getShortDescription()}}</p>--}}
            @if($item->getAmount() < \App\Models\Slc\SlcConfig::getParams()->getKeyPiecesRequired($item->getType()))
                <a href="" class="disabled" onclick="shortMessage.show('{{__('slc.not_enough_shards')}}'); return false;">{{__('slc.collect')}}</a>
            @else
                <a href="{{route('slc.activate-modal', ['item_id' => $item->getId()], false)}}" data-ajax="modal" data-method="POST" @if(\App\Helpers\Device::detect() !== \App\Helpers\Device::DESKTOP) data-popup-type="bottom-popup" @endif>
                    {{$info->getActionName()}}
                </a>
            @endif
            <p>
            <span>&nbsp;</span><br>&nbsp;
            </p>
        </div>
    </div>
</div>
