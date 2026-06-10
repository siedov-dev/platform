<div class="page__head">
    <div class="n_container">
        <div class="page__block-go-back-to-mome">
            <img src="/images/svg/go-back-to-mome-icon.svg" alt="">
            <a href="{{route('landing', [], false)}}" class="content-link">{{__('slc.back_to_main')}}</a>
        </div>
        <h1 class="page__title flex">
            {{__('chests.lost_treasures')}}
            <a href="{{route('slc.page-info', ['page' => 'lost_treasures'], false)}}" class="page-info-icon ml-05" data-ajax="modal" data-modal-class="mobile-popup"></a>
        </h1>
    </div>
</div>
<main class="main lost-treasures">
    <div class="flex pl-1 pr-1">
        <div class="tabs">
            <label class="tabs__tab tabs__tab_active" data-target="#treasures-common-tab">
                {{ __('chests.treasure_common') }}
            </label>
            <label class="tabs__tab" data-target="#treasures-legendary-tab">
                {{ __('chests.treasure_legendary') }}
            </label>
        </div>
    </div>
    <div id="treasures-common-tab" class="tabContent">
        <div class="flex">
            @include('desktop.slc.treasures.treasure-tab', ['key_types' => \App\Models\Slc\Chest\ChestKeyType::getCommon(), 'inventory' => $inventory])
        </div>
    </div>
    <div id="treasures-legendary-tab" class="tabContent" style="display: none">
        <div class="flex">
            @include('desktop.slc.treasures.treasure-tab', ['key_types' => \App\Models\Slc\Chest\ChestKeyType::getLegendary(), 'inventory' => $inventory])
        </div>
    </div>
</main>
@include('phone.blocks.bottom-deposit-btn')
