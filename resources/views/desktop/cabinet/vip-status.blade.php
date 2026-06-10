<div class="cabinet__bonuses-wrapper">
<div class="cabinet__bonuses-item cabinet__bonus" style='background-image: url("{{$vip_status?->statusInfo?->getImgUrl(\App\Helpers\UI::getTheme())}}");'>

    <div class="cabinet__bonus-time">
        <img src="/img/bonuses/clock.png">
        <div>{{\Carbon\Carbon::parse($vip_status->created_at)->isoFormat('DD MMM')}}-{{\Carbon\Carbon::parse($vip_status->active_to)->isoFormat('DD MMM YYYY')}}</div>
    </div>

    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-title">{{ $vip_status->statusInfo->getName() }}</div>
    </div>

</div>
</div>
