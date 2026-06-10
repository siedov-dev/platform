<?php
/**
 * @var \App\Models\Bonuses\UserBonus[] $bonuses
 * @var \App\Models\UserGift[] $gifts
 */

use App\Models\Bonuses\Foreign\PragPlayBonus;
use Illuminate\Support\Facades\Auth;

$isPromoIncluded = false;

/** @var PragPlayBonus[] $pplay_bonuses */
$pplay_bonuses = PragPlayBonus::getActiveBonuses();
/** @var \App\MUser $user */
$user = Auth::user();

///** @var \App\Models\BonusAvailable[] $available_bonuses */
//$available_bonuses = \App\Models\BonusAvailable::getForUser($user);

//$qt_bonuses = \App\GamesProviders\Qt\Models\QtBonus::where('user_id', $user->id)
//    ->whereIn('status', [\App\GamesProviders\Qt\Models\QtBonus::BONUS_STATUS_NOT_ACTIVATED, \App\GamesProviders\Qt\Models\QtBonus::BONUS_STATUS_ACTIVE])
//    ->where('currency', Auth::user()->currency)
//    ->orderBy('id', 'desc')
//    ->get();
//
//$qt_done_bonuses = \App\GamesProviders\Qt\Models\QtBonus::where('user_id', $user->id)
//    ->whereNotIn('status', [\App\GamesProviders\Qt\Models\QtBonus::BONUS_STATUS_NOT_ACTIVATED, \App\GamesProviders\Qt\Models\QtBonus::BONUS_STATUS_ACTIVE])
//    ->where('end_time', '>=', now()->subMonth()->format('Y-m-d H:i:s'))
//    ->where('currency', Auth::user()->currency)
//    ->orderBy('id', 'desc')
//    ->get();

///** @var \App\Models\Bonuses\Foreign\VegasFreespins[] $vegas_freespins */
//$vegas_freespins = \App\Models\Bonuses\Foreign\VegasFreespins::where('user_id', Auth::id())
//    ->where('currency', Auth::user()->currency)
//    ->where('active_to', '>=', now()->subMonth()->format('Y-m-d H:i:s'))
//    ->orderBy('id', 'desc')
//    ->get();

$bonus_event = \App\Models\BonusEvents\BonusEvent::userInEvent($user);
?>
<div id="cabinet-position" data-position="bonuses"></div>

<div class="tabs">
    <div class="tabs__tab tabs__tab_active" data-target="#cabinet__bonuses">{{ __('cabinet.bonuses') }}</div>
    <div class="tabs__tab" data-target="#cabinet__completed">{{ __('cabinet.completed') }}</div>

    <div class="tabs__right-link use-promocode-btn">
        <img src="/img/cabinet/bonuses/icons/ticket.svg">&nbsp;
        {{ __('cabinet.havePromoCode') }}
    </div>
</div>


<div id="cabinet__bonuses" class="cabinet__bonuses">
    @if($bonus_event !== null)
        @php
            $event = $bonus_event->castByType();
        @endphp
        @include('desktop.cabinet.bonus-event')
    @endif

{{--    @foreach ($pplay_active_bonuses as $bonus_data)--}}
{{--        @php--}}
{{--            $bonus = new \App\Models\Bonuses\Foreign\PragPlayActiveBonus($bonus_data);--}}
{{--        @endphp--}}
{{--        @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $bonus])--}}
{{--    @endforeach--}}

{{--    @foreach ($vegas_freespins as $vegas_fs)--}}
{{--            @php--}}
{{--                $vegas_fs->actualize();--}}
{{--            @endphp--}}
{{--            @if(!$vegas_fs->isCompleted())--}}
{{--                @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $vegas_fs])--}}
{{--            @endif--}}
{{--    @endforeach--}}

{{--    @foreach ($pplay_bonuses as $bonus)--}}
{{--        @php--}}
{{--            $bonus->actualize();--}}
{{--            $bonus = $bonus->getActiveBonus();--}}
{{--        @endphp--}}
{{--        @if($bonus && !$bonus->isCompleted())--}}
{{--            @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $bonus])--}}
{{--        @endif--}}
{{--    @endforeach--}}

{{--    @foreach ($qt_bonuses as $bonus)--}}
{{--        @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $bonus])--}}
{{--    @endforeach--}}

    @foreach ($bonuses as $bonus)
        @php
            $bonus = \App\Models\Bonuses\UserBonus::convertByType($bonus);
            $bonus->actualize();
        @endphp
        @if ($bonus->isCompleted()) @continue @endif
        @include('desktop.cabinet.single-bonus', ['bonus' => $bonus])

        @if (!$isPromoIncluded && $bonus->title === 'bonus.first_dep_bonus'
            && in_array($bonus->status, [\App\Models\Bonuses\UserBonus::STATUS_WAITING_FOR_AGREEMENT, \App\Models\Bonuses\UserBonus::STATUS_NOT_ACTIVATED]))
            @include('desktop.cabinet.bonus-promotion-modal', ['bonus' => $bonus])
            @php $isPromoIncluded = true; @endphp
        @endif
    @endforeach
    @foreach ($available_bonuses as $b_av)
        @php
            $bonus = $b_av->getBonus($user);
        @endphp
        @include('desktop.cabinet.single-bonus', ['bonus' => $bonus])
    @endforeach

    @if(count($pplay_bonuses) === 0
            && count($bonuses) === 0
            && $bonus_event === null
        )
{{--        @if(count($gifts) === 0)--}}
{{--            <div class="no-records">--}}
{{--                <div class="no-records__title">{{ __('cabinet.noBonusesTitle') }}</div>--}}
{{--                <div class="no-records__hint">{{ __('cabinet.noBonusesHint') }}</div>--}}
{{--            </div>--}}
{{--        @endif--}}
    @endif
</div>

<div id="cabinet__completed" class="cabinet__bonuses" style="display: none">
    @foreach ($bonuses as $bonus)
        @php
            $bonus = \App\Models\Bonuses\UserBonus::convertByType($bonus);
            //$bonus->actualize();
        @endphp
        @if (!$bonus->isCompleted()) @continue @endif
        @include('desktop.cabinet.single-bonus', ['bonus' => $bonus])
    @endforeach

    @foreach ($pplay_bonuses as $bonus)
        @if($bonus->isCompleted())
            @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $bonus])
        @endif
    @endforeach

{{--    @foreach ($vegas_freespins as $vegas_fs)--}}
{{--        @if($vegas_fs->isCompleted())--}}
{{--            @include('desktop.cabinet.foreign-single-bonus', ['bonus' => $vegas_fs])--}}
{{--        @endif--}}
{{--    @endforeach--}}

{{--    @foreach ($gifts as $gift)--}}
{{--        @php--}}
{{--            $gift->actualize();--}}
{{--        @endphp--}}
{{--        @include('desktop.cabinet.single-gift', ['gift' => $gift])--}}
{{--    @endforeach--}}

{{--    @if(count($gifts) === 0)--}}
{{--        <div class="no-records">--}}
{{--            <div class="no-records__title">{{ __('cabinet.noGiftsTitle') }}</div>--}}
{{--            <div class="no-records__hint">{{ __('cabinet.noGiftsHint') }}</div>--}}
{{--        </div>--}}
{{--    @endif--}}
</div>

{{--<div class="cabinet__code-form-wrap">--}}
{{--    <div class="cabinet__code-form">--}}
{{--        <div class="cabinet__code-form-close">&times;</div>--}}
{{--        <div class="cabinet__promo-code-title">{{ __('cabinet.havePromoCode') }}</div>--}}

{{--        <div class="cabinet__promo-code-hint">{{ __('cabinet.inputPromoCode') }}</div>--}}

{{--        <form action="{{action('BonusPromocodesController@activate')}}" method="post" id="cabinet__promo-code-form">--}}
{{--            @csrf--}}
{{--            <input class="cabinet__promo-code-input" type="text" name="promocode" placeholder="XXXXX">--}}
{{--            <button type="submit" class="cabinet__promo-code-btn">{{ __('cabinet.activate') }}</button>--}}
{{--        </form>--}}
{{--    </div>--}}
{{--</div>--}}



{{--<script>--}}
{{--    (() => {--}}
{{--        let form = document.getElementById('cabinet__promo-code-form');--}}

{{--        form.addEventListener('submit', async e => {--}}
{{--            e.preventDefault();--}}

{{--            let data = new FormData(form);--}}
{{--            Spinner.show(form.parentNode);--}}

{{--            try {--}}
{{--                let res = await network.post(form.getAttribute('action'), data);--}}
{{--                let msg = await res.text();--}}
{{--                Spinner.hide(form.parentNode, msg);--}}
{{--                form.querySelector('.cabinet__promo-code-input').value = '';--}}

{{--                await delay(1000);--}}
{{--                res = await network.get('/cabinet/bonuses');--}}
{{--                let html = await res.text();--}}
{{--                setInnerHtml('.cabinet__content', html);--}}

{{--                let f = document.querySelector('.cabinet__code-form-wrap');--}}
{{--                let anim = new Anim(250);--}}
{{--                await anim.start(k => {--}}
{{--                    f.style.opacity = (1-k).toString();--}}
{{--                });--}}
{{--                f.style.display = 'none ';--}}

{{--            } catch (msg) {--}}
{{--                Spinner.hide(form.parentNode, msg);--}}
{{--            }--}}
{{--        });--}}
{{--    })();--}}
{{--</script>--}}

