@php
$route = request()->route()->getName();
@endphp

<nav class="menu">

    @if(\Illuminate\Support\Facades\Auth::check())
        <div class="sidebar-user-info mb-1">
        <div class="flex jc-sb ai-fs">
            <a class="hidden-menu__first-block-price relative" href="{{route('slc.page-info', ['page' => 'slc'], false)}}" data-ajax="modal">
                <p>{{__('menu.bonus_balance')}}</p>
                <div class="info-icon popup-link" style="margin-right: -1rem;"></div>

                <div class="hidden-menu__first-price">
                    <img src="/images/svg/slc-icon.svg" alt="" width="30px">
                    <span class="text-title">
                    {!! \App\Helpers\UI::renderSlcMoney(\App\MUser::getAuth()->getSlcBalance()) !!}
                    </span>
                </div>
            </a>
            <div class="btn-lost-treasures hidden-menu__first-block-price">
                <a href="{{route('slc.treasures', [], false)}}" class="content-link">
                    <img src="/img/chests/lost_treasures_btn.png">
                </a>
            </div>
        </div>

        <div class="hidden-menu__first-links">
            <a class="menu__item hidden-menu__first-link my-tasks" href="{{route('slc.tasks', [], false)}}">
                                  <span class="wrap-img">
                                     <img src="/images/tasks-icon.png" alt="">
                                  </span>
                {{__('menu.daily_tasks')}}
                <span class="label hidden">New</span>
                <span class="circle-counter hidden">1</span>
            </a>
            <a class="menu__item hidden-menu__first-link achievements" href="{{route('slc.achievements', [], false)}}">
                                  <span class="wrap-img">
                                     <img src="/images/achievements-icon.png" alt="">
                                  </span>
                {{__('menu.achievements')}}
                <span class="label hidden">New</span>
                <span class="circle-counter hidden">1</span>
            </a>
            <a id="lucky-spin" class="hidden-menu__first-link lucky-spin popup-link" href="{{route('wheel.modal', [], false)}}" data-ajax="modal" >
                                  <span class="wrap-img">
                                     <img src="/images/lucky-spin-icon.png" alt="">
                                  </span>
                {{__('menu.lucky_spin')}}
                <span class="label hidden">New</span>
                <span class="circle-counter hidden">1</span>
            </a>
            <a id="slot-machine-link" class="hidden-menu__first-link shop slot-machine popup-link" href="{{route('slotmachine.modal', [], false)}}" data-ajax="modal" data-popup-type="slc-popup">
                                  <span class="wrap-img">
                                     <img src="/images/slot-machine-icon.png" alt="">
                                  </span>
                {{__('menu.slot_machine')}}
            </a>
            <a class="menu__item hidden-menu__first-link shop items" href="{{route('slc.shop', [], false)}}">
                                  <span class="wrap-img">
                                     <img src="/images/shop-icon.png" alt="">
                                  </span>
                {{__('menu.shop')}}
                <span class="label hidden">Fresh</span>
                <span class="circle-counter hidden">1</span>
            </a>
            <a class="menu__item hidden-menu__first-link shop user-items" href="{{route('slc.user-items', [], false)}}">
                                  <span class="wrap-img">
                                     <img src="/images/shop-icon.png" alt="">
                                  </span>
                {{__('menu.inventory')}}
                <span class="label hidden">New</span>
                <span class="circle-counter hidden">1</span>
            </a>
        </div>
        </div>
    @endif

    <div class="menu-block">
{{--        <a href="{{\App\GamesProviders\BetProduction\BetProductionApi::getPlayUrl()}}" class="menu__item @if(isset($_GET['providers']) && $_GET['providers'] === 'bet-production/bet-production') active @endif--}}
{{--        @if(\App\Models\DisabledGame::isSystemDisabled(\App\GamesProviders\GamesProvider::GAME_SYSTEM_BET_PRODUCTION, 'test'))--}}
{{--            @if(\Illuminate\Support\Facades\Auth::check() && \App\MUser::getAuth()->isTestUser())--}}
{{--                test-mode--}}
{{--                @else--}}
{{--                disabled--}}
{{--            @endif--}}
{{--        @elseif(\App\Models\DisabledGame::isSystemDisabled(\App\GamesProviders\GamesProvider::GAME_SYSTEM_BET_PRODUCTION, app()->getLocale())) disabled @endif">--}}
{{--            <img src="/img/icons/menu2/sport.png">--}}
{{--            <span class="menu__item-text">{{ __('menu.bet-production') }}</span>--}}
{{--        </a>--}}
        <a href="{{ route('games.slots', [], false) }}" class="menu__item @if($route === 'games.slots') active @endif">
            <img src="/img/icons/menu3/WSlots.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.slots') }}</span>
        </a>
        <a href="{{ route('games.crash', [], false) }}" class="menu__item @if($route === 'games.slots') active @endif">
            <img class="menu-img" src="/img/icons/game_categories/crash.png">
            <span class="menu__item-text">{{ __('landing.crash') }}</span>
        </a>

        <a href="{{ route('games.live', [], false) }}" class="menu__item @if($route === 'games.live') active @endif">
            <img src="/img/icons/menu3/WLive.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.live') }}</span>
        </a>

        <a href="{{ route('games.table', [], false) }}" class="menu__item @if($route === 'games.table') active @endif">
            <img src="/img/icons/menu3/WTablegames.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.table') }}</span>
        </a>

        <a href="{{ route('games.sport', [], false) }}" class="menu__item @if($route === 'games.sport') active @endif">
            <img src="/img/icons/menu3/WController.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.sport') }}</span>
        </a>

        <a href="/{{ app()->getLocale() }}/games?providers=tv-bet%7Ctv-bet" class="menu__item
        @if(isset($_GET['providers']) && $_GET['providers'] === 'tv-bet/tv-bet') active @endif">
            <img src="/img/icons/menu3/WMonitor.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.tv') }}</span>
        </a>

    </div>

    <hr class="menu__line"/>

    <div class="menu-block">
        @php
            $tnt_cnt = count(\App\Models\Tournaments\Custom\CustomTournament::getAvailableTournaments());
            $s = new \App\Services\Tournaments\TournamentsService(\App\MUser::getAuth());
            if ($s->tournamentsEnabled()) {
                $tnt_cnt += count($s->getAvailableTournaments());
            }
        @endphp
        <a href="{{ route('tournaments', [], false) }}" class="menu__item @if($route === 'tournaments') active @endif">
            <img src="/img/icons/tournament.svg" class="no-filter">
            <span class="menu__item-text golden">{{ __('header.tournaments') }}</span>
            @if($tnt_cnt > 0)
                <span class="circle-counter text-black">{{$tnt_cnt}}</span>
            @endif
        </a>
        @php
            $promo_cnt = \App\Models\Promos\PromoItem::getAvailable(\App\MUser::getAuth())->count();
        @endphp
        @if($promo_cnt > 0)
            <a href="{{ route('promos', [], false) }}" class="menu__item @if($route === 'promos') active @endif content-link">
                <img src="/img/icons/promo.svg" class="no-filter">
                <span class="menu__item-text red">{{ __('promos.promo') }}</span>
                    <span class="circle-counter text-black red">{{$promo_cnt}}</span>
            </a>
        @endif

        @guest
{{--            <a href="{{ route('bonuses_guest', [], false) }}" class="menu__item @if($route === 'bonuses-guest') active @endif">--}}
{{--                <img src="/img/icons/menu2/gift.png">--}}
{{--                <span class="menu__item-text">{{ __('header.bonuses') }}</span>--}}
{{--            </a>--}}
        @else
            <a href="{{ route('bonuses_guest', [], false) }}" class="menu__item no-ajax bonuses-menu-link" onclick="cabinetShowBlock('bonuses', true); return false;">
                <img src="/img/icons/menu3/WGift.svg" class="menu-img">
                <span class="menu__item-text">{{ __('header.bonuses') }}</span>
                <div class="has-new hidden">!</div>
            </a>
        @endguest

        <a href="{{ route('hallOfFame', [], false) }}" class="menu__item @if($route === 'hallOfFame') active @endif">
            <img src="/img/icons/menu3/WHallofFame.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.hallOfFame') }}</span>
            <span class="label">Hot</span>
        </a>

    </div>
    <hr class="menu__line"/>

    <div class="menu-block">
{{--        <a href="{{ route('games.favourites', [], false) }}" id="fav-games-btn" class="menu__item @if($route === 'games.favourites') active @endif" @if(!auth()->check())style="opacity: 0.25; pointer-events: none @endif">--}}
{{--            <img src="/img/icons/menu2/star.png">--}}
{{--            <span class="menu__item-text">{{ __('menu.favourites') }}</span>--}}
{{--        </a>--}}
        <div class="menu__item menu__item_app no-ajax" onclick="window.dispatchEvent(new CustomEvent('appInstallRequest'))">
            <img src="/img/icons/menu/application.png">
            <span class="menu__item-text">{{ __('menu.app') }}</span>
        </div>

        <a href="https://krakenpartners.net/{{\Illuminate\Support\Facades\App::getLocale()}}" target="_blank" class="menu__item no-ajax">
            <img src="/img/icons/menu3/WPartners.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.forPartners') }}</span>
        </a>

        <a href="{{ route('referralSystem', [], false) }}" target="_blank" class="menu__item @if($route === 'referralSystem') active @endif">
            <img src="/img/icons/menu3/WReferral.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.referralSystem') }}</span>
            <span class="label">$$$</span>
        </a>

    {{--    <a href="{{ route('stock', [], false) }}" class="menu__item @if($route === 'stock') active @endif disabled">--}}
    {{--        <img src="/img/icons/menu/promo.png">--}}
    {{--        <span class="menu__item-text">{{ __('menu.promoStock') }}</span>--}}
    {{--    </a>--}}

    {{--    <a href="{{ route('ranks', [], false) }}" class="menu__item @if($route === 'ranks') active @endif">--}}
    {{--        <img src="/img/icons/menu/statuses.png">--}}
    {{--        <span class="menu__item-text">{{ __('menu.ranks') }}</span>--}}
    {{--    </a>--}}

        <a href="https://t.me/+SRCw4R5vjbjGNXSE" class="menu__item no-ajax" target="_blank">
            <img src="/img/icons/menu3/Wknowledge.svg" class="menu-img">
            <span class="menu__item-text">{{ __('menu.news') }}</span>
        </a>
    {{--    <a href="/faq" class="menu__item @if($route === 'faq') active @endif disabled">--}}
    {{--        <img src="/img/icons/menu/faq.png">--}}
    {{--        <span class="menu__item-text">{{ __('menu.faq') }}</span>--}}
    {{--    </a>--}}
    </div>
    <hr class="menu__line"/>

    <div class="menu-block no-click-hide">
        @include('common.blocks.theme-switcher')
        @include('common.blocks.lang-selector')
    </div>
</nav>
