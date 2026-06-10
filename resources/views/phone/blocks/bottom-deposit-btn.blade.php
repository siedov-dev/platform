@if(\Illuminate\Support\Facades\Auth::check())
<div class="float-bottom-btns">
    <button class="button button_green" onclick="wallet.showDeposit()">
        <img src="/img/ui/icons/wallet.png" class="user-menu-button__image mr-05">
        <div>{{__('wallet.deposit')}}</div>
    </button>
</div>
@endif
