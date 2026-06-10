<div class="cashback-item-dialog pad">
    <div class="flex column jc-center ai-center">
        <div class="flex modal-title mt-2">{{__('slc.tournament-prize-position', ['place' => $place])}}</div>
        @if($amount > 0)
            <div class="flex ai-center mt-2" style="transform: scale(1.5)">
                <div class="currency-icon currency-icon_{{strtolower($currency)}}"></div>
                <div class="wallet-selector__amount">
                    <span class="wallet-selector__amount-int">{{ number_format(explode('.', $amount)[0], 0, '.', ',') }}.</span><span class="wallet-selector__amount-rest">{{ explode('.', number_format($amount, 2))[1]??'00' }}</span>
                </div>
            </div>
        @endif
        @if($win_item)
            <div class="flex column relative mt-1 popup-img-wrapper jc-center ai-center">
                    {!! $win_item->getInfo()->renderItemIcon() !!}
            </div>
            <div>{!! $win_item->getInfo()->name() !!}</div>
        @endif
        @if($can_activate)
            <form action="{{route('slc.activate-item')}}" method="POST">
                @csrf
                <input type="hidden" name="item_id" value="{{$item->getId()}}">
                <button type="submit" class="achievements__popup-button close-popup">{{__('slc.take_reward')}} </button>
            </form>
        @else
            <button class="achievements__popup-button button disabled" onclick="shortMessage.show('{{__("slc.verify-email-required")}}', 'error')">{{__('slc.take_reward')}}</button>
        @endif

    </div>
</div>
