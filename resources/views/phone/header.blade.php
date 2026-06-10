<header id="header" class="header @if(\Illuminate\Support\Facades\Auth::check())authorized @endif">
    <script>
        window.userId = {{ auth()->check() ? auth()->id() : 'null' }};
    </script>

    <div class="brand">
        <a href="/{{ app()->getLocale() }}" class="logo content-link">
{{--            @include('common.blocks.logo')--}}
            <div class="logo__image"></div>
        </a>
    </div>
    @if(\Illuminate\Support\Facades\Auth::check())
        <div class="flex mr-1 ai-center jc-sb grow">

            <div class="header__wallet-button tv-bet-hide">
{{--                @include('phone.blocks.wallet-selector')--}}
                @include('phone.blocks.my-profile-btn')
            </div>

{{--            <div class="header__balance">--}}
{{--                <div class="main-currency-icon currency-icon currency-icon_{{ strtolower(auth()->user()->getCurrencyName()) }}"></div>--}}
{{--                <div class="flex column wallet-selector__amount">--}}
{{--                    <div class="wallet-selector__amount">--}}
{{--                        @php--}}
{{--                            $amount = auth()->check() ? auth()->user()->getWallet()->getBalanceStr() : 0;--}}
{{--                            $user_currencies = auth()->user()->getActiveCurrencies();--}}
{{--                        @endphp--}}
{{--                        <span class="main-currency-int wallet-selector__amount-int">{{ number_format(explode('.', $amount)[0], 0, '.', ' ') }}.</span>--}}
{{--                        <span class="main-currency-rest wallet-selector__amount-rest">{{ explode('.', $amount)[1]??'00' }}</span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="header__deposit-btn ml-1" onclick="document.dispatchEvent(new CustomEvent('toggleWallet'))">
                <img src="/img/ui/icons/wallet.png" class="user-menu-button__image">
                <div>&times;</div>
            </div>

{{--            <div class="user-menu-button" onclick="document.dispatchEvent(new CustomEvent('showRightSidebar'))">--}}
{{--                <img src="/img/icons/user_main_icon.png" class="user-menu-button__image">--}}
{{--            </div>--}}
        </div>
    @else
        <div class="flex jc-center ai-center mr-05">
            <div class="flex ai-center text-smaller text-title" onclick="document.dispatchEvent(new Event('showSearchMenu'))">
                <img src="/img/ui/icons/bottom-menu/search.png" class="bottom-menu__link-icon invert-white">
                {{ __('footer.search') }}
            </div>
            <div class="flex ai-center ml-1 text-smaller text-title" onclick="document.dispatchEvent(new Event('showSupportMenu'))">
                <img src="/img/ui/icons/bottom-menu/support.png" class="bottom-menu__link-icon invert-white">
                {{ __('footer.support') }}
            </div>
        </div>
    @endif
</header>

@if(\Illuminate\Support\Facades\Auth::check())
    <div class="wallet-wrapper">
        <div class="user-wallet">
            @include('common.blocks.user-wallet')
        </div>
    </div>
@endif
