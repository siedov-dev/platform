@php
/** @var \App\Models\Slc\SlcUserShopItem $item */
@endphp
<div class="modal-params"></div>
<div class="chest__dialog pad no-click-hide">
    <h2 class="flex mt-2 jc-center">{{__('chests.congratz')}}</h2>
    <div class="chest__items invisible">
        <div class="flex jc-center wrap" style="position: relative">
            @foreach($win_items as $item)
                <div class="shine-item">
                    <img class="shine-img" src="/img/chests/item_light2.png">
                    {!! $item->getInfo()->renderItemIcon() !!}
                </div>
            @endforeach
        </div>
    </div>
    <div class="flex jc-center wrap animation-wrap">
        <lottie-player
            autoplay
            mode="normal"
            src="/img/chests/anims/{{$chest_type->name}}.json"
            class="chest-animation"
        >
        </lottie-player>
    </div>
        <div class="flex jc-center open-next">
            @if($keys != 0)
                <a class="button button_orange" onclick="PopupModal.hide();"
                   href="{{route('slc.open-chest', ['key' => $key_type->value], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
                    {{__('chests.open-next')}}
                </a>
            @endif
            <a class="content-link button button_shop button_left-icon ml-2" onclick="Modal.hide(this.closest('.modal'));" href="{{route('slc.user-items', [], false)}}">
              <img src="/images/shop-icon.png" alt="">{{__('menu.inventory')}}
            </a>
        </div>
</div>
<script>
    (() => {
        setTimeout(() => {
            let animChest = document.querySelector('.chest__dialog .chest-animation');
            animChest.addEventListener('complete', (e) => {
                e.target.closest('.chest__dialog').querySelector('.chest__items').classList.remove('invisible');
                setTimeout(() => {
                    document.querySelector('.chest__dialog .chest__items').classList.add('back-blur');
                }, 200);
            });
        }, 1000);
    })()
</script>
