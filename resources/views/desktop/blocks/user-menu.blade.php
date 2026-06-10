@if(auth()->check())
<div class="user-menu">

    <!-- Icon -->
    <div class="user-menu__avatar">
{{--        @include('common.blocks.user-avatar', ['percent' => auth()->user()->getNextRankProgress()])--}}
        @include('common.blocks.user-avatar', ['percent' => 0])
    </div>

    <!-- Name and rank -->
    <div class="user-menu__texts">
        <div class="user-menu__name">{{ auth()->user()->login }}</div>
        <div class="user-menu__id">id-{{App\MUser::getAuth()?->getId()}}</div>
        <div>
            <span class="user-menu__rank-name">{{ \App\MUser::getAuth()?->getVipStatus()?->statusInfo?->getName() }}</span>
{{--            <span class="user-menu__rank-progress">{{ auth()->user()->getNextRankProgress() }}%</span>--}}
        </div>
    </div>

    <div class="arrow-down"></div>

    <!-- Dropdown -->
    <div class="user-menu__dropdown">

        <div class="user-menu__info">

{{--            <div class="user-menu__avatar-and-name">--}}
{{--                @include('common.blocks.user-avatar', ['percent' => 0])--}}
{{--                <div class="user-menu__name-texts flex column">--}}
{{--                    <div class="user-menu__dd-name">{{ auth()->user()->login }}</div>--}}
{{--                    <div class="user-menu__name-link cabinet__link change-login-button mr-auto w-auto">{{ __('userMenu.changeNickname') }}</div>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="user-menu__rank">--}}
{{--                <div class="user-menu__rank-dd-name">{{ auth()->user()->getRankName() }}</div>--}}
{{--                <div class="user-menu__rank-progress-track">--}}
{{--                    <div class="user-menu__rank-progress-bar" style="width: {{ auth()->user()->getNextRankProgress() }}%"></div>--}}
{{--                </div>--}}
{{--                <div class="user-menu__rank-percents">{{ auth()->user()->getNextRankProgress() }}%</div>--}}
{{--            </div>--}}

        </div>

        @include('desktop.blocks.wallet-selector')
        <div id="bonus-lane"></div>

        <div class="user__menu-links">

            <a href="{{ route('cabinet.account', [], false) }}" class="user__menu-link cabinet__profile-link account-link">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/user.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/profile.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.myProfile') }}
            </a>

            <a href="{{ route('cabinet.transactions', [], false) }}" class="user__menu-link transactions-link">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/stats.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/time.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.transactionsHistory') }}
            </a>

{{--            <a href="{{ route('cabinet.sport', [], false) }}" class="user__menu-link sport-link">--}}
{{--                <div class="user__menu-icon-wrap">--}}
{{--                    <img src="/img/icons/menu/tv.png">--}}
{{--                </div>--}}
{{--                {{ __('userMenu.sportHistory') }}--}}
{{--            </a>--}}

            {{--            <a href="{{ route('cabinet.ranks', [], false) }}" class="user__menu-link ranks-link">--}}
{{--                <div class="user__menu-icon-wrap">--}}
{{--                    <img src="/img/icons/menu/favorite.png">--}}
{{--                </div>--}}
{{--                {{ __('userMenu.ranks') }}--}}
{{--            </a>--}}

            <a href="{{ route('cabinet.tournaments', [], false) }}" class="user__menu-link tournaments-link">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/fame.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/document.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.tournaments') }}
            </a>

            <a href="{{ route('cabinet.bonuses', [], false) }}" class="user__menu-link cabinet__bonuses-link bonuses-link">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/giftbox.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/cup.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.bonuses') }}
                <div id="bonus-av-cnt" class="ml-1"></div>
            </a>

            <a href="{{ route('cabinet.partnership', [], false) }}" class="user__menu-link user__menu-link__referral partnership-link">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/partners.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/users.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.referralSystem') }}
            </a>

            <a href="{{ route('cabinet.settings', [], false) }}" class="user__menu-link settings-link" id="user-menu-settings">
                <div class="user__menu-icon-wrap">
                    <img src="/img/icons/menu/settings1.png">
{{--                    <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user__menu-icon">--}}
                </div>
                {{ __('userMenu.settings') }}
            </a>

            <div class="use-promocode-btn">
                <img src="/img/icons/promo.png"> {{__('bonuses.activate_promo')}}
            </div>

            <a href="/logout" class="cabinet__logout-button">
                {{ __('userMenu.logout') }}
                <img src="/img/ui/icons/cabinet-menu/logout.svg" class="cabinet__logout-icon">
            </a>
        </div>
    </div>
</div>
<script>
    (() => {
        let bonusChecked = false;
        document.querySelector('.user-menu').addEventListener('mouseover', () => {
            if (bonusChecked === true) return;
            bonusChecked = true;
            loadToElm('{{route('bonus.count', [], false)}}', '#bonus-av-cnt');
            loadToElm('{{route('bonus.lane', [], false)}}', '#bonus-lane');
            setTimeout(()=> {bonusChecked = false}, 3000)
        });
    })();
</script>
@endif
