<div class="search-menu bottom-modal">
    <div class="content">
        <div class="pad">
            <h3 class="custom-select__value mb-2">{{ __('footer.games') }}</h3>

            <form action="{{ route('games.search', [], false) }}" method="GET" id="bottom-search-form" class="games-search-form no-click-hide">
                <input type="hidden" name="pageSize" value="{{ $pageSize?? 20 }}">
                <label class="input__wrap games-search-form__name">
                    <img src="/img/icons/Search.png" class="input__icon_left">
                    <input type="text" name="name" class="input__field" placeholder="{{ __('games.findGame') }}..." value="">
                </label>
            </form>

            <div class="games-count">
            </div>

            <div class="games">
            </div>
        </div>
    </div>

    <div class="bottom-menu__close-button">&times;</div>
</div>

<script>
    (() => {
        let form = document.getElementById('bottom-search-form');
        let input = form.querySelector('.games-search-form__name');
        let gamesWrap = document.querySelector('.search-menu .games');

        input.addEventListener('input', () => {
            document.dispatchEvent(new CustomEvent('games:searchGame'));
        });

        form.addEventListener('submit', e => {
            e.preventDefault();
            document.dispatchEvent(new CustomEvent('games:searchGame'));
        });

        gamesWrap.addEventListener('scroll', () => {
            if (gamesWrap.offsetHeight + gamesWrap.scrollTop + 800 >= gamesWrap.scrollHeight) document.dispatchEvent(new CustomEvent("games:searchGameNext"));
        });
    })();
</script>
