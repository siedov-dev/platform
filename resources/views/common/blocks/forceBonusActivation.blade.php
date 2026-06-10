<div class=" cabinet__bonuses-item mt-3">
    <div class="cabinet__pad p-2">
        <div class="text-title mt-1">{{__('bonus.have_active_bonus_name', ['name' => $name])}} ({{\App\UserWallet::currencyName($active->currency)}})</div>
        @if($canPause)
            <div class="text-red mt-1">{{__('bonus.confirm_force_pause')}} </div>
        @else
            <div class="text-red mt-1">{{__('bonus.confirm_force_cancel')}} </div>
        @endif
        <div class="flex row jc-sa mt-1">
            @if (!empty($option))
                <div class="bonus__green-btn cabinet__bonus-menu-item bonus_yes_btn" data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus_id, 'option' => $option, 'force' => true], false)}}"
                     onclick="Modal.hide(this.closest('.modal')); Modal.hide(document.querySelector('.modal .cabinet__bonus-menu').closest('.modal'))">
                    {{__('cabinet.yes')}}
                </div>
            @else
                <div data-url="{{action("GetBonusController@activateBonus", ['id' => $bonus_id, 'force' => true, 'option' => $option], false)}}" class="bonus__green-btn cabinet__bonus-activate-btn bonus_yes_btn" onclick="Modal.hide(this.closest('.modal'))">
                    {{__('cabinet.yes')}}
                </div>
            @endif
            <div class="button button_red mt-1" onclick="Modal.hide(this.closest('.modal'))">
                {{__('cabinet.no')}}
            </div>
        </div>
    </div>
</div>
