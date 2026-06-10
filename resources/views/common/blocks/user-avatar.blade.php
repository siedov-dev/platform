<?php

/**
 * @var float $percent
 */

//$id = 'p_' . time() . rand(1000, 5000);
?>

<div class="user-avatar">

{{--    <svg class="user-avatar__progress" id="{{ $id }}" width="80" height="80">--}}
{{--        <circle cx="40" cy="40" r="35" stroke="rgba(255,255,255,0.15)" fill="none"></circle>--}}
{{--        <circle cx="40" cy="40" r="35" stroke="#A3DC8E" fill="none" stroke-dasharray="150, 1000"></circle>--}}
{{--        <circle cx="10" cy="10" r="3" stroke="none" fill="#A3DC8E"></circle>--}}
{{--    </svg>--}}
    <div class="user-avatar__image-wrap">
        <img src="/img/ui/ranks/rank-{{App\MUser::getAuth()->getRank()->getLevel()}}.png" class="user-avatar__image">
    </div>
</div>

{{--<script>--}}
{{--    (() => {--}}
{{--        let svg = document.getElementById('{{ $id }}');--}}
{{--        let circles = svg.getElementsByTagName('circle');--}}
{{--        let bar = circles[1];--}}
{{--        let dot = circles[2];--}}

{{--        let percent = {{ isset($percent) ? $percent : 0 }};--}}
{{--        let angle = 2 * Math.PI * percent / 100--}}

{{--        bar.setAttribute('stroke-dasharray', Math.round(219 * percent/100) + ', 1000');--}}
{{--        dot.setAttribute('cx', (35 * Math.cos(angle) + 40).toFixed(2));--}}
{{--        dot.setAttribute('cy', (35 * Math.sin(angle) + 40).toFixed(2));--}}
{{--    })();--}}
{{--</script>--}}
