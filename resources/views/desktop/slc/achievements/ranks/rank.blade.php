<div class="rank-card rank-{{$rank_level}} @if($selected) selected @endif @if($user->getRank()->getLevel() === $rank_level) current @endif">
    <div class="veil"></div>
    <div class="current-rank">{{__('ranks.yourRank')}}</div>
    <img class="rank-bg"  src="/img/ui/ranks/rank-bg.png" alt="bg">
    <div class="rank-info">
        <img class="rank-icon" src="/img/ui/ranks/rank-{{$rank_level}}.png" alt="rank">
        <div class="rank-name mt-1">{{__('ranks.rank_'.$rank_level)}}</div>
        @if($user->getRank()->getLevel() <= $rank_level)
            <p class="mt-1">{{__('ranks.bets')}}</p>
            @if($user->getRank()->getLevel() < $rank_level)
                <div class="mt-05"><span class="current-value">{{__('ranks.from')}} {{number_format($rank->getStartValue(), 0, '.', ',')}} {{$user->getCurrencyName()}}</span></div>
            @else
                <div class="goal-values"><span class="current-value">{{number_format($user->getRankValue(), 0, '.', ',')}}
                        {{$user->getCurrencyName()}}</span>/<span>{{number_format($rank->getEndValue(), 0, '.', ',')}} {{$user->getCurrencyName()}}</span>
                </div>
            @endif
            @if($user->getRank()->getLevel() === $rank_level)
                <div class="achievements__popup-progress-bar mt-05">
                    <div class="achievements__popup-progress" style="width: {{$rank->getNextRankProgress($user->getRankValue($currency), $currency)}}%"></div>
                </div>
            @endif
        @endif

    </div>
    <div class="rewards relative @if($user->getRank()->getLevel() >= $rank_level && !$rank->isRewarded($user, $user->getCurrency())) highlighted @endif">
        <div class="title mb-1">{{__('ranks.level_rewards')}}</div>
{{--        <div class="scroll-control left prev-reward-button">--}}
{{--            <div class="arrow-left"></div>--}}
{{--        </div>--}}
{{--        <div class="scroll-control right next-reward-button">--}}
{{--            <div class="arrow-right"></div>--}}
{{--        </div>--}}
        <div class="items mt-05 scroll-hidden" onscroll="checkRewardsScroll(this)">
            @php
            $items = [];
            $i = 2;

            $items[] = [
                'p' => $i,
                'i' => '
                <div class="reward-item">
                    <div class="title">'.__('ranks.cashback').'</div>
                    <img src="/img/cashback/Barrel.svg">
                    <div class="amount">+'.$rank->getWeeklyCashBackPrc().'%</div>
                </div>
            ',
            ];
            if ($rank->isVip()) {
                $i++;
                $items[] = [
                'p' => $i,
                'i' => '
               <div class="reward-item">
                    <div class="title">'.__('ranks.vip').'</div>
                    <img src="/img/ui/ranks/vip.svg">
                    <div class="amount"></div>
                </div>
            ',
            ];
            }
            if ($rank->isSecretPrize()) {
                $i++;
                $items[] = [
                'p' => $i,
                'i' => '
                <div class="reward-item">
                    <div class="title">'.__('ranks.secret_prize').'</div>
                    <img src="/img/ui/ranks/secret_prize.svg">
                    <div class="amount"></div>
                </div>
                ',
                ];
            }
            foreach($rank->getRewards() as $reward) {
                $i++;
                $items[] = [
                'p' => $i,
                'i' => '
                <div class="reward-item">
                    <div class="title">'.$reward->getInfo()->name(false).'</div>
                    <div style="width: 3rem; height: 3rem;">
                        <div class="scale-wrapper scale-06">
                            '. $reward->getInfo()->renderItemIcon() .'
                        </div>
                    </div>
                    <div class="amount">'.($reward->getInfo()->isStackable()? $reward->getAmount() :'').'</div>
                </div>
                ',
                ];
            }

            foreach(\App\Models\Rank\UserRankPromoItem::getItemsForLevel($rank_level) as $item) {
                $items[] = [
                'p' => $item->getPriority(),
                'i' => '
                    <div class="reward-item">
                        <div class="title">'. $item->getTitle(app()->getLocale()) .'</div>
                        <img src="'.$item->getImg().'">
                        <div class="amount">'. $item->getValue(app()->getLocale()) .'</div>
                    </div>
                ',
                ];
            }

            usort($items, function ($a, $b) {return $a['p'] - $b['p'];})
            @endphp
            @foreach($items as $item)
                {!! $item['i'] !!}
            @endforeach
{{--            <div class="reward-item">--}}
{{--                <div class="title">{{__('ranks.cashback')}}</div>--}}
{{--                <img src="/img/cashback/Barrel.svg">--}}
{{--                <div class="amount">+{{$rank->getWeeklyCashBackPrc()}}%</div>--}}
{{--            </div>--}}
{{--            @if($rank->isVip())--}}
{{--                <div class="reward-item">--}}
{{--                    <div class="title">{{__('ranks.vip')}}</div>--}}
{{--                    <img src="/img/ui/ranks/vip.svg">--}}
{{--                    <div class="amount"></div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--            @if($rank->isSecretPrize())--}}
{{--                <div class="reward-item">--}}
{{--                    <div class="title">{{__('ranks.secret_prize')}}</div>--}}
{{--                    <img src="/img/ui/ranks/secret_prize.svg">--}}
{{--                    <div class="amount"></div>--}}
{{--                </div>--}}
{{--            @endif--}}
{{--            @foreach($rank->getRewards() as $reward)--}}
{{--                <div class="reward-item">--}}
{{--                    <div class="title">{{$reward->getInfo()->name(false)}}</div>--}}
{{--                    <div style="width: 3rem; height: 3rem;">--}}
{{--                        <div class="scale-wrapper scale-06">--}}
{{--                            {!! $reward->getInfo()->renderItemIcon() !!}--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="amount">@if($reward->getInfo()->isStackable()) {{$reward->getAmount()}} @endif</div>--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--            @foreach(\App\Models\Rank\UserRankPromoItem::getItemsForLevel($rank_level) as $item)--}}
{{--                <div class="reward-item">--}}
{{--                    <div class="title">{!! $item->getTitle(app()->getLocale()) !!}</div>--}}
{{--                    <img src="{{$item->getImg()}}">--}}
{{--                    <div class="amount">{!! $item->getValue(app()->getLocale()) !!}</div>--}}
{{--                </div>--}}
{{--            @endforeach--}}
        </div>
        @if($user->getRank()->getLevel() >= $rank_level)
            @if ($rank->isRewarded($user, $user->getCurrency()))
                <button class="button button_gray disabled">{{__('ranks.rewarded')}}</button>
            @else
                <form class="w-100" action="{{route('ranks.reward', [], false)}}" method="POST">
                    @csrf
                    <input type="hidden" name="level" value="{{$rank_level}}">
                    <input type="hidden" name="currency" value="{{$user->getCurrency()}}">
                    <button class="button button_green">{{__('ranks.get_reward')}}</button>
                </form>
            @endif
        @endif
    </div>
</div>
