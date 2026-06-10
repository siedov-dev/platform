@php
    $data = $bonus->freespinsConditions();
    $spins = array_keys($data);
    $deps = array_values($data);
    $info = \App\Models\DescriptionPage::byName('bonus.reg_bonus_descriptions_'.$bonus->getCurrencyName())
@endphp
<div class="popup-dialog fdep-popup no-click-hide">
    <div class="modal-params" data-show-next-popup="true"></div>
    <div class="detail-btn" onclick="this.parentElement.classList.add('details')">
        <img src="/img/popups/i.svg">
        <span class="ml-05">{{__('popups.details')}}</span>
    </div>
    <div class="description-wrapper no-click-hide">
        <div class="detail-btn" onclick="this.parentElement.parentElement.classList.remove('details')">
            <img src="/img/popups/i.svg" class="mr-05">
            {{__('popups.hide')}}
        </div>
        <div class="descr">
            {!! $info?->getPage() !!}
{{--            @include('phone.popups.fdep-descr.decr-'.mb_strtolower(\App\MUser::getAuth()->getCurrencyName()))--}}
        </div>
    </div>
    <img class="prize-img" src="/img/popups/fdep.png">
    <div class="inner-content">
        <div class="flex jc-center">
            <div class="congratz flex"><div style="transform: scaleX(-1)">🎉</div>&nbsp;{{__('popups.congratulations')}}&nbsp;<div>🎉</div></div>
        </div>
        <div class="title mt-05">{{__('popups.fdep_title')}}</div>
        <div class="title-gold">{{__('popups.fdep_title2')}}</div>
        <div class="message mt-1">{{__('popups.fdep_subtitle')}}</div>
        <div class="flex mt-1 jc-sa">
            @foreach(range(0, 2) as $k)
                <div class="fs-chip" onclick="fdepChipSelect(this, {{$deps[$k]}})">
                    <div class="flex">
{{--                        <img src="/img/popups/fs.svg">--}}
                        <div class="gold-text ml-05">{{intval($spins[$k])}} FS</div>
                    </div>
                    <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[$k]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
                </div>
            @endforeach
        </div>
        <div class="flex mt-1 jc-sa">
            <div class="fs-chip" onclick="fdepChipSelect(this, {{$deps[3]}})">
                <div class="flex">
{{--                    <img src="/img/popups/fs.svg">--}}
                    <div class="gold-text ml-05">{{intval($spins[3])}} FS</div>
                </div>
                <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[3]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
            </div>
            <div class="fs-chip max" onclick="fdepChipSelect(this, {{$deps[4]}})">
                <div class="top-text">{{__('popups.max_profit')}}</div>
                <div class="flex">
{{--                    <img src="/img/popups/fs.svg">--}}
                    <div class="gold-text ml-05">{{intval($spins[4])}} FS</div>
                </div>
                <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[4]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
            </div>
        </div>
        @if($bonus->isAgreementRequired())
            <div class="activate-bonus_and_show-bonuses btn-green mt-1"
                 data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}"
                 data-deposit="{{$deps[4]}}"
                 onclick="PopupModal.hide();">
                {{__('popups.get_bonus')}}
            </div>
        @elseif($bonus->isPaymentRequired())
            <div class="bonus-btn-main btn-green mt-1"
                 data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}"
                 data-deposit="{{$deps[4]}}"
                 onclick="wallet.showDeposit(parseFloat(this.dataset.deposit)); PopupModal.hide();">
                {{__('cabinet.deposit')}}
            </div>
        @endif
        <div class="popup-timeout link mt-1 mb-1 popup-later-btn" data-category="fdep" data-seconds="0" onclick="PopupModal.hide()">{{__('popups.activate_later')}}</div>
    </div>
</div>
