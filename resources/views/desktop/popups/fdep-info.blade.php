@php
    $data = $bonus->freespinsConditions();
    $spins = array_keys($data);
    $deps = array_values($data);
    $info = \App\Models\DescriptionPage::byName('bonus.reg_bonus_descriptions_'.$bonus->getCurrencyName())
@endphp
<div class="popup-dialog popup-desktop fdep-popup no-click-hide">
    <div class="flex">
        <div class="description-wrapper no-click-hide flex column">
            <div class="descr inner-content">
                {!! $info?->getPage() !!}
{{--                @include('desktop.popups.fdep-descr.decr-'.mb_strtolower(\App\MUser::getAuth()->getCurrencyName()))--}}
            </div>
            <div class="flex mb-2 ml-3">
                <div class="link" onclick="this.closest('.popup-dialog').classList.remove('details')">
                    {{__('popups.hide')}}
                </div>
            </div>
        </div>
        <div class="inner-content mt-2">
            <div class="flex jc-center">
                <div class="congratz flex"><div style="transform: scaleX(-1)">🎉</div>&nbsp;{{__('popups.congratulations')}}&nbsp;<div>🎉</div></div>
            </div>
            <div style="padding: 0 1rem">
                <div class="title mt-05">{{__('popups.fdep_title')}}</div>
                <div class="title-gold">{{__('popups.fdep_title2')}}</div>
                <div class="message mt-1">{{__('popups.fdep_subtitle')}}</div>
            </div>
            <div class="flex mt-1 jc-sa">
                @foreach(range(0, 2) as $k)
                    <div class="fs-chip" onclick="fdepChipSelect(this, {{$deps[$k]}})">
                        <div class="flex">
{{--                            <img src="/img/popups/fs.svg">--}}
                            <div class="gold-text ml-05">{{intval($spins[$k])}} FS</div>
                        </div>
                        <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[$k]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
                    </div>
                @endforeach
            </div>
            <div class="flex mt-1 jc-sa">
                <div class="fs-chip" onclick="fdepChipSelect(this, {{$deps[3]}})">
                    <div class="flex">
{{--                        <img src="/img/popups/fs.svg">--}}
                        <div class="gold-text ml-05">{{intval($spins[3])}} FS</div>
                    </div>
                    <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[3]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
                </div>
                <div class="fs-chip max" onclick="fdepChipSelect(this, {{$deps[4]}})">
                    <div class="top-text">{{__('popups.max_profit')}}</div>
                    <div class="flex">
{{--                        <img src="/img/popups/fs.svg">--}}
                        <div class="gold-text ml-05">{{intval($spins[4])}} FS</div>
                    </div>
                    <div class="fs-cond">{{__('popups.from')}} {{ \App\Helpers\UI::moneyStr($deps[4]) }} {{\App\UserWallet::currencySymbol($bonus->getCurrency())}}</div>
                </div>
            </div>
            <div class="flex jc-sa ai-center mt-1 mb-1">
                <div class="link mt-1 mb-1" onclick="this.closest('.popup-dialog').classList.add('details')">{{__('popups.details')}}</div>
                @if($bonus->isAgreementRequired())
                    <div class="bonus-btn-wrap" onclick="Modal.hide(this)">
                        <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false)}}"
                             data-deposit="{{$deps[4]}}"
                             class="cabinet__bonus-menu-item bonus-btn-main">{{__('cabinet.get')}}</div>
                    </div>
                @elseif($bonus->isPaymentRequired())
                    <div class="bonus-btn-wrap" onclick="Modal.hide(this)">
                        <div class="bonus-btn-main" data-deposit="0" onclick='document.dispatchEvent(new CustomEvent("showDeposit", {detail: {deposit: this.getAttribute("data-deposit")} }))'>{{__('cabinet.deposit')}}</div>
                    </div>
                @elseif($bonus->isActive() && !$bonus->noPlayButton())
                    @if($bonus->isDirectPlayUrl())
                        <div>
                            <div class="bonus-btn-wrap">
                                <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
                                <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" style="display: none;"></div>
                                <div class="bonus-btn-main" onclick="this.parentElement.querySelector('.cabinet__bonus-play-btn').click();Overlay.hide();">{{__('games.play')}}</div>
                            </div>

                        </div>
                    @else
                        <div class="bonus-btn-wrap" onclick="Modal.hide(this)">
                            <a class="bonus-btn-main" href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus">{{__('bonus.play')}}</a>
                        </div>
                    @endif
                @else
                    <div></div>
                @endif
            </div>
        </div>
        <div class="fdep-img">
            <img class="" src="/img/popups/fdep-d.png">
        </div>
    </div>
</div>
