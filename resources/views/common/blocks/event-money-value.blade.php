<div class="cabinet__bonuses-item mt-2" action="{{action("GetBonusController@activateEvent")}}" method="POST" style="background-color: #0c0c30;">
    @csrf
    <div class="text-gray mt-1 title">{{__('bonus.activation_sum')}}</div>
    <label class="input__wrap">
        <input class="input__field mt-1" type="number" step="1" value="" name="user_data">
    </label>
    <div class="flex row jc-sb mt-1">
        <duv type="submit" class="cabinet__event-activate-btn" data-url="{{action("GetBonusController@activateEvent",  ['event_id' => $event_id])}}">
            {{__('bonus.activate')}}
        </duv>
        <div class="button button_red mt-1" onclick="Modal.hide(this.closest('.modal')); return false;">
            {{__('cabinet.no')}}
        </div>
    </div>
</div>
