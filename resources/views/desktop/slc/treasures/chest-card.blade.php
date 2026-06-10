@php
    $keys = $inventory->getItemsCount($key_type->getChestType()->getRequiredItem());
//    $next_item = $key_type->nextFreeKeyDate();
@endphp
<div class="chest-card {{$key_type->value}} no-click-hide">
    <div class="chest-info">
        <h3 class="mt-1 ml-1">{{__('chests.contains')}}</h3>
        <div class="chance-items mt-1">
            @foreach($key_type->getChestType()->getItems() as $slc_item)
                    <div class="scale-wrapper">
                        {!! $slc_item->getInfo()->renderItemIcon() !!}
                    </div>
                    <div>{!! $slc_item->getInfo()->name() !!}</div>
                    <div>
                        {{ ($slc_item->getAmount() * $slc_item->getMultiplier()) }}
                        @if($slc_item->getMaxAmount() > $slc_item->getAmount())
                            - {{ ($slc_item->getMaxAmount() * $slc_item->getMultiplier()) }}
                        @endif
                    </div>
                    <div>{!! $slc_item->getChance() !!}%</div>
            @endforeach
        </div>
    </div>
    <div class="flex jc-fe">
        <div class="flex">
            <div class="flex mr-05 ai-center key-img">
                <img src="/img/chests/{{$key_type->name}}.png">
            </div>
            <div class="flex column key-price">
                <div>{{__('chests.key_price')}}</div>
                <div>{{\App\Models\Slc\SlcShopItem::getItemPrice($key_type->getChestType()->getRequiredItem())}}
                    <img src='/images/svg/balans-icon.svg'>
                </div>
            </div>
        </div>
    </div>
    <div class="flex jc-sb mt-1 overflow-hidden">
        <div class="chest-img">
            <img src="/img/chests/{{$key_type->getChestType()->name}}.png">
        </div>
        <div class="items">
            @foreach($key_type->getChestType()->getGroupedItems() as $slc_item)
                {!! $slc_item->getInfo()->renderRangeItemIcon($slc_item->amount * $slc_item->multiplier, $slc_item->max_amount * $slc_item->multiplier) !!}
            @endforeach
        </div>
        <div class="flex jc-center ai-center chest-info-btn">
            <img src="/img/chests/chest_info.png" onclick="this.closest('.chest-card').classList.toggle('info-shown')">
        </div>
    </div>
    <h3 class="mt-2">
        {{__('chests.'.$key_type->getChestType()->name)}}
    </h3>
    <p class="short-info mt-1">
        {{__('chests.'.$key_type->getChestType()->name.'_short-info')}}
    </p>

    <div class="bottom-info">
        <div class="flex jc-sb">
            <div class="flex column">

            </div>
            <a class="key-{{$key_type->value}} button button_orange @if($keys == 0) disabled @endif"
               href="{{route('slc.open-chest', ['key' => $key_type->value], false)}}" data-ajax="modal">
                {{__('chests.open')}}
            </a>
        </div>
        <div class="flex jc-fe keys-amount mt-05 key-{{$key_type->value}}">
            <span>{{__('chests.you_have')}}&nbsp;{{$keys}}</span>
            <img src='/img/chests/{{$key_type->name}}.png'>
            <span>{{__('chests.keys')}}</span>
        </div>
    </div>
</div>
