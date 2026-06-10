<?php
/**
 * @var \App\Models\Bonuses\KUserBonus[] $bonuses
 */

use Illuminate\Support\Facades\Auth;

/** @var \App\MUser $user */
$user = Auth::user();

$active_list = [];
$available_list = [];
$done_list = [];

\App\Services\KBonus\KBonus::getInstance()->actualizeUserBonuses($user->getId());
$bonuses = \App\Services\KBonus\KBonus::getInstance()->getUserBonuses($user->getId(), $user->currency);
$completed = \App\Services\KBonus\KBonus::getInstance()->getUserCompletedBonuses($user->getId(), $user->currency);
foreach ($bonuses as $bonus) {
//    $bonus = \App\Models\Bonuses\KUserBonus::convertByType($bonus);
    if ($bonus->isCompleted()) {
        $done_list[] = $bonus;
    } else if ($bonus->isActive() || $bonus->isLocked()) {
        $bonus->actualize();
        $active_list[] = $bonus;
    } else {
        $available_list[] = $bonus;
    }
}
foreach ($completed as $bonus) {
//    $bonus = \App\Models\Bonuses\KUserBonus::convertByType($bonus);
    $done_list[] = $bonus;
}

$active_tab = 'arch';

if (count($active_list) > 0) {
    $active_tab = 'active';
} elseif(count($available_list) > 0) {
    $active_tab = 'available';
}

$vip_status = $user->getVipStatus();
?>
<div id="cabinet-position" data-position="bonuses"></div>

<div class="tabs cabinet--bonuses_tabs">
    <div class="tabs__tab @if($active_tab === 'active') tabs__tab_active @endif" data-target="#cabinet__bonuses_active">{{ __('bonus.active_bonuses') }}
        @if(count($active_list) > 0)<div class="amount">{{count($active_list)}}</div>@endif
    </div>
    <div class="tabs__tab @if($active_tab === 'available') tabs__tab_active @endif" data-target="#cabinet__bonuses">{{ __('bonus.available_bonuses') }}
        @if(count($available_list) > 0)<div class="amount">{{count($available_list)}}</div>@endif
    </div>
    <div class="tabs__tab @if($active_tab === 'arch') tabs__tab_active @endif" data-target="#cabinet__completed">{{ __('bonus.archive') }}</div>

    <div class="tabs__right-link use-promocode-btn">
        <img src="/img/cabinet/bonuses/icons/ticket.svg">&nbsp;
        {{ __('cabinet.havePromoCode') }}
    </div>
</div>

<div class="cabinet__pad-title mb-1">{{__('cabinet.bonuses_currency').' '.\App\MUser::getAuth()?->getCurrencyName()}}</div>

<div id="cabinet__bonuses_active" class="cabinet__bonuses" @if($active_tab !== 'active') style="display: none" @endif">
    @if($vip_status !== null)
        @include('desktop.cabinet.vip-status', ['vip_status' => $vip_status])
    @endif

    @foreach ($active_list as $bonus)
        {!! $bonus->getRenderer()->renderCabinetBonus() !!}
    @endforeach

    @if(count($active_list) === 0)
        <div class="no-records">
            <div class="no-records__title">{{ __('cabinet.noBonusesTitle') }}</div>
            <div class="no-records__hint">{{ __('cabinet.noBonusesHint') }}</div>
        </div>
    @endif
</div>

<div id="cabinet__bonuses" class="cabinet__bonuses" @if($active_tab !== 'available') style="display: none" @endif>
    @if($vip_status !== null)
        @include('desktop.cabinet.vip-status', ['vip_status' => $vip_status])
    @endif

    @foreach ($available_list as $bonus)
        {!! $bonus->getRenderer()->renderCabinetBonus() !!}
    @endforeach

    @if(count($available_list) === 0)
            <div class="no-records">
                <div class="no-records__title">{{ __('cabinet.noBonusesTitle') }}</div>
                <div class="no-records__hint">{{ __('cabinet.noBonusesHint') }}</div>
            </div>
    @endif
</div>

<div id="cabinet__completed" class="cabinet__bonuses" @if($active_tab !== 'arch') style="display: none" @endif>
    @foreach ($done_list as $bonus)
        {!! $bonus->getRenderer()->renderCabinetBonus() !!}
    @endforeach

    @if(count($done_list) === 0)
        <div class="no-records">
            <div class="no-records__title">{{ __('cabinet.noBonusesTitle') }}</div>
            <div class="no-records__hint">{{ __('cabinet.noCompletedBonusesHint') }}</div>
        </div>
    @endif
</div>
