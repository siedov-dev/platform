<div class="casino-benefits">

    <div class="casino-benefits__item">
        <div class="casino-benefits__icon-wrap">
            <img src="/img/ui/icons/chip-colored.svg" class="casino-benefits__icon">
        </div>
        <div class="casino-benefits__text">
            <span class="casino-benefits__text-yellow">{{ \App\GamesProviders\GamesManager::getTotalGames() }}+</span><br/>
            {{ __('landing.availableGames') }}
        </div>
    </div>

    <div class="casino-benefits__item">
        <div class="casino-benefits__icon-wrap">
            <img src="/img/ui/icons/crown-colored.svg" class="casino-benefits__icon">
        </div>
        <div class="casino-benefits__text">{{ __('landing.uniqueBonuses') }}</div>
    </div>

    <div class="casino-benefits__item">
        <div class="casino-benefits__icon-wrap">
            <img src="/img/ui/icons/graphs-colored.svg" class="casino-benefits__icon">
        </div>
        <div class="casino-benefits__text">{{ __('landing.instantMoneyPayout') }}</div>
    </div>

    <div class="casino-benefits__item">
        <div class="casino-benefits__icon-wrap">
            <img src="/img/ui/icons/suit-colored.svg" class="casino-benefits__icon">
        </div>
        <div class="casino-benefits__text">{{ __('landing.vip') }}</div>
    </div>

</div>
