        <div class="popup__content"> <!-- popup__content-image -->
            <h2>{{__('slc.wager_item_title')}}</h2>
            <div class="popup__wrap-text">
                <h3>{{__('slc.description')}}</h3>
                {!! __('slc.description_wager', ['amount' => $item->getAmount()]) !!}
                <h4>{{__('slc.conditions')}}</h4>
                <h4>{!! __('slc.conditions_wager') !!}</h4>
            </div>
        </div>
