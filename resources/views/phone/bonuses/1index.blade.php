<div class="width-limiter">
    <div class="bonuses__banner-wrap">
        <img src="/img/bonuses/banner_desktop.svg" class="bonuses__banner">
    </div>

    <div class="bonuses__list">
        <div class="bonuses__list-title">Доступные бонусы</div>

        <div class="bonuses__items">

            <!-- First deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/150_dep.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Первый депозит</div>
                    <div class="bonuses__item-desc">Получи к первому депозиту 150%+50FS от 100р./50грн.</div>
                    <a href="{{ route('bonuses.info', ['id' => 'first-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Second deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/100_dep.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Второй депозит</div>
                    <div class="bonuses__item-desc">Получи к второму депозиту 100%+35FS от 500р./250грн.</div>
                    <a href="{{ route('bonuses.info', ['id' => 'second-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Third deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/50_dep.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Третий депозит</div>
                    <div class="bonuses__item-desc">Получи к третьему депозиту 50%+40FS от 500р./250грн.</div>
                    <a href="{{ route('bonuses.info', ['id' => 'third-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Active cashback -->
            <div class="bonuses__item">
                <img src="/img/bonuses/cashback.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Активный Cashback</div>
                    <div class="bonuses__item-desc">Получай до 10% возврата живыми деньгами с каждого прокрута</div>
                    <a href="{{ route('bonuses.info', ['id' => 'active-cashback'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Plus/Minus -->
            <div class="bonuses__item">
                <img src="/img/bonuses/plus_minus.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Плюс/Минус</div>
                    <div class="bonuses__item-desc">Для выигрыша вам нужно просто удвоить бонусный баланс.</div>
                    <a href="{{ route('bonuses.info', ['id' => 'plus-minus'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Super free spins -->
            <div class="bonuses__item">
                <img src="/img/bonuses/fs_wager.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Фриспины с выбором вейджера</div>
                    <div class="bonuses__item-desc">Приятный бонус с выбором вейджера</div>
                    <a href="{{ route('bonuses.info', ['id' => 'super-free-spins'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Free spins + Cashback -->
            <div class="bonuses__item">
                <img src="/img/bonuses/fs_cashback.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Фриспины + Cashback</div>
                    <div class="bonuses__item-desc">Приятный бонус с выбором вейджера + CashBack</div>
                    <a href="{{ route('bonuses.info', ['id' => 'free-spins-cashback'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Filthy free spins -->
            <div class="bonuses__item">
                <img src="/img/bonuses/filty_fs.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">"Грязные" фриспины</div>
                    <div class="bonuses__item-desc">Фриспины с отыгрышем на собственные средства</div>
                    <a href="{{ route('bonuses.info', ['id' => 'filthy-free-spins'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Non-deposit -->
            <!--<div class="bonuses__item">
                <img src="/img/bonuses/bonus_2.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Бездепозит</div>
                    <div class="bonuses__item-desc">150% бездепозитный бонус можно активировать в случае тра ляля...</div>
                    <a href="{{ route('bonuses.info', ['id' => 'non-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>-->

            <!-- Active non-deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/activ_bezdep.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Активный бездепозит</div>
                    <div class="bonuses__item-desc">Бездепозитный бонус с возможностью выбора вейджера</div>
                    <a href="{{ route('bonuses.info', ['id' => 'active-non-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/deposit_bonus.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Депозитный бонус</div>
                    <div class="bonuses__item-desc">Внеси депозит и получи больше</div>
                    <a href="{{ route('bonuses.info', ['id' => 'deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Stock: Drummatic Plus/Minus -->
            <div class="bonuses__item">
                <img src="/img/bonuses/event_plusminus.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Акция: Плюс/Минус</div>
                    <div class="bonuses__item-desc">Получайте х2 от суммы активации и выигрывайте больше</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-drummatic-plus-minus'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Stock: Cashback -->
            <div class="bonuses__item">
                <img src="/img/bonuses/event_cashback.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Акция: Cashback</div>
                    <div class="bonuses__item-desc">Возвращай средства без вейджера.</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-cashback'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Stock: Deposit -->
            <div class="bonuses__item">
                <img src="/img/bonuses/event_deposit.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Акция: Депозит</div>
                    <div class="bonuses__item-desc">Внеси депозит и получи больше</div>
                    <a href="{{ route('bonuses.info', ['id' => 'stock-deposit'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- QT: Free spins -->
            <div class="bonuses__item">
                <img src="/img/bonuses/fs_no_wager.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">FreeSpins без Вейджера</div>
                    <div class="bonuses__item-desc">Крути и сразу забирай весь выигрыш</div>
                    <a href="{{ route('bonuses.info', ['id' => 'qt-free-spins'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Deposit + Free Spins + Tournament -->
            <div class="bonuses__item">
                <img src="/img/bonuses/dep_fs_tournament.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Депозит + Фриспины + Турнир</div>
                    <div class="bonuses__item-desc">150% бездепозитный бонус можно активировать в случае тра ляля...</div>
                    <a href="{{ route('bonuses.info', ['id' => 'deposit-free-spins-tournament'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>

            <!-- Pragmatic Play: Free spins -->
            <!--<div class="bonuses__item">
                <img src="/img/bonuses/bonus_2.jpg" class="bonuses__item-image">
                <div class="bonuses__item-info">
                    <div class="bonuses__item-title">Pragmatic Play: Фриспины</div>
                    <div class="bonuses__item-desc">150% бездепозитный бонус можно активировать в случае тра ляля...</div>
                    <a href="{{ route('bonuses.info', ['id' => 'pragmatic-play-free-spins'], false) }}" class="bonuses__item-button mr-auto">Подробнее</a>
                </div>
            </div>-->

        </div>
    </div>
</div>
