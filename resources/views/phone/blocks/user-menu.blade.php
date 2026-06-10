<div class="user-menu">

    <a href="/cabinet/account" class="user-menu__link account-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/user.png">
{{--            <img src="/img/ui/icons/cabinet-menu/profile.svg" class="user-menu__icon">--}}
        </div>
        {{ __('userMenu.myProfile') }}
    </a>

    <a href="/cabinet/transactions" class="user-menu__link transactions-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/stats.png">
{{--            <img src="/img/ui/icons/cabinet-menu/time.svg" class="user-menu__icon">--}}
        </div>
        {{ __('userMenu.transactionsHistory') }}
    </a>

{{--    <a href="/cabinet/ranks" class="user-menu__link ranks-link">--}}
{{--        <div class="user-menu__icon-wrap">--}}
{{--            <img src="/img/icons/menu/favorite.png">--}}
{{--        </div>--}}
{{--        {{ __('userMenu.ranks') }}--}}
{{--    </a>--}}
{{--    <a href="/cabinet/sport" class="user-menu__link sport-link">--}}
{{--        <div class="user-menu__icon-wrap">--}}
{{--            <img src="/img/icons/menu/tv.png">--}}
{{--                        <img src="/img/ui/icons/cabinet-menu/document.svg" class="user-menu__icon">--}}
{{--        </div>--}}
{{--        {{ __('userMenu.sportHistory') }}--}}
{{--    </a>--}}

    <a href="/cabinet/tournaments" class="user-menu__link tournaments-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/fame.png">
{{--            <img src="/img/ui/icons/cabinet-menu/document.svg" class="user-menu__icon">--}}
        </div>
        {{ __('userMenu.tournaments') }}
    </a>

    <a href="/cabinet/bonuses" class="user-menu__link cabinet__bonuses-link bonuses-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/giftbox.png">
{{--            <img src="/img/ui/icons/cabinet-menu/cup.svg" class="user-menu__icon">--}}
        </div>
        {{ __('userMenu.bonuses') }}
        <div id="bonus-av-cnt" class="ml-1"></div>
    </a>

    <a href="/cabinet/partnership" class="user-menu__link  user__menu-link__referral partnership-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/partners.png">
{{--            <img src="/img/ui/icons/cabinet-menu/users.svg" class="user-menu__icon">--}}
        </div>
        {{ __('userMenu.referralSystem') }}
    </a>

    <a href="/cabinet/settings" class="user-menu__link settings-link">
        <div class="user-menu__icon-wrap">
            <img src="/img/icons/menu/settings1.png">
{{--            <img src="/img/ui/icons/cabinet-menu/gear.svg" class="user-menu__icon" id="user-menu-settings">--}}
        </div>
        {{ __('userMenu.settings') }}
    </a>

    @if(auth()->check())
        <div class="use-promocode-btn" onclick="document.dispatchEvent(new CustomEvent('hideRightSidebar'))">
            <img src="/img/icons/promo.png"> {{__('bonuses.activate_promo')}}
        </div>

        <a href="/logout" class="cabinet__logout-button">
        {{ __('userMenu.logout') }}
        <img src="/img/ui/icons/cabinet-menu/logout.svg" class="cabinet__logout-icon">
    </a>
    @endif
</div>
