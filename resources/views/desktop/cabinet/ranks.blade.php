@php
    $ranks = \App\Models\Rank\UserRank::getAllRanks();
    $digits = 0;
    if (\App\Helpers\Localization::getCurrency() ===  \App\UserWallet::CURRENCY_mBTC) $digits = 2;
    /** @var \App\MUser $user */
    $user = auth()->user();
    $user_rank = $user->getRank();
@endphp
<div id="cabinet-position" data-position="ranks"></div>

<!-- Account info -->
<div class="cabinet__ranks-wrap">


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

            <div class="rank-info__sub-heading">{{ __('ranks.freeSpins') }}</div>
            <div class="rank-info__text">{{ __('ranks.freeSpinsText') }}</div>

        </div>
    </div>

    <!-- Rank -->
    <div class="cabinet__acc-rank-info">
        <div class="cabinet__acc-rank-icon">
            <img src="/img/ui/icons/star-colored.png" class="cabinet__acc-rank-image">
        </div>

        <div class="cabinet__acc-rank-texts">
            <div class="cabinet__acc-rank-name">{{ $user->getRankName() }}</div>
            <div class="cabinet__ranks-progress-wrap">
                <div class="cabinet__ranks-progress-track">
                    <div class="cabinet__ranks-progress-bar" style="width: {{ $user->getNextRankProgress() }}%"></div>
                </div>
            </div>
            <div class="cabinet__ranks-numbers">
                <span class="cabinet__ranks-numbers-value">{{ number_format($user->getRankValue(), $digits,".", " ") }}</span>
                / {{ number_format($user_rank->getEndValue(), $digits,".", " ") }} {{ auth()->user()->getCurrencyName() }}
            </div>
        </div>
    </div>

    <div class="cabinet__acc-divider"></div>

    <!-- Verification -->
    <div class="cabinet__acc-verification">
        <div class="cabinet__acc-vrf-title">
            {{ __('cabinet.verification') }}
            <div class="cabinet__acc-vrf-hint cabinet__hint">
                <img src="/img/ui/icons/question-mark.svg">
                <div class="cabinet__hint-popup" style="width: 15rem">
                    <div>{{ __('cabinet.verificationHint') }}</div>
                    <a href="#" class="cabinet__link" style="display: inline-block"
                       onclick="event.preventDefault(); document.querySelector('.cabinet__settings-link').click()">{{ __('cabinet.details') }}</a>
                </div>
            </div>
        </div>

        <div class="cabinet__acc-vrf-status status-verified">
            @if (auth()->user()->personalData()->isConfirmed())
                <img src="/img/ui/icons/shield-succ.svg" class="cabinet__acc-vrf-icon">
            @else
                <img src="/img/ui/icons/shield-fail.svg" class="cabinet__acc-vrf-icon">
            @endif
            {{ auth()->user()->personalData()->getStatusString() }}
        </div>

        @if(!auth()->user()->personalData()->isConfirmed() && auth()->user()->personalData()->status != \App\Models\PersonalData::STATUS_REQUESTED)
            <div class="cabinet__acc-vrf-link cabinet__link">{{ __('cabinet.verifyAccount') }}</div>
        @endif
    </div>

    <div class="cabinet__acc-divider"></div>

    <!-- Cash back -->
    <div class="cabinet__acc-cashback">
        <div class="cabinet_acc-cashback-icon">
            <img src="/img/ui/icons/cash-back-colored.png" class="cabinet__acc-cashback-image">
        </div>

        <div class="cabinet_acc-cashback-texts">
            <div class="cabinet__acc-vrf-title">
                {{ __('cabinet.yourCashback') }}:
            </div>

            <div class="cabinet__acc-cashback-percents">{{$user_rank->getWeeklyCashBackPrc()}}%</div>
        </div>

    </div>
</div>


<div class="cabinet__ranks-title">{{ __('ranks.allRanks') }}:</div>

<div class="cabinet__ranks-list">
    @foreach($ranks as $rank)
        <div class="rank @if($rank->getLevel() == $user_rank->getLevel()) rank_active @endif">
            @if($rank->getLevel() == $user_rank->getLevel())
                <div class="rank__your-rank">{{ __('ranks.yourRank') }}</div>
            @endif
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
                    <div class="currency-icon currency-icon_{{mb_strtolower($user->getCurrencyName())}}"></div>&nbsp;&nbsp;
                    <div class="money">
                        <div class="money__int">{!! number_format($rank->getStartValue(), $digits, ".", " ") !!}
                            &nbsp;@if($rank->getEndValue() > 0)
                                -&nbsp;{!! number_format($rank->getEndValue(), $digits, ".", " ") !!}
                            @endif</div>
                    </div>
                </div>
            </div>

            @if($rank->getDepositFrom() > 0)
                <div class="rank__cond-2">
                    <div class="rank__benefit rank__benefit_enabled">
                        <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/check.svg"></div>
                        {{__('ranks.weekly_fs')}}
                    </div>

                    <div class="rank_cond-2-item rank__benefit_enabled">
                        {{ __('ranks.deposit') }}:&nbsp;<span>{{ __('ranks.from') }}&nbsp;{!! \App\Helpers\UI::renderMoney($rank->getDepositFrom()) !!}&nbsp;-&nbsp;<span
                                    class="rank__cond-highlight">{{$rank->getSpins()}}FS</span></span>
                    </div>

                    <div class="rank_cond-2-item rank__benefit_enabled">
                        {{ __('ranks.bet') }}:&nbsp;{!! \App\Helpers\UI::renderMoney($rank->getBetLimit()) !!}</span>
                    </div>

                    <div class="rank_cond-2-item rank__benefit_enabled">
                        {{ __('ranks.wager') }}:&nbsp;<span class="table__text_white">x{{$rank->getWager()}}</span>
                    </div>

                    <div class="rank_cond-2-item rank__benefit_enabled">
                        {{ __('ranks.maxWin') }}
                        :&nbsp;{!! \App\Helpers\UI::renderMoney($rank->getWinLimit(), $user->getCurrencyName()) !!}
                    </div>
                </div>
            @else
                <div class="rank__cond-2">
                    <div class="rank__benefit">
                        <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/cross.svg"></div>
                        {{__('ranks.weekly_fs')}}
                    </div>

                    <div class="rank_cond-2-item">
                        {{ __('ranks.deposit') }}:&nbsp;<span>{{ __('ranks.from') }}&nbsp;- </span>
                    </div>

                    <div class="rank_cond-2-item">
                        {{ __('ranks.bet') }}:&nbsp;-
                    </div>

                    <div class="rank_cond-2-item">
                        {{ __('ranks.wager') }}:&nbsp;<span class="table__text_white">-</span>
                    </div>

                    <div class="rank_cond-2-item">
                        {{ __('ranks.maxWin') }}:&nbsp;-
                    </div>
                </div>
            @endif

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
                                                <div class="rank__benefit-icon"><img src="/img/cabinet/ranks/check.svg">
                                                </div>
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
