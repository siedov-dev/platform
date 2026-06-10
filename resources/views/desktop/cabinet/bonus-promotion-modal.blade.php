<?php
use App\Models\BonusCurrencyConfig;

/**
 * @var \App\Models\Bonuses\UserBonus $bonus
 */

$money_k = BonusCurrencyConfig::getMoneyK($bonus->currency);
?>


<div id="promo-modal" style="display: none">
    <img src="/img/bonuses/banner-bg.png" class="promo-modal__bg">
    <div class="promo-modal__wrap">
        <div class="promo-modal__title">{{ __('bonuses.youHaveBonuses') }}</div>

        <div class="promo-modal__banner">
            <div class="promo-modal__down-arrow"></div>
            <img src="/img/bonuses/promo_bonus_banner.png" class="promo-modal__banner-img">
        </div>

        <div class="promo-modal__promo-texts">
{{--            <div class="promo-description">--}}
{{--                <div>{{__('bonuses.minimumDeposit')}} <span class="green-text">{{100 * $money_k}} {{mb_strtolower(\App\UserWallet::currencyName($bonus->currency))}}:</span></div>--}}
{{--                <div class="green-text">120%+40FS</div>--}}
{{--            </div>--}}

            <div class="promo-description">
                <div>{{__('bonuses.minimumDeposit')}} <span class="green-text">{{$bonus->getJsonParam('min_sum')}} {{mb_strtolower(\App\UserWallet::currencyName($bonus->currency))}}:</span></div>
                <div class="green-text">100%+50FS</div>
            </div>
        </div>

        <div class="bonus__green-btn promo-modal__activate-btn">
            {{__('bonus.activate_bonus')}}
            <img src="/img/bonuses/red-gift.png" class="promo-modal__activate-icon">
        </div>
        <div class="promo-modal__cancel-btn" onclick="Modal.hide(this)">{{__('bonus.dont_need')}}</div>
    </div>
</div>



<script>
    (() => {
        let promo = document.getElementById('promo-modal');
        promo.parentNode.removeChild(promo);
        promo.style.display = 'block';

        document.addEventListener('requestPromoModal', showPromoModal);


        let activateButton = promo.querySelector('.promo-modal__activate-btn');
        activateButton.addEventListener('click', () => {
            let btn = document.querySelector('.cabinet__bonuses-item[data-id="{{ $bonus->id }}"] .cabinet__bonus-activate-btn');
            if (btn !== null) {
                btn.click();
            }
            Modal.hide(activateButton);
        });

        let arrowButton = promo.querySelector('.promo-modal__down-arrow');
        let promoImg =  promo.querySelector('.promo-modal__banner');

        promoImg.addEventListener('click', function() {
            let texts = promo.querySelector('.promo-modal__promo-texts');
            let arrow = promo.querySelector('.promo-modal__promo-texts');
            if(texts.classList.contains('expanded')) {
                texts.classList.remove('expanded');
                arrowButton.classList.remove('expanded');
            } else {
                texts.classList.add('expanded');
                arrowButton.classList.add('expanded');
            }
        });


        async function showPromoModal() {
            let modal = Modal.show(promo);
            modal.closeButton.classList.add('promo-modal__close-button');
        }

        @if(!request()->get('nomodals') == 1)
            setTimeout(showPromoModal, 0);
        @endif    })();
</script>
