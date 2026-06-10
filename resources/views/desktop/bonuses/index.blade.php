<div class="width-limiter">
    <div class="bonuses__banner-wrap">
        <img src="/img/banners/welcome_offer_{{ app()->getLocale() }}.webp" class="bonuses__banner">
    </div>

    <div class="bonuses__list">
        <div class="bonuses__list-title">{{ __('bonuses.availableBonuses') }}</div>

        <div class="bonuses__items">
            
            <!-- 375% Приветственный пакет -->
        <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => '375procent'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/banners/welcome_offer_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.375_procent') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.375_procent1') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => '375procent'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- First deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'first-deposit-sw'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/150_dep_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.firstDepName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.firstDepDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'first-deposit-sw'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Second deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'second-deposit-sw'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/100_dep_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.secondDepName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.secondDepDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'second-deposit-sw'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Third deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'third-deposit-sw'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/50_dep_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.thirdDepName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.thirdDepDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'third-deposit-sw'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Active cashback -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'active-cashback'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/cashback_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.activeCashBackName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.activeCashBackDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'active-cashback'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Plus/Minus -->
            <!--<div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'plus-minus'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/plus_minus_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.plusMinusName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.plusMinusDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'plus-minus'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>-->

            <!-- Super free spins -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'super-free-spins'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/fs_wager_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.wagerFSName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.wagerFSDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'super-free-spins'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Free spins + Cashback -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'free-spins-cashback'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/fs_cashback_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.fsPlusCashBackName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.fsPlusCashBackDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'free-spins-cashback'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Filthy free spins -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'filthy-free-spins'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/filty_fs_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.dirtyFSName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.dirtyFSDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'filthy-free-spins'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Birthday -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'birthday'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/banners/birthday_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.birthdayName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.birthdayDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'birthday'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Non-deposit -->
        <!--<div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'non-deposit'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/activ_bezdep_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.nonDepositName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.nonDepositDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'non-deposit'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>-->

            <!-- Active non-deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'active-non-deposit'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/activ_bezdep_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.activeNonDepName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.activeNonDepDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'active-non-deposit'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'deposit'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/deposit_bonus_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.depBonusName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.depBonusDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'deposit'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Stock: Drummatic Plus/Minus -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'stock-drummatic-plus-minus'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/event_plusminus_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.stockPlusMinusName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.stockPlusMinusDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-drummatic-plus-minus'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Stock: Cashback -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'stock-cashback'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/event_cashback_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.stockCashBackName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.stockCashBackDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-cashback'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- Stock: Deposit -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'stock-deposit'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/event_deposit_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.stockDepositName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.stockDepositDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-deposit'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- QT: Free spins -->
            <!--<div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'qt-free-spins'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/fs_no_wager_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.fsWithoutWagerName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.fsWithoutWagerDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'qt-free-spins'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>-->

            <!-- Deposit + Free Spins + Tournament -->
            <!--<div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'deposit-free-spins-tournament'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/dep_fs_tournament_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.depPlusFSPlusTournamentName') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.depPlusFSPlusTournamentDesc') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'deposit-free-spins-tournament'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>-->

            <!-- Vegas freespins stock -->
            <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'stock-vegas-freespins'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/vegas_fs_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('event-6.name') }}</div>
                    <div class="bonuses__item-desc">{{ __('event-6.description2') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-vegas-freespins'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>

            <!-- smart-wager -->
        <div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'smart-wager'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/smart-wager_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonuses.smart-wager') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.smart-wager1') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'smart-wager'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>
            </div>
            

            <!-- Vegas freespins -->
        <!--<div class="bonuses__item">
                <a href="{{ route('bonuses.info', ['id' => 'vegas-freepins'], false) }}" class="bonuses__item-image-wrap">
                    <img src="/img/bonuses/vegas_fs_{{ app()->getLocale() }}.webp" class="bonuses__item-image">
                </a>
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">{{ __('bonus.vegas_fs_name') }}</div>
                    <div class="bonuses__item-desc">{{ __('bonuses.vegas_fs_description') }}</div>
                    <a href="{{ route('bonuses.info', ['id' => 'vegas-freespins'], false) }}" class="bonuses__item-button mr-auto">{{ __('bonuses.moreDetails') }}</a>
                </div>-->
        </div>

    </div>
</div>
