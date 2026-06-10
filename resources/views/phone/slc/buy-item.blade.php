@php
    /** @var \App\Models\Slc\SlcItem $item */
    $price = $item->getPrice();
    $balance = \App\MUser::getAuth()->getSlcBalance();
    $info = $item->getInfo();
@endphp
<div class="modal-block slc-item-buy no-click-hide">
    <h3 class="modal-title mt-2 mb-2 flex jc-center text-white">{{__('slc.confirm_buy_item')}}</h3>
    {!! $info->renderItemIcon() !!}
    <div class="flex jc-center mt-1 mb-1 text-white">{{$info->name()}}</div>
    <form action="{{route('slc.buy-item', [], false)}}" method="POST" data-ajax="#layout-content">
        @csrf
        <input type="hidden" name="item_id" value="{{$item->getId()}}">
        @if($info->isStackable() || $info->isGroupable())
            <div class="flex jc-center">
                <div class="page__head-block-wrap-price">
                    <div class="page__head-wrap-price">
                        <img src="/images/svg/balans-icon.svg" alt="">
                        {!! \App\Helpers\UI::renderSlcMoney($price) !!}
                    </div>
                </div>
            </div>
            <div class="flex jc-sb p-1 amount-controls">
                <button type="button" class="button button_red mr-1">
                    <span class="text-white text-largest">-</span>
                </button>
                <input class="input__field_modal" type="text" name="amount" value="1" min="1" size="3" step="1" oninput="this.value = this.value.replaceAll(/\D/ig, '')">
                <button type="button" class="button button_green ml-1">
                    <span class="text-white text-largest">+</span>
                </button>
            </div>
        @endif
        <div class="flex jc-sb">
            <button type="submit" class="button button_green @if($price > $balance) disabled @endif"
                    onclick="if(this.classList.contains('disabled')) {shortMessage.show('{{__("slc.not_enough_slc")}}', 'error'); return false;} Modal.hide(this);">
                <span class="mr-05">{{__('slc.buy')}}</span>
                <img src="/images/svg/cart-icon.svg" alt="">
            </button>
            <button type="button" class="button button_transparent" onclick="Modal.hide(this)">{{__('cabinet.cancel')}}</button>
        </div>
    </form>
</div>
@if($info->isStackable() || $info->isGroupable())
    <script>
        (() => {
            const price = {{$price}};
            const balance = {{$balance}};
            const priceEl = document.querySelector('.modal-block.slc-item-buy .page__head-wrap-price p').firstChild;
            const minusButton = document.querySelector('.modal-block.slc-item-buy .amount-controls button:first-child');
            const plusButton = document.querySelector('.modal-block.slc-item-buy .amount-controls button:last-child');
            const amountInput = document.querySelector('.modal-block.slc-item-buy .amount-controls input');
            const buyBtn  =  document.querySelector('.modal-block.slc-item-buy button[type="submit"]');

            minusButton.addEventListener('click', (e) => {
                if (amountInput.value > 1) amountInput.value--;
                priceEl.textContent = (amountInput.value * price).toString();
                if ((amountInput.value * price) > balance) {
                    buyBtn.classList.add("disabled");
                    priceEl.parentElement.closest('.page__head-wrap-price').classList.add('lack-amount')
                } else {
                    buyBtn.classList.remove("disabled");
                    priceEl.parentElement.closest('.page__head-wrap-price').classList.remove('lack-amount')
                }
            });

            plusButton.addEventListener('click', (e) => {
                amountInput.value++;
                priceEl.textContent = (amountInput.value * price).toString();
                if ((amountInput.value * price) > balance) {
                    buyBtn.classList.add("disabled");
                    priceEl.parentElement.closest('.page__head-wrap-price').classList.add('lack-amount')
                } else {
                    buyBtn.classList.remove("disabled");
                    priceEl.parentElement.closest('.page__head-wrap-price').classList.remove('lack-amount')
                }
            });
        })()
    </script>
@endif
