@php
/** @var \App\Models\Slc\SlcCurrencyExchange $item */
$price = $item->getSlcAmount();
$balance = \App\MUser::getAuth()->getSlcBalance();
@endphp
<div class="modal-block slc-item-buy no-click-hide">
    <h3 class="modal-title mt-2 mb-2 flex jc-center text-white">{{__('slc.confirm_exchange')}}</h3>
    <div class="exchange-item">
        <div class="flex jc-sb">
            <div class="icon-wrapper">
                <img src="{{$item->getImg()}}" alt="">
            </div>
            <div class="flex column grow p-1 jc-sa">
                <div class="title">{{$item->getTitle()}}</div>
                <div class="amount">
                    {{intval($item->getCurrencyAmount())}} {{\App\UserWallet::currencySymbol(\App\UserWallet::getCurrencyCode($item->getCurrency()))}}
                    @if($item->getPrc() > 0)
                        <span class="prc-1">+{{$item->getPrc()}}%</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="flex jc-center mt-1 mb-1 text-white">{{__('slc.exchange_receive')}} {{intval($item->getCurrencyAmount())}} {{\App\UserWallet::currencySymbol(\App\UserWallet::getCurrencyCode($item->getCurrency()))}}</div>
    <form action="{{route('slc.exchange.modal', [], false)}}" method="POST">
        @csrf
        <input type="hidden" name="item_id" value="{{$item->getId()}}">
        <div class="flex jc-sb">
            <button type="submit" class="button button_green @if($price > $balance) disabled @endif"
            onclick="if(this.classList.contains('disabled')) {shortMessage.show('{{__("slc.not_enough_slc")}}', 'error'); return false;}">
                <span class="mr-05">{{__('slc.exchange')}}</span>
                <img src="/images/svg/cart-icon.svg" alt="">
            </button>
            <button type="reset" class="button button_transparent" onclick="Modal.hide(this.closest('.modal'))">{{__('cabinet.cancel')}}</button>
        </div>
    </form>
</div>
