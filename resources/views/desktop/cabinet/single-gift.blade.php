<?php
/** @var $gift \App\Models\UserGift */

$timeProgress = round((time() - strtotime($gift->created_at))/($gift->active_to->timestamp - strtotime($gift->created_at))*100);
?>
<div class="cabinet__gifts-item cabinet__gift">

    <div class="cabinet__bonus-corner">
        <div class="cabinet__bonus-delete-btn" data-url="{{ action('GiftsController@delete', ['gift_id' => $gift->id]) }}"><img src="/img/cabinet/bonuses/icons/delete.svg"></div>
    </div>

    <div class="cabinet__bonus-content">
        <div class="cabinet__bonus-left">
            <div class="cabinet__gift-title">{{ $gift->prize === null ? '' : $gift->prize->description }}</div>

            @if($gift->status === \App\Models\UserGift::STATUS_NEW)
                <div class="cabinet__gift-expire-label">{{__('bonuses.estimated')}}:</div>
                <div class="cabinet__gift-expire-value">{{ $gift->active_to }}</div>
                <div class="cabinet__bonus-progress progress-bar">
                    <div class="progress-bar__track progress-bar__track_green" style="width: {{ $timeProgress }}%"></div>
                </div>
            @elseif($gift->status === \App\Models\UserGift::STATUS_OPEN)
                <div class="cabinet__gift-taken">{{__('bonuses.gift-status_opened')}}</div>
            @endif

        </div>
        <div class="cabinet__bonus-right">

            <div class="cabinet__bonus-image-wrap mt-1">
                <img src="/img/cabinet/bonuses/icons/bonus-in-progress.svg" class="cabinet__bonus-image">
            </div>

            @if($gift->status == \App\Models\UserGift::STATUS_NEW)
                <div class="cabinet__gift-take-btn" data-url="{{ action('GiftsController@activate', ['gift_id' => $gift->id]) }}">{{__('bonuses.gift-take')}}</div>
            @else
                @php
                    $bonus = $gift->getBonus();
                @endphp

                @if($bonus && $bonus->isActive())
                    @if($bonus->isDirectPlayUrl())
                        <div>
                            <div class="game__name" style="display: none">{{$bonus->getGameName()}}</div>
                            <div class="game game__button game__play-real cabinet__bonus-play-btn" data-url="{{ $bonus->getPlayUrl() }}" onclick="Overlay.hide()">{{ __('games.play') }}</div>
                        </div>
                    @else
                        <a href="{{route('games', [], false)}}?category=bonus" data-url="{{route('games', [], false)}}?category=bonus" class="cabinet__bonus-play-btn">{{__('bonus.play')}}</a>
                    @endif
                @endif
            @endif
        </div>
    </div>

</div>

