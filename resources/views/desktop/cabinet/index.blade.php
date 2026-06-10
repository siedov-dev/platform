@php
    $bonus_count = \App\Models\Bonuses\KUserBonus::getAvailableBonusesCount(\Illuminate\Support\Facades\Auth::id());
//    $bonus_count += count(\App\Models\BonusAvailable::getForUser(\App\MUser::getAuth()))
@endphp
<div class="cabinet">

    <!-- Menu -->
    <div class="cabinet__menu-wrap">
        <div class="cabinet__menu-header">

            @include('common.blocks.user-avatar', ['percent' => 0])

            <div class="cabinet__menu-hello">{{ __('cabinet.welcome') }}</div>
            <div class="cabinet__menu-name">{{ auth()->user()->login }}</div>
            <div class="cabinet__menu-id">id-{{ auth()->user()->id }}</div>
            <div class="cabinet__menu-hello">{{ \App\MUser::getAuth()?->getVipStatus()?->statusInfo?->getName() }}</div>
            <div class="cabinet__change-name-link cabinet__link change-login-button">{{ __('userMenu.changeNickname') }}</div>
        </div>

        <div class="cabinet__menu-links">

            <a href="{{ route('cabinet.account', [], false) }}" class="cabinet__menu-link account-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/user.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/profile.svg" class="cabinet__menu-icon">--}}
                </div>
                {{ __('userMenu.myProfile') }}
            </a>

            <a href="{{ route('cabinet.transactions', [], false) }}" class="cabinet__menu-link transactions-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/stats.png">
                </div>
                {{ __('userMenu.transactionsHistory') }}
            </a>

{{--            <a href="{{ route('cabinet.sport', [], false) }}" class="cabinet__menu-link sport-link">--}}
{{--                <div class="cabinet__menu-icon-wrap">--}}
{{--                    <img src="/img/icons/menu/tv.png">--}}
{{--                                        <img src="/img/ui/icons/cabinet-menu/time.svg" class="cabinet__menu-icon">--}}
{{--                </div>--}}
{{--                {{ __('userMenu.sportHistory') }}--}}
{{--            </a>--}}

{{--            <a href="{{ route('cabinet.ranks', [], false) }}" class="cabinet__menu-link cabinet__ranks-link ranks-link">--}}
{{--                <div class="cabinet__menu-icon-wrap">--}}
{{--                    <img src="/img/icons/menu/favorite.png">--}}
{{--                </div>--}}
{{--                {{ __('userMenu.ranks') }}--}}
{{--            </a>--}}

            <a href="{{ route('cabinet.tournaments', [], false) }}" class="cabinet__menu-link tournaments-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/fame.png">
                </div>
                {{ __('userMenu.tournaments') }}
            </a>

            <a href="{{ route('cabinet.bonuses', [], false) }}" class="cabinet__menu-link bonuses-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/giftbox.png">
                </div>
                {{ __('userMenu.bonuses') }}
                @if($bonus_count > 0)
                    <span class="circle-counter text-black">{{$bonus_count}}</span>
                @endif
            </a>

            <a href="{{ route('cabinet.partnership', [], false) }}" class="cabinet__menu-link partnership-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/partners.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/users.svg" class="cabinet__menu-icon">--}}
                </div>
                {{ __('userMenu.referralSystem') }}
            </a>

            <a href="{{ route('cabinet.settings', [], false) }}" class="cabinet__menu-link cabinet__settings-link settings-link">
                <div class="cabinet__menu-icon-wrap">
                    <img src="/img/icons/menu/settings1.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/gear.svg" class="cabinet__menu-icon">--}}
                </div>
                {{ __('userMenu.settings') }}
            </a>

            <a href="/logout" class="cabinet__logout-button">
                {{ __('userMenu.logout') }}
                <img src="/img/ui/icons/cabinet-menu/logout.svg" class="cabinet__logout-icon">
            </a>
        </div>
    </div>

    <div class="cabinet__content-wrap">
        <div class="cabinet__content">
            {!! $content !!}
        </div>
    </div>
</div>
