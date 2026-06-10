<div class="bottom-menu-wrap">
    <div class="bottom-menu @if(!\Illuminate\Support\Facades\Auth::check()) not-auth @endif">

{{--        <a href="/games/slots" class="bottom-menu__green-button">--}}
{{--            <img src="/img/ui/icons/bottom-menu/joystick.svg" class="bottom-menu__green-button-icon">--}}
{{--        </a>--}}
        <div class="bottom-menu__green-button" onclick="document.dispatchEvent(new CustomEvent('showMainMenu'))">
            <div id="menu-button" class="menu-button">
                <div class="menu-button__line"></div>
                <div class="menu-button__line"></div>
                <div class="menu-button__line"></div>
                <div class="has-new hidden">!</div>
            </div>
        </div>

        <div class="bottom-menu__left">
            @if (\Illuminate\Support\Facades\Auth::check())
                <div class="bottom-menu__link quick-games-menu" onclick="document.dispatchEvent(new Event('showGamesMenu'))">
                    <img src="/img/ui/icons/bottom-menu/games.png" class="bottom-menu__link-icon">
                    <span>{{ __('footer.games') }}</span>
                    <div class="has-new">!</div>
                </div>
                <a href="{{ route('cabinet.bonuses', [], false) }}" onclick="cabinetShowBlock('bonuses', true); return false;" class="bottom-menu__link bonuses-menu-link">
                    <img src="/img/ui/icons/bottom-menu/bonuses.png" class="bottom-menu__link-icon">
                    <span>{{ __('footer.bonuses') }}</span>
                    <div class="has-new hidden">!</div>
                </a>
            @else
                <a href="{{ route('login.form', [], false) }}" data-ajax="modal" id="login-button" class="auth-buttons__login">{{ __('header.login') }}</a>
            @endif
        </div>
{{--        <div class="bottom-menu__left sport">--}}
{{--            <a href="{{ route('games.live', [], false) }}" onclick="this.parentElement.parentElement.classList.remove('show-sport'); Overlay.hide()" data-ajax="#layout-content" class="bottom-menu__link">--}}
{{--                <img src="/img/icons/menu/live.png" class="bottom-menu__link-icon">--}}
{{--                {{ __('menu.live') }}--}}
{{--            </a>--}}
{{--            <a href="/{{ app()->getLocale() }}/games/tv?providers=tv-bet%7Ctv-bet" onclick="this.parentElement.parentElement.classList.remove('show-sport');Overlay.hide()" data-ajax="#layout-content" class="bottom-menu__link">--}}
{{--                <img src="/img/icons/menu/tv.png" class="bottom-menu__link-icon">--}}
{{--                {{ __('menu.tv') }}--}}
{{--            </a>--}}
{{--        </div>--}}

        <div class="bottom-menu__right">
            @if(\Illuminate\Support\Facades\Auth::check())
{{--            <div class="bottom-menu__link" id="ntf-button" onclick="document.dispatchEvent(new CustomEvent('showNotifications'))">--}}
{{--                <div id="ntf__indicator" class="bottom-menu__indicator" style="display: none"></div>--}}
{{--                <img src="/img/ui/icons/bottom-menu/bell.svg" class="bottom-menu__link-icon">--}}
{{--                {{ __('header.notif') }}--}}
{{--            </div>--}}
                <div class="bottom-menu__link" onclick="document.dispatchEvent(new Event('showSearchMenu'))">
                    <img src="/img/ui/icons/bottom-menu/search.png" class="bottom-menu__link-icon">
                    <span>{{ __('footer.search') }}</span>
                </div>
                <div class="bottom-menu__link support @if(auth()->check() && \App\Models\UserValue::getValue(auth()->id(), 'support_notify') === 1) notify @endif"
                     onclick="document.dispatchEvent(new Event('showSupportMenu')); this.classList.remove('notify');">
                    <img src="/img/ui/icons/bottom-menu/support.png" class="bottom-menu__link-icon">
                    <span>{{ __('footer.support') }}</span>
                    <div class="has-new">!</div>
                </div>
            @else
                <a href="{{ route('register.form', [], false) }}" id="register-button" data-ajax="modal" class="auth-buttons__reg">{{ __('header.signUp') }}</a>
            @endif
        </div>

        @include('phone.bottom-modals.main-menu')
        @include('phone.bottom-modals.navigation')
        @include('phone.bottom-modals.games')
        @include('phone.bottom-modals.support')
        @include('phone.bottom-modals.search')

    </div>

{{--    <div id="ntf-dropdown" class="ntf__dd">--}}

{{--        <div class="ntf__dd-title">--}}
{{--            {{ __('header.notifications') }}--}}
{{--            <div class="ntf__dd-close-btn" onclick="document.dispatchEvent(new CustomEvent('hideNotifications'))">&times;</div>--}}
{{--        </div>--}}

{{--        @include('common.blocks.notifications')--}}

{{--        <div class="ntf__del-btn-wrap">--}}
{{--            <div class="ntf__del-btn" style="display: none">{{__('notify.clear_all')}} &nbsp; &times;</div>--}}
{{--        </div>--}}
{{--    </div>--}}


{{--    <div id="ntf-backdrop" onclick="document.dispatchEvent(new CustomEvent('hideNotifications'))"></div>--}}
</div>
@include('phone.bottom-modals.popup')
