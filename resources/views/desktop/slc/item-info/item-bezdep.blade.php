<div class="popup__content"> <!-- popup__content-image -->
    <h2>{{__('slc.bezdep_item_title')}}</h2>
    <div class="popup__wrap-text">
        <h3>{{__('slc.description')}}</h3>
        {!! __('slc.description_bezdep', ['amount' => $item->getAmount()]) !!}
        <h4>{{__('slc.conditions')}}</h4>
        <h4>{!! __('slc.conditions_deposit') !!}</h4>
    </div>
</div>
