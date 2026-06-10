@php
    /** @var $bonus \App\Models\Bonuses\WelcomeBonusPack */
    /** @var \App\Models\BonusPatterns $pattern */
    $dep_n = $bonus->tries;
    $curr_name = $bonus->getCurrencyName();

    $pattern = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, $dep_n);

    $dep_mult = $pattern->getBonusParam('multiplier', $curr_name);
    $wager = $pattern->getBonusParam('wager', $curr_name);
    $min_dep = $pattern->getBonusParam('min_sum', $curr_name);
    $max_dep = $pattern->getBonusParam('max_sum', $curr_name);
    $fs_sum = $pattern->getBonusParam('fs_dep_sum', $curr_name);

    $fs_icon_position = ($fs_sum / ($max_dep - $min_dep)) * 100;

    $pattern_fd_dep_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 1);
    $pattern_fd_dep_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 2);
    $pattern_fd_dep_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_DEP, 3);

    $pattern_fd_fs_1 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 1);
    $pattern_fd_fs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 2);
    $pattern_fd_fs_3 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 3);

    $pattern_fd_ndfs_1 =\App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 4);
    $pattern_fd_ndfs_2 = \App\Models\BonusPatterns::getRegPattern($curr_name, \App\Models\BonusPatterns::RB_TYPE_FS, 5);

    $total_money = $pattern_fd_dep_1->getBonusParam('max_sum', $curr_name, 0);
    $total_money += $pattern_fd_dep_2->getBonusParam('max_sum', $curr_name, 0);
    $total_money += $pattern_fd_dep_3->getBonusParam('max_sum', $curr_name, 0);

    $total_fs = $pattern_fd_fs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_2->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_fs_3->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
    $total_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    $nd_fs = $pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0);
    $nd_fs += $pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0);

    $steps = [
        'RUB' => 100,
        'UAH' => 50,
        'INR' => 100,
        'KZT' => 100,
        'EUR' => 2,
        'USDT' => 2,
        ];
    $step = $steps[$curr_name]?? 1;

    $spins = match (intval($dep_n)) {
        1 => $pattern_fd_fs_1->getBonusParam('freespins', $curr_name, 0),
        2 => $pattern_fd_fs_2->getBonusParam('freespins', $curr_name, 0),
        3 => $pattern_fd_fs_3->getBonusParam('freespins', $curr_name, 0),
    };
    $nd_disabled = false;
@endphp
<div class="bonuses__wb-wrapper no-click-hide">
    <div class="cabinet__bonus-title">{{__('bonuses.welcome_bonus')}}</div>
        <a class="conditions" href="{{route('bonus.popup-info', ['id' => $bonus->getId(), 'sub_id' => $bonus->getSubId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">
            <div>{{__('bonus.info')}}</div>
        </a>
{{--    <a class="conditions" href="{{route('wb.cond', ['bonus_id' => $bonus->getId()], false)}}" data-ajax="modal" data-popup-type="bottom-popup">--}}
{{--        <img src="/img/bonuses/cond.svg" alt="conditions">--}}
{{--        &nbsp;{{__('bonus.conditions')}}--}}
{{--    </a>--}}
    <p class="mt-1">{{$total_money}} {{$curr_name}} + {{$total_fs}} FS</p>
{{--    <p class="mt-1">{{__('bonuses.welcome_bonus-ndfs', ['ndfs' => $nd_fs])}}</p>--}}
    <p class="mt-1">{{__('bonuses.welcome_bonus-fsc')}}</p>
    <div class="w-100 line-through"></div>
    <div class="deposits-wrapper scroll-hidden">
        @if($dep_n > 1)
            @if(empty($users_fs_bonuses[0]))
                @php $nd_disabled = true @endphp
                <div class="dep canceled">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-canceled.svg"></div>
                </div>
            @else
                <div class="dep done">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
                </div>
            @endif
        @else
            <div class="dep">
                <div class="title">{{__('bonuses.welcome_bonus-1dep')}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_fs_1->getBonusParam('freespins', $curr_name, 0)}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/prc.svg"> {{$pattern_fd_dep_1->getBonusParam('multiplier', $curr_name, 0)}}%</div>
            </div>
        @endif
        @if($dep_n > 2)
                @if(empty($users_fs_bonuses[1]))
                    @php $nd_disabled = true @endphp
                    <div class="dep canceled">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-canceled.svg"></div>
                </div>
            @else
                <div class="dep done">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
                </div>
            @endif
        @else
            <div class="dep">
                <div class="title">{{__('bonuses.welcome_bonus-2dep')}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_fs_2->getBonusParam('freespins', $curr_name, 0)}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/prc.svg"> {{$pattern_fd_dep_2->getBonusParam('multiplier', $curr_name, 0)}}%</div>
            </div>
        @endif
        @if($dep_n > 3)
                @if(empty($users_fs_bonuses[2]))
                    @php $nd_disabled = true @endphp
                    <div class="dep canceled">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-canceled.svg"></div>
                </div>
            @else
                <div class="dep done">
                    <div class="flex ai-center"><img src="/img/bonuses/wb-done.svg"></div>
                </div>
            @endif
        @else
            <div class="dep">
                <div class="title">{{__('bonuses.welcome_bonus-3dep')}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_fs_3->getBonusParam('freespins', $curr_name, 0)}}</div>
                <div class="flex ai-center"><img src="/img/bonuses/prc.svg"> {{$pattern_fd_dep_3->getBonusParam('multiplier', $curr_name, 0)}}%</div>
            </div>
        @endif
        <div class="dep @if($nd_disabled) disabled @endif">
            <div class="title">{{__('bonuses.welcome_bonus-1ndep')}}</div>
            <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_ndfs_1->getBonusParam('freespins', $curr_name, 0)}}</div>
        </div>
            <div class="dep @if($nd_disabled) disabled @endif">
            <div class="title">{{__('bonuses.welcome_bonus-2ndep')}}</div>
            <div class="flex ai-center"><img src="/img/bonuses/fs.svg"> {{$pattern_fd_ndfs_2->getBonusParam('freespins', $curr_name, 0)}}</div>
        </div>
    </div>
    <div class="w-100 flex jc-center mt-1">
        <p>{{__('bonuses.welcome_bonus-rewards')}}:</p>
    </div>
    <div class="rewards">
        <div>
            <img src="/img/bonuses/prc.svg">
            <p>{{__('bonus.bonus-amount')}}</p>
            <div class="amount"><span id="w-bonus-amount">{{$pattern->getBonusParam('multiplier', $curr_name, 0) * $min_dep / 100 + $min_dep}}</span> {{$curr_name}}</div>
        </div>
        <div>
            <img src="/img/bonuses/fs.svg">
            <p>{{__('bonus.freespins')}}</p>
            <div class="amount"><span id="w-bonus-spins" @if($fs_sum <= $min_dep) style="color: yellow;" @endif>@if($fs_sum <= $min_dep) {{$spins}} @endif</span></div>
        </div>
    </div>
    <div class="flex jc-sb ai-center mt-1">
        <p>{{__('bonus.dep_sum')}}</p>
        <div class="amount range-amount" onclick="this.classList.add('hidden'); this.nextElementSibling.size = this.nextElementSibling.value.length; this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.focus()">
            <span id="dep-amount">{{$min_dep}}</span> {{$curr_name}}
        </div>
        <input id="wb-amount-input" class="amount range-amount hidden" min="{{$min_dep}}" max="{{$max_dep}}" value="{{$min_dep}}">
    </div>
    <div class="custom-range w-100 mt-1" style="--value: 0%" data-target-value=".bonuses__wb-wrapper #dep-amount">
        <input id="dep-value" type="range" min="{{$min_dep}}" max="{{$max_dep}}" value="{{$min_dep}}" step="{{$step}}">
        <div class="range-sums"><div>{{$min_dep}} {{$curr_name}}</div><div>{{$max_dep}} {{$curr_name}}</div></div>
        <div class="range-threshold @if($fs_sum <= $min_dep) hidden @endif" style="--left: 0%">
            {{__('bonuses.welcome_bonus-fs_dep_amount', ['fs_sum' => $fs_sum])}} {{$curr_name}}
            <img src="/img/bonuses/fs.svg" alt="fs" class="threshold-img" style="margin-left: -4px">
        </div>
    </div>
    <div class="flex w-100 jc-center p-1">
        <button class="button button_green grow" onclick="Modal.hide(this);wallet.showDeposit(document.getElementById('dep-value').value)">{{__('bonuses.welcome_bonus-make_deposit')}}</button>
    </div>
</div>
<script>
    (
        () => {
            const rangeInput = document.getElementById('dep-value');
            const amountInput = document.getElementById('wb-amount-input');
            const depAmount = document.querySelector('.bonuses__wb-wrapper #dep-amount');

            amountInput.addEventListener('blur', (e) => {
                e.target.classList.add('hidden');
                e.target.previousElementSibling.classList.remove('hidden');
                if (e.target.value < {{$min_dep}}) {
                    e.target.value = {{$min_dep}}
                }
                document.getElementById('w-bonus-amount').innerText = e.target.value * {{$pattern->getBonusParam('multiplier', $curr_name, 0) / 100}} + + e.target.value;
                rangeInput.value = e.target.value;
                depAmount.innerText = e.target.value;
                if (e.target.value >= {{$fs_sum}}) {
                    document.getElementById('w-bonus-spins').innerText = {{$spins}};
                    document.getElementById('w-bonus-spins').style.color = "yellow";
                } else {
                    document.getElementById('w-bonus-spins').innerText = 0;
                    document.getElementById('w-bonus-spins').style.color = "";
                }
                let event = new window.Event('change', { bubbles: true });
                rangeInput.dispatchEvent(event);
            })


            amountInput.addEventListener('input', (e) => {
                e.target.size = e.target.value.length
                document.getElementById('w-bonus-amount').innerText = e.target.value * {{$pattern->getBonusParam('multiplier', $curr_name, 0) / 100}} + + e.target.value;
                rangeInput.value = e.target.value;
                depAmount.innerText = e.target.value;
                if (e.target.value >= {{$fs_sum}}) {
                    document.getElementById('w-bonus-spins').innerText = {{$spins}};
                    document.getElementById('w-bonus-spins').style.color = "yellow";
                } else {
                    document.getElementById('w-bonus-spins').innerText = 0;
                    document.getElementById('w-bonus-spins').style.color = "";
                }
                let event = new window.Event('change', { bubbles: true });
                rangeInput.dispatchEvent(event);
            })

            rangeInput.addEventListener('input', (e) => {
                document.getElementById('w-bonus-amount').innerText = e.target.value * {{$pattern->getBonusParam('multiplier', $curr_name, 0) / 100}} + + e.target.value;
                amountInput.value = e.target.value;
                if (e.target.value >= {{$fs_sum}}) {
                    document.getElementById('w-bonus-spins').innerText = {{$spins}};
                    document.getElementById('w-bonus-spins').style.color = "yellow";
                } else {
                    document.getElementById('w-bonus-spins').innerText = 0;
                    document.getElementById('w-bonus-spins').style.color = "";
                }
            })

            setTimeout(() => {
                //set threshold position
                const prc =  ({{$fs_sum - $min_dep}} / {{$max_dep - $min_dep}}) * (rangeInput.getBoundingClientRect().width - 28) + 13;
                rangeInput.parentElement.querySelector('.range-threshold').style.setProperty('--left', `${prc - 7}px`)
                // set default positon to get freesins
                @if($fs_sum > 0)
                    rangeInput.value = {{$fs_sum}};
                    let event = new window.Event('input', { bubbles: true });
                    rangeInput.dispatchEvent(event);
                @endif
            }, 0);
        }
    )()
</script>
