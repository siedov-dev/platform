<div class="cashback-item-dialog pad">
    <div class="flex column jc-center ai-center">
        <div class="flex modal-title mt-1">{{__('cashback-plus.dialog-title')}}</div>
        <div class="flex column relative mt-2 popup-img-wrapper jc-center ai-center">
            <img src="/img/cashback/Barrel.svg">
            <div class="absolute-bc flex jc-center text-implication mb-1">+{{$current_value}}%</div>
        </div>
        @if($current_value < 100)
            <form action="{{route('slc.activate-item')}}" method="POST">
                @csrf
                <input type="hidden" name="item_id" value="{{$item->getId()}}">
                <button type="submit" class="achievements__popup-button close-popup">{{__('slc.activate')}} +{{$item->getamount()}}%</button>
            </form>
        @else

            <button class="achievements__popup-button button disabled" onclick="shortMessage.show('{{__("cashback-plus.max_reached_error")}}', 'error')">{{__('cashback-plus.max_reached')}}</button>
        @endif
    </div>
</div>
