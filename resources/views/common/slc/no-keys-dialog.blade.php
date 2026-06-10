<div class="pad">
    <h3 class="modal-title mt-1 mb-2 flex jc-center text-white">{{__('slc.no-key')}}</h3>
    {!! $item->getInfo()->renderItemIcon() !!}
    <div class="flex jc-center mt-1 mb-1 text-white">{{$item->getInfo()->name()}}</div>
    <div class="flex jc-sb">
        <a class="content-link button button_shop button_left-icon" onclick="Modal.hide(this.closest('.modal'));" href="{{route('slc.shop', [], false)}}">
            <img src="/images/shop-icon.png" alt="">{{__('menu.shop')}}
        </a>
        <button type="reset" class="button button_transparent" onclick="Modal.hide(this.closest('.modal'))">{{__('cabinet.cancel')}}</button>
    </div>
</div>
