@php
    /** @var $bonus \App\Models\Bonuses\UserBonus */
        $active = $bonus->isCancelable();
        /** @var \App\Models\BonusPatterns $pattern */
        $pattern = \App\Models\BonusPatterns::find($bonus->pattern_id);
        $dep_n = 1;
        preg_match('#(\d)#', $pattern->name, $match);
        $dep_n = $match[1];
        $dep_mult = $bonus->getJsonParam('multiplier');
        $wager = $bonus->getJsonParam('wager');
        $wallet = new \App\GamesProviders\WalletGameWalletAdapter(\App\MUser::getAuth()->getWallet($bonus->getCurrency()));
        $currency = $bonus->getCurrencyName(); //название валюты RUB
        $symbol = \App\UserWallet::currencySymbol($bonus->currency);// символ валюты
@endphp
<div class="cabinet__bonuses-wrapper no-click-hide">
    <div class="cabinet__bonuses-item cabinet__bonus welcome-bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
         data-id="{{ $bonus->getId() }}"
         style="--bgImg: url('/img/bonuses/wb/{{$bonus->curr_name}}-{{$bonus->pattern_id}}.png')">

        <div class="cabinet__bonus-content">
            <div class="cabinet__bonus-goal">{{ __('bonus.stake') }} {{ $pattern->getBonusParam('fs_bet', $currency) }} {{ $symbol }}
                , {{ __('bonus.wager') }} x{{$wager}}</div>

        @if($bonus->isActive())
                <div>
                    @if($bonus->getSpinsAvailable() > 0)
                        <div class="cabinet__bonus-balance">
                            <img src="/img/bonuses/fs.svg" alt="fs">
                            <div class="ml-05">{{$bonus->getSpinsAvailable()}}</div>
                        </div>
                    @else
                        <div class="cabinet__bonus-balance">
                            <div>{{\App\Helpers\UI::moneyStr($bonus->getBalanceCents() / 100, " ")}}
                                <span
                                    class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                            </div>
                        </div>
                    @endif
                        @if($bonus->getSpinsAvailable() > 0)
                            <div>
                                <div class="bonuses__item-progress"
                                     style="--progress: {{$bonus->getProgressPercents()}}%">
                                    <div class="progress-label">{{$bonus->getProgressHint()}}</div>
                                </div>
                            </div>
                        @elseif($bonus->money_available > 0)
                            <div class="cabinet__bonus-balance">
                                <div>{{\App\Helpers\UI::moneyStr($bonus->getBalanceCents() / 100, " ")}}
                                    <span
                                        class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                                </div>
                            </div>
                            @if($bonus->isProgressAvailable())
                                <div class="bonuses__item-progress"
                                     style="--progress: {{$bonus->getProgressPercents()}}%">
                                    <div class="progress-label">{{$bonus->getTargetHint()}}</div>
                                </div>
                            @endif
                        @endif
                </div>
            @else
                <div class="cabinet__bonus-time">
                    @if($bonus->isCancelable())
                        @if($bonus->getSpinsAvailable() > 0)
                            <div class="cabinet__bonus-balance">
                                <img src="/img/bonuses/fs.svg" alt="fs">
                                <div class="ml-05">{{$bonus->getSpinsAvailable()}}</div>
                            </div>
                        @elseif($bonus->money_available > 0)
                            <div class="cabinet__bonus-balance">
                                <span class="currency-icon currency-icon_{{mb_strtolower($bonus->getCurrencyName())}}"></span>
                                <div class="ml-05">{{$bonus->money_available}} {{$bonus->getCurrencyName()}}</div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="mt-1"></div>
            @endif
            <div class="cabinet__bonus-title">
                <div>{{ __('bonus.freespins') }}</div>
                <div class="cabinet__bonus-sub_title">{{ __('bonus.for_dep_'.$dep_n) }}</div>
            </div>
            <div class="bonus-btn-wrap">
                {!! $bonus->getRenderer()->renderPlayButton() !!}
                @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
            </div>
        </div>
    </div>
</div>
