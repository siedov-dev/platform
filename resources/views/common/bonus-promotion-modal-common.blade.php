<?php
use App\Models\BonusCurrencyConfig;
$bonus_exists = true;
?>

@if($checkBonus === true)
    @php
        $bonus_exists = true;
        $bonus_exists = \App\Models\Bonuses\UserBonus::where('title', 'bonus.first_dep_bonus')
            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
            ->wherein('status', [\App\Models\Bonuses\UserBonus::STATUS_WAITING_FOR_AGREEMENT, \App\Models\Bonuses\UserBonus::STATUS_NOT_ACTIVATED])
            ->first();
        if ($bonus_exists !== null) $bonus_exists = \App\Models\Bonuses\UserBonus::convertByType($bonus_exists);
    @endphp
@endif

@if($bonus_exists)
    @php
        /**
     * @var \App\Models\Bonuses\UserBonus $bonus_exists
     */

    $money_k = BonusCurrencyConfig::getMoneyK($bonus_exists->currency);

    @endphp
    <style>
        #promo-modal-common + .spinner .message {
            color: #18e02e;
            text-shadow: black 0 0 2px;
        }
    </style>

<div id="promo-modal-common" style="display: none">
    <img src="/img/bonuses/banner-bg.png" class="promo-modal__bg">
    <div class="promo-modal__wrap">
        <div class="promo-modal__title">{{ __('bonuses.youHaveBonuses') }}</div>

        <div class="promo-modal__banner">
            <div class="promo-modal__down-arrow"></div>
            <img src="/img/bonuses/promo_bonus_banner.png" class="promo-modal__banner-img">
        </div>

        <div class="promo-modal__promo-texts">

            <div class="promo-description">
                <div>{{__('bonuses.minimumDeposit')}} <span class="green-text">{{$bonus_exists->getJsonParam('min_sum')}} {{mb_strtolower(\App\UserWallet::currencyName($bonus_exists->currency))}}:</span></div>
                <div class="green-text">100%+50FS</div>
            </div>
        </div>

        <div data-url="{{action("GetBonusController@activateBonus", ['locale' => app()->getLocale(), 'id' => $bonus_exists->getId()], false)}}" class="bonus__green-btn promo-modal__activate-btn cabinet__bonus-activate-btn">
            {{__('bonus.activate')}}
            <img src="/img/bonuses/red-gift.png" class="promo-modal__activate-icon">
        </div>
        <div class="promo-modal__cancel-btn" onclick="Modal.hide(this)">{{__('bonus.dont_need')}}</div>
    </div>
</div>



<script>
    (() => {
        let promo = document.getElementById('promo-modal-common');
        promo.parentNode.removeChild(promo);
        promo.style.display = 'block';

        document.addEventListener('requestCommonPromoModal', showCommonPromoModal);


        let activateButton = promo.querySelector('.promo-modal__activate-btn');
        activateButton.addEventListener('click', function () {
            setTimeout( () => {
                this.classList.remove('cabinet__bonus-activate-btn');
                this.style.display = "none";
                let cancelBtn = this.parentElement.querySelector('.promo-modal__cancel-btn');
                if (cancelBtn) {
                    cancelBtn.textContent = "{{__('bonus.activated')}}"
                }
                setTimeout(async () => {
                    await Modal.hide(promo);
                    //show deposit form
                    setTimeout(async () => {
                        document.dispatchEvent(new CustomEvent('showDeposit'));
                    }, 500);
                }, 1500);
            }, 1000)
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


        async function showCommonPromoModal() {
            let modal = Modal.show(promo);
            modal.closeButton.classList.add('promo-modal__close-button');
        }

        setTimeout(showCommonPromoModal, 0);

    })();
</script>
@endif
