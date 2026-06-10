@php
/** @var \App\Models\Slc\SlcCurrencyExchange $item */
@endphp
<div class="slc__exchange-items">
    <div class="exchange-ad-banner" style="--img: url('/img/slc/exchange_plate_m.png');">
        <p>{{__('slc.exchange-ad-t1')}}</p>
        <p>{{__('slc.exchange-ad-t2')}}</p>
        <p>{{__('slc.exchange-ad-t3')}}</p>
    </div>
    <div class="exchange-ad-text">{{__('slc.exchange-ad-text')}}</div>
    @foreach($exchange_items as $idx => $item)
        @if($is_exchange_blocked)
            <a class="exchange-item" href="{{route('slc.exchange.modal', ['item_id' => $item->getId()], false)}}"
               onclick="shortMessage.show('{{__("slc.exchange_blocked")}}', 'error'); return false;">
        @elseif($item->getSlcAmount() > \App\MUser::getAuth()->getSlcBalance())
            <a class="exchange-item" href="{{route('slc.exchange.modal', ['item_id' => $item->getId()], false)}}"
               onclick="shortMessage.show('{{__("slc.not_enough_slc")}}', 'error'); return false;">
        @else
            <a class="exchange-item" href="{{route('slc.exchange.modal', ['item_id' => $item->getId()], false)}}"
               data-ajax="modal" data-popup-type="bottom-popup">
        @endif
            <div class="flex jc-sb">
                <div class="icon-wrapper">
                    <img src="{{$item->getImg()}}" alt="">
                </div>
                <div class="flex column grow jc-sa pl-05 pr-05">
                    <div class="title">{{$item->getTitle()}}</div>
                    <div class="amount">
                        {{intval($item->getCurrencyAmount())}} {{\App\UserWallet::currencySymbol(\App\UserWallet::getCurrencyCode($item->getCurrency()))}}
                        @if($item->getPrc() > 0)
                            <span class="prc-{{$idx}}">+{{$item->getPrc()}}%</span>
                        @endif
                    </div>
                </div>
                <div class="flex page__head-wrap-price @if($item->getSlcAmount() > \App\MUser::getAuth()->getSlcBalance()) disabled @endif">
                    <img src="/images/svg/balans-icon.svg" alt="">
                    {!! \App\Helpers\UI::renderSlcMoney($item->getSlcAmount(), false) !!}
                </div>
            </div>
        </a>
    @endforeach
</div>
