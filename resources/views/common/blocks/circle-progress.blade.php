@php
    $percent = $prc?? 0;
    $r = $r?? 26;
    $l = 2 * 3.1415 * $r;
    $f = $l * $percent / 100;
@endphp
<div style="" class="circle-progress">
    <svg width="60" height="60">
        <circle cx="30" cy="30" r="{{$r}}" stroke="#a3dc8e" stroke-width="4" fill="none" stroke-dasharray="{{$f}}, {{$l - $f}}"></circle>
    </svg>
</div>
