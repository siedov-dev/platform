<div class="width-limiter">

    <div class="stock__banner">
        <img src="/img/stock/banner_desktop.svg" class="stock__banner-image w-100">
    </div>

    <div class="tabs mt-2">
        <div class="tabs__tab tabs__tab_active" data-target="#tournaments__list-active">Все промоушены</div>
        <div class="tabs__tab" data-target="#tournaments__list-past">Активные</div>
    </div>


    <div id="stock__list-active" class="stock__list mb-2">

        @for($i = 0; $i < 5; $i++)
        <div class="stock__item">
            <div class="stock__item-image-wrap">
                <img src="/img/stock/promo_1.jpg" class="stock__item-image">
            </div>
            <div class="stock__item-texts">
                <div class="stock__item-title">Акция “Плюс минус”</div>
                <div class="stock__item-desc">Акция "Плюс Минус". Акция которая помогает Вам выиграть больше! Перед пополнением нужна...</div>
                <a href="/stock/plus-minus" class="stock__item-button">Подробнее</a>
            </div>
        </div>
        @endfor

    </div>
</div>
