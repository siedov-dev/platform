<div class=" cabinet__bonuses-item mt-3">
    <div class="cabinet__pad p-2">
        <div class="text-title mt-1">{{__('bonus.have_active_bonus_name', ['name' => $name])}} ({{\App\UserWallet::currencyName($active->currency)}})</div>
        @if($canPause)
            <div class="text-red mt-1">{{__('bonus.confirm_force_pause')}} </div>
        @else
            <div class="text-red mt-1">{{__('bonus.confirm_force_cancel')}} </div>
        @endif
        <div class="flex row jc-sa mt-1">
            <div data-url="{{action("GetBonusController@resumeBonus", ['id' => $bonus_id, 'force' => true], false)}}" class="bonus__green-btn cabinet__bonus-activate-btn bonus_yes_btn" onclick="Modal.hide(this.closest('.modal'))">
                {{__('cabinet.yes')}}
            </div>
            <div class="button button_red mt-1" onclick="Modal.hide(this.closest('.modal'))">
                {{__('cabinet.no')}}
            </div>
        </div>
    </div>
</div>
