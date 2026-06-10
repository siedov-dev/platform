@php
/** @var \App\Models\Bonuses\KUserBonus $bonus */
@endphp
<div class="bonus-lane">
    <img src="/img/icons/bonus-circle.png" alt="bonus" width="26px" height="26px">
    <div class="balance">
        <div class="balance-name">{{$bonus->getBalanceName()}}</div>
        <div>
            @include('common.blocks.money-value', ['amount' => $bonus->getBalanceString()])
        </div>
    </div>
    @if($bonus->isAgreementRequired())
        <div class="bonus-btn"  onclick="this.closest('#bonus-lane').parentElement.querySelector('.bonuses-link').click()">
            <img src="/img/icons/gift-icon.png">&nbsp;{{__('bonuses.get')}}
        </div>
    @else
        <div class="bonus-progress" onclick="this.closest('#bonus-lane').parentElement.querySelector('.bonuses-link').click()">
            <div class="progress" style="width: {{$bonus->getProgressPercents()}}%"></div>
            <div class="text">
                @if($bonus->isProgressAvailable())
{{--                    <div>{{__('bonuses.played')}}</div>--}}
                    <div>{{$bonus->getProgressHint()}}</div>
                @else
                    <div>{{$bonus->getStatusName()}}</div>
                @endif
            </div>
        </div>
    @endif
</div>
