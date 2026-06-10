<style>
    .popup__choose-bonus{
        max-width: clamp(20rem, 100%, 25rem);
        background: #2B2E61;
        padding: 2rem;
        border-radius: 1rem;
    }
    .bonus-list{
        display: flex;
        flex-flow: column;
    }
    .bonus-list > * {
        border-bottom: #444444 1px solid;
    }
</style>
<div class="popup__choose-bonus no-click-hide">
    <div>
        @if(empty($bonuses))
            <div class="no-bonuses__block-image wrap-lazy-load-image">
                <img src="/images/no-bonuses-image.png" alt="">
            </div>
            <div class="no-bonuses__block-text">
                <h3>{{__('slc.no_bonuses_title')}}</h3>
                <p>{{__('slc.no_deposit_bonuses')}}</p>
            </div>
        @else
            <form action="{{route('slc.activate-item', [], false)}}" method="POST" class="wallet-asset">
                @csrf
                <input type="hidden" name="item_id" value="{{$item->getid()}}">
                <h2 class="modal-title">{{__('slc.chose_bonus')}}</h2>
                <div class="bonus-list mt-2">
                    @foreach($bonuses as $bonus)
                        @php
                            $min_wager = $bonus->getJsonParam("min_wager");
                            $current_wager = $bonus->getJsonParam("wager");
                            $item_amount = $item->getAmount();
                            $can_sub = $current_wager - $min_wager;
                            if ($can_sub > $item_amount) $can_add = $item_amount;
                        @endphp
                        <div class="flex" onclick="this.querySelector('.custom-radio input').click()">
                            <div class="flex column grow">
                                <div class="asset-name flex column">
                                    <div>{{$bonus->getName()}}</div>
                                </div>
                                <div class="">
                                    <span>wager {{ $current_wager }} - {{$can_sub}}</span>
                                </div>
                            </div>
                            <div class="asset-value flex">
                                <div class="custom-radio ml-1">
                                    <input name="bonus_id" type="radio" value="{{$bonus->getId()}}" required>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="achievements__popup-button close-popup">{{__('slc.apply')}}</button>
            </form>
        @endif
    </div>
</div>
