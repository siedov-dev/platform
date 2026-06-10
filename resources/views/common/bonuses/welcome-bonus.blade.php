@php
/** @var $bonus \App\Models\Bonuses\SharedMoneyBonus */
    $active = $bonus->isCancelable();
    /** @var \App\Models\BonusPatterns $pattern */
    $pattern = \App\Models\BonusPatterns::find($bonus->pattern_id);
    $dep_n = 1;
    preg_match('#(\d)#', $pattern?->name, $match);
    $dep_n = $match[1]?? 1;
    $dep_mult = $bonus->getJsonParam('multiplier');
    $wager = $bonus->getJsonParam('wager');
    //$wallet = new \App\Gameproviders\UserWalletGameWalletAdapter(\App\MUser::getAuth()->getWallet($bonus->getCurrency()))
@endphp
<div class="cabinet__bonuses-wrapper no-click-hide">
<div class="cabinet__bonuses-item cabinet__bonus welcome-bonus {{ $active ? 'cabinet__bonuses-item_active' : 'cabinet__bonuses-item_disabled' }}"
     data-id="{{ $bonus->getId() }}" style="--bgImg: url('/img/bonuses/wb/{{$bonus->curr_name}}-{{$bonus->pattern_id}}.png');">

    <div class="cabinet__bonus-content">
        @if($bonus->isActive())
            <div>
                <div class="cabinet__bonus-balance">
                    <div>{{\App\Helpers\UI::moneyStr($bonus->getBalanceCents() / 100, " ")}}
                        <span class="currency-color_{{mb_strtolower($bonus->getCurrencyName())}}">{{$bonus->getCurrencyName()}}</span>
                    </div>
                </div>
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
                    <img src="/img/icons/calendar.svg">
                    <div>{{__('bonus.to')}}&nbsp;{{\Carbon\Carbon::parse($bonus->active_to)->isoFormat('DD MMMM YYYY')}}</div>
                @endif
            </div>
            <div class="mt-1"></div>
        @endif
        <div class="cabinet__bonus-title">
            <div>{{ __('bonus.dep_bonus_prc', ['prc' => $dep_mult]) }}</div>
{{--            <div class="cabinet__bonus-goal">{{ __('bonus.wager') }} x{{$wager}}</div>--}}
            <div class="cabinet__bonus-sub_title">{{ __('bonus.for_dep_'.$dep_n) }}</div>
        </div>
        <div class="bonus-btn-wrap">
            {!! $bonus->getRenderer()->renderPlayButton() !!}
            @include('desktop.cabinet.bonus-info-btn', ['bonus' => $bonus])
        </div>
    </div>
</div>
</div>
