<div class="welcome-bonus-wrapper">
    <div class="banner-img">
        <img src="/img/banners/wb-bnr.png">
        <div class="ext-info-btn" onclick="this.parentElement.parentElement.classList.toggle('shown')">
            <img src="/img/ui/icons/arrow-up-whitebg.png">
        </div>
    </div>
    <div class="ext-info">
        <div class="info">
            <div>{!! __('landing.welcomeBonus') !!}
            </div>
            <a href="/ru/register" data-ajax="modal" class="auth-buttons__reg">{{__('landing.regAndTakeBonus')}}</a>
        </div>
    </div>
</div>
