@php
    $months = [];
    for ($f = 7; $f >= 0; $f--) {
        $months[] = \Carbon\Carbon::now()->firstOfMonth()->subMonths($f);
    }
    if(\Illuminate\Support\Facades\App::getLocale() === 'ru') {
        $monthsNames = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
    } elseif (\Illuminate\Support\Facades\App::getLocale() === 'kz') {
        $monthsNames = ["Қаңтар", "Ақпан", "Наурыз", "Сәуір", "Мамыр", "Маусым", "Шілде", "Тамыз", "Қыркүйек", "Қазан", "Қараша", "Желтоқсан"];
    } elseif (\Illuminate\Support\Facades\App::getLocale() === 'ua') {
        $monthsNames = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
    } else {
        $m = 0;
        $date = now()->firstOfYear();
        for ($f = 0; $f < 12; $f++) {
            $monthsNames[] = $date->isoFormat('MMMM');
            $date->lastOfMonth()->addDay();
        }
    }
@endphp

<div class="width-limiter">

    <div class="hof__banner">
        <img src="/img/banners/hall-of-fame_phone_{{ app()->getLocale() }}.webp" class="hof__banner-img w-100">
    </div>

    <h2 class="hof__title">{{__('hall-of-fame.title')}}:</h2>

    <div class="hof__buttons">
        <div class="hof__button hof__button_active" data-date="all">{{__('hall-of-fame.all_time')}}</div>
        @foreach($months as $month)
            <div class="hof__button" data-date="{{ $month->format('Y-m-d') }}">{{ $monthsNames[$month->month - 1] }}</div>
        @endforeach
    </div>


    <div id="hof__table-wrap">
        @include('phone.hall-of-fame.table', ['month' => null])
    </div>
</div>
@include('phone.blocks.bottom-deposit-btn')
