<header id="header" class="header">
    <script>
        window.userId = {{ auth()->id() }};
    </script>
    <div class="width-limiter flex">
        <div class="left-menu-wrapper">
            <div id="menu-button" class="menu-button" onclick="this.closest('.left-menu-wrapper').classList.toggle('shown')">
                <div class="menu-button__line"></div>
                <div class="menu-button__line"></div>
                <div class="menu-button__line"></div>
                <div class="has-new hidden">!</div>
            </div>
            <div class="left-menu">
                @include('desktop.menu')
            </div>
        </div>

{{--        <div class="header-switch">--}}
{{--            <a href="{{ route('games.live', [], false) }}" class="content-link @if(request()->route()->getName() === 'games.live') active @endif">--}}
{{--                <img src="/img/icons/menu2/live.png">--}}
{{--                <span class="menu__item-text">{{ __('menu.live') }}</span>--}}
{{--            </a>--}}
{{--            <a href="{{\App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl()}}" class="sport">--}}
{{--                <img src="/img/icons/menu2/sport.png">--}}
{{--                <span class="menu__item-text">{{ __('menu.bet-production') }}</span>--}}
{{--            </a>--}}
{{--            <a href="{{ route('landing', [], false) }}" class="content-link @if(request()->route()->getName() !== 'games.live') active @endif">--}}
{{--                <img src="/img/icons/menu2/slots.png">--}}
{{--                <span class="menu__item-text">{{ __('mainMenu.casino') }}</span>--}}
{{--            </a>--}}
{{--        </div>--}}
        <div class="header__quick-menu">
                <div class="header__quick-menu-item quick-games-menu" onclick="document.dispatchEvent(new Event('showGamesMenu'))">
                    <img src="/img/ui/icons/bottom-menu/games.png" class="bottom-menu__link-icon">
                    <div class="has-new">!</div>
                </div>
                <a href="{{ route('cabinet.bonuses', [], false) }}" onclick="cabinetShowBlock('bonuses', true); return false;" class="header__quick-menu-item bonuses-menu-link">
                    <img src="/img/ui/icons/bottom-menu/bonuses.png" class="bottom-menu__link-icon">
                    <div class="has-new hidden">!</div>
                </a>
                <div class="header__quick-menu-item" onclick="document.dispatchEvent(new Event('showSearchMenu'))">
                    <img src="/img/ui/icons/bottom-menu/search.png" class="bottom-menu__link-icon">
                </div>
                <div class="header__quick-menu-item support
                @if(auth()->check() && \App\Models\UserValue::getValue(auth()->id(), 'support_notify') === 1) notify @endif"
                     onclick="document.dispatchEvent(new Event('showSupportMenu')); this.classList.remove('notify');">
                    <img src="/img/ui/icons/bottom-menu/support.png" class="bottom-menu__link-icon">
                    <div class="has-new">!</div>
                </div>
                <div class="header-modals-wrapper">
                    @include('desktop.header-modals.games')
                    @include('desktop.header-modals.support')
                    @include('desktop.header-modals.search')
                </div>
        </div>

        <div class="header-switch sport-header-items ml-1">
            <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('live') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'live') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
                <img src="/img/icons/menu/live.png" class="button__icon">
                <span class="button__text">{{ __('menu.sport-live') }}</span>
            </a>

            <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('line') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'line') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
                <img src="/img/icons/menu/table.png" class="button__icon">
                <span class="button__text">{{ __('menu.sport-line') }}</span>
            </a>

            <a href="{{ \App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl('e-sport') }}" class="header__button button button_borderless sport-header-items
            @if(request()->get('gameId', '') === 'e-sport') active @endif" onclick="this.parentElement.querySelectorAll('a').forEach(el => el.classList.remove('active')); this.classList.add('active')">
                <img src="/img/icons/menu/vr.png" class="button__icon">
                <span class="button__text">{{ __('menu.sport-e-sports') }}</span>
            </a>
        </div>



        <a href="{{ route('games.live', [], false) }}" class="header__button button button_borderless tv-bet-items" onclick="document.getElementById('header').classList.remove('tv-bet');">
            <img src="/img/icons/menu/live.png" class="button__icon">
            <span class="button__text">{{ __('menu.live') }}</span>
        </a>

        <a href="{{ route('games.table', [], false) }}" class="header__button button button_borderless tv-bet-items" onclick="document.getElementById('header').classList.remove('tv-bet');">
            <img src="/img/icons/menu/table.png" class="button__icon">
            <span class="button__text">{{ __('menu.table') }}</span>
        </a>

        <a href="{{ route('games.sport', [], false) }}" class="header__button button button_borderless tv-bet-items" onclick="document.getElementById('header').classList.remove('tv-bet');">
            <img src="/img/icons/menu/vr.png" class="button__icon">
            <span class="button__text">{{ __('menu.sport') }}</span>
        </a>

        <a href="{{ route('landing', [], false) }}" class="logo sport-hide content-link">
{{--            <img alt="Kraken" src="/img/Logo.png" class="logo__image">--}}
{{--            <img alt="Kraken" src="/img/kraken-logo-{{\App\Helpers\UI::getTheme()}}.svg" class="logo__image">--}}
            <div class="logo__image"></div>
{{--            @include('common.blocks.logo')--}}
        </a>


        @if (auth()->check())
            <div class="header__wallet-button tv-bet-hide">
                <div class="wallet-bonus-balance">
                    @include('desktop.blocks.wallet-selector')
                </div>
            </div>

{{--            @include('desktop.blocks.notifications')--}}

            @include('desktop.blocks.user-menu')
        @else

            <a href="{{ route('login.form', [], false) }}" id="login-button" class="header__login-button button button_outlined" data-ajax=".modal .content">{{ __('header.login') }}</a>


            <a href="{{ route('register.form', [], false) }}" id="register-button" class="header__reg-button button button_green" data-ajax="modal">{{ __('header.signUp') }}</a>


            <script>
                (async () => {

                    let loginButton = document.getElementById('login-button');
                    let clickTime = 0;

                    // Create modal when user clicks login button.
                    loginButton.addEventListener('click', () => {
                        clickTime = Date.now();
                        Modal.show('<div class="login-form"></div>');
                        Spinner.show('.login-form');
                    });

                    // Smoothly hide spinner and set modal content.
                    loginButton.addEventListener('success', async e => {
                        e.preventDefault();
                        if (Date.now() - clickTime > 1000) {
                            await Spinner.hide('.login-form');
                        }

                        let content = document.querySelector('.login-form');
                        if (content === null) {
                            Modal.show(e.detail);
                        } else {
                            setInnerHtml(content.parentNode, e.detail);
                        }
                    });
                })();
            </script>
        @endif

    </div>
</header>

@if(\Illuminate\Support\Facades\Auth::check())
    <div class="wallet-wrapper desktop">
        <div class="user-wallet">
            @include('common.blocks.user-wallet')
        </div>
    </div>
@endif
