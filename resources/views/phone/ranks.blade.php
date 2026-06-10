@php
    $ranks = \App\Models\Rank\UserRank::getAllRanks();
    $digits = 0;
    if (\App\Helpers\Localization::getCurrency() ===  \App\UserWallet::CURRENCY_mBTC) $digits = 2;
@endphp

<div id="rank-info-modal" style="display: none">
    <div class="rank-preview" style="pointer-events: none"></div>

    <div class="rank-info_texts">
        <div class="rank-info__heading">{{ __('ranks.gameTerms') }}:</div>

        <div class="rank-info__sub-heading">{{ __('ranks.playerRank') }}</div>
        <div class="rank-info__text">{{ __('ranks.playerRankText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.betsSum') }}:</div>
        <div class="rank-info__text">{{ __('ranks.betsSumText') }}:</div>
        <div class="rank-info__hint">{{ __('ranks.betsSumHint') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.spin') }}</div>
        <div class="rank-info__text">{{ __('ranks.spinText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.freeSpins') }}</div>
        <div class="rank-info__text">{{ __('ranks.freeSpinsText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.wager') }}</div>
        <div class="rank-info__text">{{ __('ranks.wagerText') }}</div>
        <div class="rank-info__hint">{{ __('ranks.wagerHint') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.maxWin') }}</div>
        <div class="rank-info__text">{{ __('ranks.maxWinText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.maxWinTitle') }}</div>
        <div class="rank-info__text">{{ __('ranks.maxWinText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.insurance') }}</div>
        <div class="rank-info__text">{{ __('ranks.insuranceText') }}</div>

        <div class="rank-info__sub-heading">{{ __('ranks.callback') }}</div>
        <div class="rank-info__text">{{ __('ranks.callbackText') }}</div>

    </div>
</div>


<div class="width-limiter">

    <div class="cabinet__ranks-title">{{ __('ranks.allRanks') }}:</div>

    <div class="cabinet__ranks-list">
        @foreach($ranks as $rank)
            <div class="rank">
                <div class="rank__head">
                    <div class="rank__icon">
                        <img src="/img/cabinet/ranks/rank_{{$rank->getLevel() -1}}.png" class="rank__image">
                    </div>
                    <div>
                        <div class="rank__label">{{ __('ranks.name') }}:</div>
                        <div class="rank__name">{{ __('ranks.rank_'.$rank->getLevel()) }}</div>
                    </div>
                </div>

                <div class="rank__cond">
                    <div class="rank__label">{{ __('ranks.sumOfBets') }}:</div>
                    <div class="rank__cond-value">
                        <div class="currency-icon currency-icon_{{mb_strtolower(\App\UserWallet::currencyName(\App\Helpers\Localization::getCurrency()))}}"></div>&nbsp;&nbsp;
                        <div class="money">
                            <div class="money__int">{!! number_format($rank->getStartValue(), $digits, ".", " ") !!}
                                &nbsp;@if($rank->getEndValue() > 0)
                                    -&nbsp;{!! number_format($rank->getEndValue(), $digits, ".", " ") !!}
                                @endif</div>
                        </div>
                    </div>
                </div>

                <div class="rank__benefits">

                    @if($rank->getCashBack() > 0)
                        <div class="rank__benefit rank__benefit_enabled">
                            <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/check.svg"></div>
                            @else
                                <div class="rank__benefit">
                                    <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/cross.svg"></div>
                                    @endif
                                    {{__('ranks.deposit_insurance')}}
                                </div>

                                <div class="rank__benefit rank__benefit_enabled">
                                    <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/check.svg"></div>
                                    {{__('ranks.weekly_cashback')}} {{$rank->getWeeklyCashBackPrc()}}%
                                </div>

                                @if($rank->getWagerDiminution() > 0)
                                    <div class="rank__benefit rank__benefit_enabled">
                                        <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/check.svg"></div>
                                        @else
                                            <div class="rank__benefit">
                                                <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/cross.svg">
                                                </div>
                                                @endif
                                                {{__('ranks.wager_diminution')}}
                                            </div>

                                            @if($rank->isVip())
                                                <div class="rank__benefit rank__benefit_enabled">
                                                    <div class="rank__benefit-icon"><img
                                                                src="/img/cabinet/ranks/check.svg"></div>
                                                    @else
                                                        <div class="rank__benefit">
                                                            <div class="rank__benefit-icon"><img
                                                                        src="/img/cabinet/ranks/cross.svg"></div>
                                                            @endif
                                                            {{__('ranks.vip_access')}}
                                                        </div>

                                                </div>
                                    </div>
                                    @endforeach
                        </div>


                </div>
