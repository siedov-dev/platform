<div class="popup__content"> <!-- popup__content-image -->
    <div class="popup__wrap-text">
        @include('desktop.bonuses.info_'.$bonus->getDescriptionName(), ['bonus_id' => $bonus->getDescriptionName(), 'in_modal' => true])
    </div>
</div>
