@php
$id = request()->route()->parameter('id', 'first-deposit');
@endphp
<div class="article__sidebar ml-2">
    <a href="{{ route('bonuses.info', ['id' => '375procent'], false) }}" class="article__sidebar-item @if($id === '375procent') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.375_procent') }}</div>
    </a>
    
    <a href="{{ route('bonuses.info', ['id' => 'first-deposit'], false) }}" class="article__sidebar-item @if($id === 'first-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.firstDepName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'second-deposit'], false) }}" class="article__sidebar-item @if($id === 'second-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.secondDepName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'third-deposit'], false) }}" class="article__sidebar-item @if($id === 'third-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.thirdDepName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'active-cashback'], false) }}" class="article__sidebar-item @if($id === 'active-cashback') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.activeCashBackName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'plus-minus'], false) }}" class="article__sidebar-item @if($id === 'plus-minus') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.plusMinusName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'super-free-spins'], false) }}" class="article__sidebar-item @if($id === 'super-free-spins') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.wagerFSName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'free-spins-cashback'], false) }}" class="article__sidebar-item @if($id === 'free-spins-cashback') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.fsPlusCashBackName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'filthy-free-spins'], false) }}" class="article__sidebar-item @if($id === 'filthy-free-spins') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.dirtyFSName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'non-deposit'], false) }}" class="article__sidebar-item @if($id === 'non-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.dirtyFSName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'active-non-deposit'], false) }}" class="article__sidebar-item @if($id === 'active-non-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.activeNonDepName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'deposit'], false) }}" class="article__sidebar-item @if($id === 'deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.depBonusName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'stock-drummatic-plus-minus'], false) }}" class="article__sidebar-item @if($id === 'stock-drummatic-plus-minus') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.stockPlusMinusName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'stock-cashback'], false) }}" class="article__sidebar-item @if($id === 'stock-cashback') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.stockCashBackName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'stock-deposit'], false) }}" class="article__sidebar-item @if($id === 'stock-deposit') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.stockDepositName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'stock-vegas-freespins'], false) }}" class="article__sidebar-item @if($id === 'stock-vegas-freespins') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('event-6.name') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'qt-free-spins'], false) }}" class="article__sidebar-item @if($id === 'qt-free-spins') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.fsWithoutWagerName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'deposit-free-spins-tournament'], false) }}" class="article__sidebar-item @if($id === 'deposit-free-spins-tournament') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.depPlusFSPlusTournamentName') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'pragmatic-play-free-spins'], false) }}" class="article__sidebar-item @if($id === 'smart-wager') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonuses.smart-wager') }}</div>
    </a>

    <a href="{{ route('bonuses.info', ['id' => 'vegas-freespins'], false) }}" class="article__sidebar-item @if($id === 'vegas-freespins') article__sidebar-item_active @endif">
        <div class="article__sidebar-item-title">{{ __('bonus.vegas_fs_name') }}</div>
    </a>
    
    

</div>
