<div class="bonus-info-wrapper no-click-hide">
    @if($bonus->canPause())
        <div class="cabinet__bonus-pause-btn bonus-info-action-btn"
             data-url="{{ action("GetBonusController@pauseBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}"
             data-question="{{__('bonus.pause')}}?">
            <img src="/img/cabinet/bonuses/icons/pause-button.svg">{{__('bonus.pause')}}
        </div>
    @endif
    @if($bonus->isCancelable())
        <div class="cabinet__bonus-delete-btn bonus-info-action-btn"
            data-url="{{ action("GetBonusController@cancelBonus", ['id' => $bonus->getId()?? $bonus->getSubId()], false) }}"
            data-question="{{__('bonus.delete')}}?">
            <img src="/img/cabinet/bonuses/icons/delete.svg">{{__('bonus.delete')}}
        </div>
    @endif
    <div class="bonus-info-content">
        {!! $bonus->getShortDescription(); !!}
    </div>
    <div class="flex full-wide-btn p-1">
        {!! $bonus->getRenderer()->renderPlayButton() !!}
    </div>
</div>
