<div class="cabinet__code-form-wrap">
    <div class="cabinet__code-form">
        <img src="/img/bonuses/promo-bg.png" class="promo-bg">
        <div class="cabinet__code-form-close">&times</div>
{{--        <div class="cabinet__promo-code-icon">--}}
{{--            <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" alt="" class="cabinet__promo-code-image">--}}
{{--        </div>--}}

        <form action="{{action('BonusPromocodesController@activate')}}" method="post" id="cabinet__promo-code-form">
            @csrf
            <div class="cabinet__promo-code-title">{{ __('cabinet.havePromoCode') }}</div>
            <div class="flex w-100 jc-fe mr-1">
                <a href="{{route('slc.page-info', ['page' => 'promocode'], false)}}" class="page-info-icon mr-1" data-ajax="modal"></a>
            </div>
            <input class="cabinet__promo-code-input" type="text" name="promocode" placeholder="XXXXX">
            <div class="cabinet__promo-code-hint">{{ __('cabinet.inputPromoCode') }}</div>
            <button type="submit" class="button button_green cabinet__promo-code-btn">{{ __('cabinet.activate') }}</button>
        </form>
    </div>
</div>

<script>
    (() => {
        let form = document.getElementById('cabinet__promo-code-form');

        form.addEventListener('submit', async e => {
            e.preventDefault();

            let data = new FormData(form);
            Spinner.show(form.parentNode);

            try {
                let res = await network.post(form.getAttribute('action'), data);
                let msg = await res.text();
                Spinner.hide(form.parentNode, msg);
                form.querySelector('.cabinet__promo-code-input').value = '';

                await delay(2000);
                form.parentElement.querySelector('.cabinet__code-form-close').click();
                if (res.headers.get('show_deposit') !== null) {
                    let event = new CustomEvent('click:wallet-selector__plus-button');
                    document.dispatchEvent(event);
                }
                // document.location.href = '/cabinet/bonuses';

                // res = await network.get('/cabinet/bonuses');
                // let html = await res.text();
                // setInnerHtml('.cabinet__content', html);

            } catch (msg) {
                Spinner.hide(form.parentNode, msg);
            }
        });
    })();
</script>
