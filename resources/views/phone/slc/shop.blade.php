@php
/** @var \App\Models\Slc\SlcShopItem $item */
@endphp
<div class="page__head slc-shop">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing')}}">{{__('slc.back_to_main')}}</a>
        </div>
        <div class="page__head-wrap-title">
            <h1 class="page__title flex">
                {{__('slc.shop')}}
                <a href="{{route('slc.page-info', ['page' => 'shop'], false)}}" class="page-info-icon ml-05" data-ajax="modal" data-modal-class="mobile-popup"></a>
            </h1>
            <div class="page__head-block-wrap-price">
                <p>{{__('wallet.balance')}}</p>
                <div class="page__head-wrap-price">
                    <img src="/images/svg/slc-icon.svg" alt="" width="40px">
                    {!! \App\Helpers\UI::renderSlcMoney(\App\MUser::getAuth()->getSlcBalance()) !!}
                </div>
            </div>
        </div>
        <div class="flex mt-1">
            <div class="tabs grow">
                <label class="tabs__tab @if(!$show_exchange) tabs__tab_active @endif" data-target="#shop-tab">
                    {{ __('slc.buy') }}
                </label>
                <label class="tabs__tab @if($show_exchange) tabs__tab_active @endif" data-target="#exchange-tab">
                    {{ __('slc.exchange') }}
                </label>
            </div>
        </div>
    </div>
</div>
<main class="main">
    <div class="page__content">
        <div class="n_container">
            <div id="shop-tab" class="tabContent" @if($show_exchange) style="display: none;" @endif>
                <div class="page-ad-text">{{__('slc.shop-ad-text')}}</div>
                <div class="shop__items">
                    @foreach($items as $item)
                        @php
                            $info = $item->getInfo();
                        @endphp
                        <div class="shop__item">
                            <div class="shop__block {{$info->blockType()}}">
                                <div class="shop__block-image wrap-lazy-load-image">
                                    <img class="desktop" src="/img/inventory/shop_item_bg.png" alt="">
                                    <img class="mobile" src="/img/inventory/shop_item_bg.png" alt="">
                                    <img class="glance" src="/img/inventory/glance.png" alt="">
                                    {!! $info->renderShopImage() !!}
                                    <a href="{{route('slc.item-info', ['item_id' => $item->getId()], false)}}" class="info-icon popup-link" data-popup-type="bottom-popup" data-ajax="modal"></a>
                                </div>
                                <div class="shop__block-wrap-bottom">
                                    <p>{{$info->name()}}</p>
                                    <div class="shop__block-bottom">
                                        <div class="wrap-price">
                                            <img src="/images/svg/balans-icon.svg" alt="">
                                            <p>{{$item->getPrice()}}</p>
                                        </div>
                                        @if($item->getPrice() > \App\MUser::getAuth()->getSlcBalance())
                                            <button class="shop__block-button disabled" onclick="shortMessage.show('{{__("slc.not_enough_slc")}}', 'error')">
                                                <img src="/images/svg/cart-icon.svg" alt="">
                                            </button>
                                        @else
                                            <a href="{{route('slc.buy-item-modal', ['item_id' => $item->getId()], false)}}" class="shop__block-button" data-ajax="modal" data-popup-type="bottom-popup">
                                                <img src="/images/svg/cart-icon.svg" alt="">
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>`
            </div>

            <div id="exchange-tab" class="tabContent" @if(!$show_exchange) style="display: none;" @endif>
                @include('phone.slc.exchange')
            </div>
        </div>
    </div>
</main>
@include('phone.blocks.bottom-deposit-btn')
