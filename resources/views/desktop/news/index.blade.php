<div class="width-limiter">
    <div class="bonuses__banner-wrap">
        <img src="/img/news/banner_desktop.svg" class="bonuses__banner">
    </div>

    <div class="bonuses__list">
        <div class="bonuses__list-title">Последние новости</div>

        <div class="bonuses__items">

            @for ($i = 0; $i < 10; $i++)
            <div class="bonuses__item">
                <img src="/img/news/article_1.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-date">15 Января</div>
                    <div class="bonuses__item-title">Кешбек без депозиа</div>
                    <div class="bonuses__item-desc">Мы добавили самого популярного провайдера в интернете.</div>
                    <a href="/news/test"data-ajax="#layout-content"  class="bonuses__item-button w-100">Читать полностью</a>
                </div>
            </div>
            <div class="bonuses__item">
                <img src="/img/news/article_2.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-date">15 Января</div>
                    <div class="bonuses__item-title">150% без депозита</div>
                    <div class="bonuses__item-desc">150% бездепозитный бонус можно активировать в случае тра ляля...</div>
                    <a href="/news/test"data-ajax="#layout-content"  class="bonuses__item-button w-100">Читать полностью</a>
                </div>
            </div>
            <div class="bonuses__item">
                <img src="/img/news/article_3.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-date">15 Января</div>
                    <div class="bonuses__item-title">150% без депозита</div>
                    <div class="bonuses__item-desc">150% бездепозитный бонус можно активировать в случае тра ляля...</div>
                    <a href="/news/test"data-ajax="#layout-content"  class="bonuses__item-button w-100">Читать полностью</a>
                </div>
            </div>
            @endfor

        </div>
    </div>
</div>
