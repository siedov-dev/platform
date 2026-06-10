<div class="modal-params" data-refresh_on_close></div>
<div class="pad">
    <h3 class="modal-title mt-3 mb-2 flex jc-center text-white">{{__('slc.key_congratz')}}</h3>
    {!! $key->getInfo()->renderItemIcon() !!}
    <div class="flex jc-center mt-1 mb-1 text-white">{{$key->getInfo()->name()}}</div>
    <div class="flex jc-center ai-center">
        <div class="btn-lost-treasures hidden-menu__first-block-price">
            <a href="{{route('slc.treasures', [], false)}}" class="content-link hide-modals">
                <img src="/img/chests/lost_treasures_btn.png">
            </a>
        </div>
    </div>
</div>
