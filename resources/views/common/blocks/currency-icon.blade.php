@php
    $symbol = \App\Helpers\Currency::getCurrencySymbol($curr);
    $color = \App\Helpers\Currency::getCurrencyColor($curr);
    $length = mb_strlen($symbol);
@endphp
<style>
    .currency-icon-{{$curr}} {
        background-color: #{{$color}};
    }
    .currency-icon-{{$curr}}::after{
        content: '{{$symbol}}';
        @if($length == 2)
            font-size: .8rem;
        @elseif($length > 2)
            font-size: .6rem;
        @endif
    }
    .currency-icon-{{$curr}}::before {
        background-color: #{{\App\Helpers\UI::adjustColorBrightness($color, -10)}};
        background-image: linear-gradient(to bottom, #{{\App\Helpers\UI::adjustColorBrightness($color, -30)}}, transparent);
    }
</style>
<div class="currency-icon currency-icon-{{$curr}}"></div>
